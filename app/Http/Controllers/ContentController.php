<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Content;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Maize\Markable\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        if ($request['category'] == 'all') {
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
                ->paginate(9);
        } else {
            $tags = $request['category'];
            $category = Tag::where('tag', $tags)->pluck('id_content');

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
                ->whereIn('contents.id', $category)
                ->leftJoin('projects', 'contents.id_proyek', '=', 'projects.id')
                ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
                ->leftJoin('bimbingan', 'contents.id_proyek', '=', 'bimbingan.id_project')
                ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
                ->leftJoin('users', 'users.id', '=', 'students.id_user')
                ->groupBy(
                    'contents.id',
                    'contents.id_proyek',
                    'contents.thumbnail_image_url',
                    'contents.content_url',
                    'contents.video_url',
                    'contents.video_tittle',
                    'contents.github_url',
                    'contents.tipe_konten',
                    'contents.created_at',
                    'contents.updated_at',
                    'users.first_name',
                    'users.last_name',
                    'projects.id',
                    'projects.id_lecturer',
                    'projects.id_period',
                    'projects.tittle',
                    'projects.agency',
                    'projects.description',
                    'projects.tools',
                    'projects.status',
                )
                ->selectRaw('CONCAT(GROUP_CONCAT(DISTINCT tags.tag ORDER BY tags.tag ASC SEPARATOR \',\')) as tags')
                ->paginate(9);
        }

        foreach ($contents as $content) {
            $thumbnail_image_url = asset('storage/' . $content->thumbnail_image_url);
            $content_url = asset('storage/' . $content->content_url);
            $content->thumbnail_image_url = $thumbnail_image_url;
            $content->content_url = $content_url;
        }

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
            ->groupBy(
                'contents.id',
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
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->get();

        foreach ($get_contents as $content) {
            $thumbnail_image_url = asset('storage/' . $content->thumbnail_image_url);
            $content_url = asset('storage/' . $content->content_url);
            $content->thumbnail_image_url = $thumbnail_image_url;
            $content->content_url = $content_url;
        }



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
            ->groupBy(
                'contents.id',
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
            ->groupBy(
                'contents.id',
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
            ->selectRaw('contents.*, projects.id as project_id, projects.id_lecturer, projects.id_period, projects.tittle, projects.agency, projects.description, projects.tools, projects.status, JSON_ARRAYAGG(tags.tag) as tags')
            ->orderBy($based, 'asc')->get();
        // Mengembalikan konten yang telah dipilih dan diurutkan
        return response()->json($sorted_contents);
    }

    public function store(Request $request)
    {
        $id = Auth::user()->id;
        $request->validate([
            'thumbnail_image_url' => 'required',
            'content_url' => 'required',
            'video_url' => 'required',
            'video_tittle' => 'required',
            'tipe_konten' => 'required',
        ]);



        $project = Project::select('projects.id')
            ->leftJoin('bimbingan', 'projects.id', '=', 'bimbingan.id_project')
            ->leftJoin('lecturers', 'lecturers.id', '=', 'bimbingan.id_lecturer')
            ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
            ->leftJoin('users', 'users.id', '=', 'students.id_user')
            ->where('users.id', $id)->get();



        if ($request->hasFile('thumbnail_image_url')) {
            $thumbnailPath = $request->file('thumbnail_image_url')->store('thumbnails', 'public');
        }

        // Simpan PDF file
        if ($request->hasFile('content_url')) {
            $contentPath = $request->file('content_url')->store('pdfs', 'public');
        }


        $content = new Content();
        $content->id_proyek = $project[0]->id;
        $content->thumbnail_image_url = $thumbnailPath;
        $content->content_url = $contentPath;
        $content->video_url = $request->video_url;
        $content->video_tittle = $request->video_tittle;
        $content->github_url = $request->github_url;
        $content->tipe_konten = $request->tipe_konten;
        $content->save();

        $tags = $request->input('tags');

        $tags = explode(',', $tags);

        foreach ($tags as $tagValue) {
            $tag = new Tag();
            $tag->tag = $tagValue;
            $tag->id_content = $content->id; // Mengisi id_content dengan id content yang baru saja disimpan
            $tag->save();
        }

        return response()->json(["Content berhasil ditambahkan", $content]);
    }

    public function showProject()
    {
        $id = Auth::user()->id;
        $project = Project::select('projects.*', 'users.*', 'lecturers.full_name as lecturer')
            ->leftJoin('bimbingan', 'projects.id', '=', 'bimbingan.id_project')
            ->leftJoin('lecturers', 'lecturers.id', '=', 'bimbingan.id_lecturer')
            ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
            ->leftJoin('users', 'users.id', '=', 'students.id_user')
            ->where('users.id', $id)->get();

        return response()->json($project);
    }

    public function show($id)
    {

        $content = Content::select(
            'contents.*',
            'users.first_name',
            'users.last_name',
            'lecturers.full_name as lecturer',
            'projects.id as project_id',
            'projects.id_lecturer',
            'projects.id_period',
            'projects.tittle',
            'projects.agency',
            'projects.description',
            'projects.tools',
            'projects.status',
        )
            ->where('contents.id', '=', $id)
            ->leftJoin('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->leftJoin('bimbingan', 'contents.id_proyek', '=', 'bimbingan.id_project')
            ->leftJoin('lecturers', 'lecturers.id', '=', 'bimbingan.id_lecturer')
            ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
            ->leftJoin('users', 'users.id', '=', 'students.id_user')
            ->groupBy(
                'contents.id',
                'contents.id_proyek',
                'contents.thumbnail_image_url',
                'contents.content_url',
                'contents.video_url',
                'contents.video_tittle',
                'contents.github_url',
                'contents.tipe_konten',
                'contents.created_at',
                'contents.updated_at',
                'users.first_name',
                'users.last_name',
                'lecturers.full_name',
                'projects.id',
                'projects.id_lecturer',
                'projects.id_period',
                'projects.tittle',
                'projects.agency',
                'projects.description',
                'projects.tools',
                'projects.status',
            )
            ->selectRaw('CONCAT(GROUP_CONCAT(DISTINCT tags.tag ORDER BY tags.tag ASC SEPARATOR \',\')) as tags')
            ->get();

        $thumbnail_image_url = asset('storage/' . $content[0]->thumbnail_image_url);
        $content[0]->thumbnail_image_url = $thumbnail_image_url;
        $content_url = asset('storage/' . $content[0]->content_url);
        $content[0]->content_url = $content_url;

        return response()->json($content);
        // return view('content.show');
    }



    public function self($id)
    {
        $content = Content::select(
            'contents.id as content_id',
            'contents.id_proyek',
            'contents.thumbnail_image_url',
            'contents.content_url',
            'contents.video_url',
            'contents.video_tittle',
            'contents.github_url',
            'contents.tipe_konten',
            'contents.created_at',
            'contents.updated_at',
            'users.id',
            'users.username',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.email_verified_at',
            'users.role',
            'users.photo',
            'lecturers.full_name as lecturer',
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
            ->leftJoin('lecturers', 'lecturers.id', '=', 'bimbingan.id_lecturer')
            ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
            ->leftJoin('users', 'users.id', '=', 'students.id_user')
            ->where('users.id', '=', $id)
            ->groupBy(
                'contents.id',
                'contents.id_proyek',
                'contents.thumbnail_image_url',
                'contents.content_url',
                'contents.video_url',
                'contents.video_tittle',
                'contents.github_url',
                'contents.tipe_konten',
                'contents.created_at',
                'contents.updated_at',
                'users.id',
                'users.username',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.email_verified_at',
                'users.role',
                'users.photo',
                'projects.id',
                'lecturers.full_name',
                'projects.id_lecturer',
                'projects.id_period',
                'projects.tittle',
                'projects.agency',
                'projects.description',
                'projects.tools',
                'projects.status',
            )
            ->selectRaw('CONCAT(GROUP_CONCAT(DISTINCT tags.tag ORDER BY tags.tag ASC SEPARATOR \',\')) as tags')
            ->get();

        $thumbnailPath = asset('storage/' . $content[0]->thumbnail_image_url);
        $contentPath = asset('storage/' . $content[0]->content_url);
        $content[0]->thumbnail_image_url = $thumbnailPath;
        $content[0]->content_url = $contentPath;
        return response()->json($content);
        // return view('content.show');
    }



    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'thumbnail_image_url' => 'nullable',
            'content_url' => 'nullable',
            'video_url' => 'nullable',
            'video_tittle' => 'nullable',
            'tipe_konten' => 'nullable',
        ]);

        $id = Auth::user()->id;
        $project = Project::select('projects.id')
            ->leftJoin('bimbingan', 'projects.id', '=', 'bimbingan.id_project')
            ->leftJoin('lecturers', 'lecturers.id', '=', 'bimbingan.id_lecturer')
            ->leftJoin('students', 'bimbingan.id_student', '=', 'students.id')
            ->leftJoin('users', 'users.id', '=', 'students.id_user')
            ->where('users.id', $id)->get()->first();


        if ($request->hasFile('thumbnail_image_url')) {
            $thumbnailPath = $request->file('thumbnail_image_url')->store('thumbnails', 'public');
            $validatedData['thumbnail_image_url'] = $thumbnailPath;
        }

        // Simpan PDF file
        if ($request->hasFile('content_url')) {
            $contentPath = $request->file('content_url')->store('pdfs', 'public');
            $validatedData['content_url'] = $contentPath;
        }

        $content = Content::where('id_proyek', $project->id)->first();
        if ($content) {
            $content->update($validatedData);
        } else {
            return response()->json("Content tidak ditemukan");
        };

        if ($request->has('tags')) {
            $tags = $request->input('tags');

            $tags = explode(',', $tags);

            // Delete old tags associated with this content
            Tag::where('id_content', $content->id)->delete();
            foreach ($tags as $tagValue) {
                $tag = new Tag();
                $tag->tag = $tagValue;
                $tag->id_content = $content->id; // Mengisi id_content dengan id content yang baru saja disimpan
                $tag->save();
            }
        }




        return response()->json(["Content berhasil diupdate", $content]);

        // return redirect()->route('content.index');
    }
    public function destroy($id)
    {

        return redirect()->route('content.index');
    }
    public function search(Request $request)
    {
        $query = $request->input('query');

        $contents = Content::select(
            'contents.id',
            'contents.id_proyek',
            'contents.created_at',
            'contents.updated_at',
            'projects.id as project_id',
            'projects.id_lecturer',
            'projects.id_period',
            'projects.tittle',
            'projects.agency',
            'projects.description',
            'projects.tools',
            'projects.status',
            DB::raw('JSON_ARRAYAGG(tags.tag) as tags')
        )
            ->join('projects', 'contents.id_proyek', '=', 'projects.id')
            ->leftJoin('tags', 'contents.id', '=', 'tags.id_content')
            ->where('projects.tittle', 'like', "%" . $query . "%")
            ->groupBy(
                'contents.id',
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
            ->get();

        return response()->json($contents);
    }


    public function addLike($contentId)
    {
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);
        Like::add($content, $user); // marks the course liked for the given user

        return response()->json("Content berhasil dilike");
    }

    public function unLike($contentId)
    {
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);
        Like::remove($content, $user); // marks the course liked for the given user

        return response()->json("Content berhasil diunlike");
    }
    public function toggleLike($contentId)
    {
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);
        Like::toggle($content, $user); // marks the course liked for the given user
    }
    public function checkLike($contentId)
    {
        $content = Content::where('id', $contentId)->first();
        $id = Auth::user()->id;
        $user = User::find($id);
        $isLiked = Like::has($content, $user); // marks the course liked for the given user
        return response()->json($isLiked);
    }
    public function countLikes($contentId)
    {
        $content = Content::where('id', $contentId)->first();
        if (!$content) {
            $count = 0;
            return response()->json($count);
        } else {
            $count = Like::count($content); // marks the course liked for the given post 
            return response()->json($count);
        }
    }
    public function showLiked()
    {
        $likedContents = Content::select(
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
            ->join('markable_likes', 'contents.id', '=', 'markable_likes.markable_id')
            ->where('markable_likes.user_id', Auth::user()->id)
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
            ->paginate(9);

        foreach ($likedContents as $content) {
            $thumbnail_image_url = asset('storage/' . $content->thumbnail_image_url);
            $content_url = asset('storage/' . $content->content_url);
            $content->thumbnail_image_url = $thumbnail_image_url;
            $content->content_url = $content_url;
        }


        return response()->json($likedContents);
    }
    public function createComment(Request $request, $contentId)
    {
        $comment = $request->comment;
        $content = Content::where('id', $contentId)->first();
        $content->comment($comment);

        return response()->json($content);
    }
    public function showComments($contentId)
    {
        // $content = DB::select('SELECT content, user_id FROM comments where commentable_id ='.$contentId);
        $contents = Comment::select('comments.*', 'users.first_name', 'users.last_name', 'users.photo')
            ->where('comments.commentable_id', '=', $contentId)
            ->leftJoin('users', 'comments.user_id', '=', 'users.id')
            ->get();

        foreach ($contents as $content) {
            $content->photo = asset('storage/post_img/' . $content->photo);
        }

        return response()->json($contents);
    }
    public function showCommentsUser()
    {
        $id = Auth::user()->id;
        $commentcontent = Content::select(
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
            ->join('comments', 'contents.id', '=', 'comments.commentable_id')
            ->where('comments.user_id', $id)
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
            ->paginate(9);

        foreach ($commentcontent as $content) {
            $thumbnail_image_url = asset('storage/' . $content->thumbnail_image_url);
            $content_url = asset('storage/' . $content->content_url);
            $content->thumbnail_image_url = $thumbnail_image_url;
            $content->content_url = $content_url;
        }

        return response()->json($commentcontent);
    }
}
