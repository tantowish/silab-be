<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Maize\Markable\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function index()
    {
        // Mengambil semua data konten beserta data project yang terkait
        $contents = $contents = Content::select(
            'contents.*',
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
            ->groupBy('contents.id')
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->get();



        // Mengembalikan respons JSON yang berisi semua data konten termasuk data project yang terkait
        return response()->json($contents);
    }


    public function showBasedOnTopic($tags)
    {
        // Mengambil ID konten yang memiliki tag sesuai dengan topik yang dipilih
        $contents = Tag::where('tag', $tags)->pluck('id_content');

        // Mengambil konten berdasarkan ID yang telah dipilih dan juga mengambil data proyek yang terkait
        $get_contents = Content::select('contents.*', 'projects.id as project_id', 'projects.id_lecturer', 'projects.id_period', 'projects.tittle', 'projects.agency', 'projects.description', 'projects.tools', 'projects.status')
            ->whereIn('contents.id', $contents)
            ->join('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->groupBy('contents.id')
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->get();
        


        // Mengembalikan konten yang telah dipilih beserta data proyek yang terkait dan diurutkan berdasarkan kolom yang dipilih
        return response()->json($get_contents);
    }


    public function sortedData($based, $tags)
    {
        // Mengambil ID konten yang memiliki tag sesuai dengan topik yang dipilih
        $contents = Tag::where('tag', $tags)->pluck('id_content');
        // Mengambil konten berdasarkan ID yang telah dipilih dan diurutkan berdasarkan kolom tanggal
        $sorted_contents = Content::select('contents.*', 'projects.id as project_id', 'projects.id_lecturer', 'projects.id_period', 'projects.tittle', 'projects.agency', 'projects.description', 'projects.tools', 'projects.status')
            ->whereIn('contents.id', $contents)
            ->join('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->groupBy('contents.id')
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->orderBy($based, 'asc')->get();
        // Mengembalikan konten yang telah dipilih dan diurutkan
        return response()->json($sorted_contents);
    }


    public function sortedAllData($based)
    {
        // Mengambil konten berdasarkan ID yang telah dipilih dan diurutkan berdasarkan kolom tanggal
        $sorted_contents = Content::select('contents.*', 'projects.id as project_id', 'projects.id_lecturer', 'projects.id_period', 'projects.tittle', 'projects.agency', 'projects.description', 'projects.tools', 'projects.status')
            ->join('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->groupBy('contents.id')
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->orderBy($based, 'asc')->get();
        // Mengembalikan konten yang telah dipilih dan diurutkan
        return response()->json($sorted_contents);
    }

    public function store(Request $request)
    {
        $request->validate([
            'thumbnail_image_url' => 'required',
            'content_url' => 'required',
            'video_url' => 'required',
            'video_tittle' => 'required',
            'github_url' => 'required',
            'tipe_konten' => 'required',
        ]);

        $id_proyek = $request->id_proyek;
        $content = Content::create($request->all());
        return response()->json("Content berhasil ditambahkan");
    }

    public function show($id)
    {
        $content = Content::select('contents.*', 'projects.id as project_id', 'projects.id_lecturer', 'projects.id_period', 'projects.tittle', 'projects.agency', 'projects.description', 'projects.tools', 'projects.status')
            ->where('contents.id', $id)
            ->join('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->groupBy('contents.id')
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->get();

        return response()->json($content);
        // return view('content.show');
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'id_proyek' => 'required',
            'thumbnail_image_url' => 'required',
            'content_url' => 'required',
            'video_url' => 'required',
            'video_tittle' => 'required',
            'github_url' => 'required',
            'tipe_konten' => 'required',
        ]);
        $id = $request->id_proyek;
        $content = Content::find($id);
        $content->update($request->all());
        return response()->json("Content berhasil di-update");
        // return redirect()->route('content.index');
    }
    public function destroy($id)
    {

        return redirect()->route('content.index');
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $contents = Content::select('contents.*', 'projects.id as project_id', 'projects.id_lecturer', 'projects.id_period', 'projects.tittle', 'projects.agency', 'projects.description', 'projects.tools', 'projects.status')
            ->join('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->groupBy('contents.id')
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->where('projects.tittle', 'like', "%" . $query . "%")
            ->get();
        return response()->json($contents);
    }

    public function addLike($contentId){
        $content = Content::where('id', $contentId)->first();  
        $id = Auth::user()->id;
        $user = User::find($id);     
        Like::add($content, $user); // marks the course liked for the given user
    }

    public function unLike($contentId){
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);        
        Like::remove($content, $user); // marks the course liked for the given user
    }
    public function toggleLike($contentId){
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);        
        Like::toggle($content, $user); // marks the course liked for the given user
    }
    public function checkLike($contentId){
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);        
        $isLiked = Like::has($content, $user); // marks the course liked for the given user
        return response()->json($isLiked);
    }   
    public function countLikes($contentId){
        $content = Content::where('id', $contentId)->first();
        $count = Like::count($content); // marks the course liked for the given post 
        return response()->json($count);
    }
    public function showLiked(){
        $likedContents = Content::firstOrFail()->likes;
        return response()->json($likedContents);
    }
    public function createComment($contentId, Request $request){
        $comment = $request->comment;
        $content = Content::where('id', $contentId)->first();
        $content->comment($comment);
    }
    public function showComments($contentId){
        // $content = DB::select('SELECT content, user_id FROM comments where commentable_id ='.$contentId);
        $content = Comment::where('commentable_id', $contentId)->get();
        return response()->json($content);
    }
    public function showCommentsUser(){
        $id = Auth::user()->id;
        $content = Content::select('contents.*')
            ->join('comments', 'contents.id', '=', 'comments.commentable_id')
            ->where('comments.user_id', $id)
            ->get();
        return response()->json($content);
    }   

}
