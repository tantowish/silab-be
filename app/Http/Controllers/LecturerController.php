<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;

class LecturerController extends Controller
{
    public function index()
    {
        $lecturers = Lecturer::select('lecturers.*')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->groupBy('lecturers.id')
            ->selectRaw('lecturers.*, JSON_ARRAYAGG(specialities.tag) as specialities')
            ->get();
        return response()->json($lecturers);
        // return view('lecturer.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $lecturers = Lecturer::select('lecturers.id', 'lecturers.full_name')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.full_name', 'like', "%" . $query . "%")
            ->groupBy('lecturers.id', 'lecturers.full_name')
            ->selectRaw('JSON_ARRAYAGG(specialities.tag) as specialities')
            ->get();

        return response()->json($lecturers);
    }


    public function show($id)
    {
        $lecturer = Lecturer::select('lecturers.*')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.id', $id)
            ->groupBy('lecturers.id')
            ->selectRaw('lecturers.*, JSON_ARRAYAGG(specialities.tag) as specialities')
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
