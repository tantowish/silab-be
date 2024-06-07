<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    public function index(Request $request)
    {

        $query = $request->input('query', '');

        $lecturers = Lecturer::select('lecturers.*')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.full_name', 'like', "%" . $query . "%")
            ->groupBy('lecturers.id', 'lecturers.full_name')
            ->selectRaw('CONCAT(\'[\', GROUP_CONCAT(DISTINCT specialities.tag ORDER BY specialities.tag ASC SEPARATOR \',\'), \']\') as specialities')
            ->paginate(5);

        // Initialize an array to store lecturers with specialities
        $lecturersWithSpecialities = $lecturers->items();

        // Fetch and aggregate specialities for each lecturer
        foreach ($lecturersWithSpecialities as &$lecturer) {
            $specialities = DB::table('specialities')
                ->where('id_lecturer', $lecturer->id)
                ->pluck('tag');

            // Add specialities as a JSON array
            $lecturer->specialities = $specialities->toJson();
        }

        // Convert the modified items back to a paginator instance
        $lecturers->setCollection(collect($lecturersWithSpecialities));

        // Return the paginated lecturers with specialities as JSON
        return response()->json($lecturers);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $lecturers = Lecturer::select('lecturers.id', 'lecturers.full_name')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.full_name', 'like', "%" . $query . "%")
            ->groupBy('lecturers.id', 'lecturers.full_name')
            ->selectRaw('CONCAT(\'[\', GROUP_CONCAT(DISTINCT specialities.tag ORDER BY specialities.tag ASC SEPARATOR \',\'), \']\') as specialities')
            ->get();

        return response()->json($lecturers);
    }


    public function show($id)
    {
        $lecturer = Lecturer::select('lecturers.*')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.id', $id)
            ->groupBy('lecturers.id')
            ->selectRaw('lecturers.*, CONCAT(\'[\', GROUP_CONCAT(DISTINCT specialities.tag ORDER BY specialities.tag ASC SEPARATOR \',\'), \']\') as specialities')
            ->first();
        return response()->json($lecturer);
        // return view('lecturer.show');
    }

    public function update(Request $request, $id)
    {
        $lecturer = Lecturer::find($id);
        $lecturer->update($request->all());
        return response()->json("Dosen berhasil di-update");
        // return redirect()->route('lecturer.index');
    }
}
