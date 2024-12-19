<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;

class TagController extends Controller
{
    public function show($id)
    {
        $tags = Tag::where('id_content', $id)->pluck('tag');
        return response()->json($tags);
    }
}
