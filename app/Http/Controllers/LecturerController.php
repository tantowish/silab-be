<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Content;

class LecturerController extends Controller
{
    public function index(Request $request)
    {

        $query = $request->input('query', '');

        $lecturers = Lecturer::select('lecturers.*', 'specialities.tag')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.full_name', 'like', "%" . $query . "%")
            ->groupBy('lecturers.id', 'lecturers.full_name', 'lecturers.id_user', 'lecturers.image_profile', 'lecturers.front_title', 'lecturers.back_title', 'lecturers.NID', 'lecturers.phone_number', 'lecturers.max_quota', 'lecturers.isKaprodi', 'lecturers.created_at', 'lecturers.updated_at', 'specialities.tag')
            ->selectRaw('CONCAT(GROUP_CONCAT(DISTINCT specialities.tag ORDER BY specialities.tag ASC SEPARATOR \',\')) as specialities')
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
            ->selectRaw('CONCAT(GROUP_CONCAT(DISTINCT specialities.tag ORDER BY specialities.tag ASC SEPARATOR \',\')) as specialities')
            ->get();

        return response()->json($lecturers);
    }


    public function show($id)
    {
        $lecturer = Lecturer::select('lecturers.*')
            ->leftJoin('specialities', 'lecturers.id', '=', 'specialities.id_lecturer')
            ->where('lecturers.id', $id)
            ->groupBy('lecturers.id', 'lecturers.full_name', 'lecturers.id_user', 'lecturers.image_profile', 'lecturers.front_title', 'lecturers.back_title', 'lecturers.NID', 'lecturers.phone_number', 'lecturers.max_quota', 'lecturers.isKaprodi', 'lecturers.created_at', 'lecturers.updated_at')
            ->selectRaw('lecturers.*, CONCAT(GROUP_CONCAT(DISTINCT specialities.tag ORDER BY specialities.tag ASC SEPARATOR \',\')) as specialities')
            ->first();

        $contents = Content::select(
            'contents.*',
            'users.first_name',
            'users.last_name',
            'projects.id as project_id',
            'projects.id_lecturer',
            'projects.id_period',
            'projects.tittle',
            'projects.agency',
            'projects.description',
            'projects.tools',
            'projects.status',
        )
            ->leftJoin('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->leftJoin('bimbingan', 'contents.id_proyek', '=', 'bimbingan.id_project')
            ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
            ->leftJoin('users', 'users.id', '=', 'students.id_user')
            ->groupBy(
                'contents.id',
                'users.first_name',
                'users.last_name',
                'contents.id_proyek',
                'contents.thumbnail_image_url',
                'contents.content_url',
                'contents.video_url',
                'contents.video_tittle',
                'contents.github_url',
                'contents.tipe_konten',
                'contents.created_at',
                'contents.updated_at',
                'projects.id',
                'projects.id_lecturer',
                'projects.id_period',
                'projects.tittle',
                'projects.agency',
                'projects.description',
                'projects.tools',
                'projects.status'
            )
            ->selectRaw('CONCAT(GROUP_CONCAT(DISTINCT tags.tag ORDER BY tags.tag ASC SEPARATOR \',\')) as tags')
            ->where('projects.id_lecturer', $id)
            ->get();

        $lecturer->contents = $contents;
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
