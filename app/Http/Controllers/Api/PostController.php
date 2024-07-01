<?php

namespace App\Http\Controllers\Api;

//import Model "Post"
use App\Models\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//import Resource "PostResource"
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //mengambil semua data post
        $posts = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public  function store(Request $request){
        $validator =Validator::make($request->all(),[
         'image'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:3000',
         'title'  => 'required',
         'content'=> 'required'
        ]);

        //mengecek validasi apabila gagal maka menampilkan pesan dan error kode 422
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        //menambahkan gambar
        $image = $request->file('image');
        $image->storeAs('public/posts',$image->hashName());

      
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content
        ]);
        return new PostResource(true,'Data Post berhasil ditambahkan!',$post);
    }

    public function show($id){
        $post = Post::find($id);

        return new PostResource(true,'Detail Data Post',$post);
    }

    public function update(Request $request,$id){
        $validator = Validator::make($request->all(),[
             'title' => 'required',
             'content' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        $post = Post::find($id);
        // mengecek apakah ada gambar yang diubah
        if($request->hasFile('image')){

           $image = $request->file('image');
           $image->storeAs('public/posts',$image->hashName());
            
           Storage::delete('public/posts'.basename($post->$image));

           $post->update([
                'image' => $image ->hashName(),
                'title' => $request->title,
                'content' => $request ->content
           ]);

        }
        else{
            $post->update([
                'title' => $request ->title,
                'content' => $request -> content
            ]);
        }

        return new PostResource(true,'Data post berhasil diubah',$post);
    }

    public function destroy($id){
      $post = Post::find($id);

      Storage::delete('public/posts'.basename($post->image));
      $post-> delete();

      return new PostResource(true,'Data post berhasil dihapus',$post);
    }
}
 