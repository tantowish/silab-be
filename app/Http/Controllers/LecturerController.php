<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;

class LecturerController extends Controller
{
    public function index()
    {
        $lecturers = Lecturer::all();
        return response()->json($lecturers);
        // return view('lecturer.index');
    }
    public function create()
    {
        
        // return view('lecturer.create');
    }
    public function store(Request $request)
    {

        return redirect()->route('lecturer.index');
    }
    public function show($id)
    {
        $lecturer = Lecturer::find($id);
        return response()->json($lecturer);
        // return view('lecturer.show');
    }
    public function edit($id)
    {
        return view('lecturer.edit');
    }
    public function update(Request $request, $id)
    {
        $lecturer = Lecturer::find($id);
        $lecturer->update($request->all());
        return response()->json("Dosen berhasil di-update");
        // return redirect()->route('lecturer.index');
    }
    public function destroy($id)
    {
        return redirect()->route('lecturer.index');
    }

}
