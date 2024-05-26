<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index() {
        $post = Post::latest()->paginate(5);

        return new PostResource(true, 'List Data Posts', $post);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
            'image'=> $image->hashName(),
            'title'=> $request->title,
            'content'=> $request->content
        ]);

        return new PostResource(true,'data Berhasil Ditambahkan ',$post);
    }
    public function show($id) {
        $post = Post::findOrFail($id);
        return new PostResource(true,'Data Ditemukan berdasarkan id '.$id , $post);
    }

    public function update(Request $request, $id) {

        $post = Post::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        if($request->hasFile('image')) {
            $image = $request->file('image');

            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts/' .basename($post->image));


            $post->update([
                'image'=> $image->hashName(),
                'title'=> $request->title,
                'content'=> $request->content,
            ]);
        } else {
            $post->update([
                'title'=> $request->title,
                'content'=> $request->content,
            ]);
        }
        return new PostResource(true,'data Berhasil DiPATCH ',$post);

    }
    public function destroy($id) {
        $post = Post::findOrFail($id);
        Storage::delete('public/posts/' .basename($post->image));
        $post->delete();
        return new PostResource(true,'data Berhasil DiDELETE ',null);

    }
}
