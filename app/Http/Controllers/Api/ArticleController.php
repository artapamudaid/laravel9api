<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ArticleResource;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        //get latest articles with paginate by 10
        $article = Article::latest()->paginate(10);

        return new ArticleResource(true, 'List of Articles', $article);
    }

    public function store(Request $request)
    {
        //create validator
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        //check validator
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/articles', $image->hashName());

        //post article
        $article = Article::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        return new ArticleResource(true, 'Article Posted Successfully', $article);
    }

    public function show(Article $article)
    {
        //get article by id
        return new ArticleResource(true, 'Article is Found', $article);
    }

    public function update(Request $request, Article $article)
    {
        //create validator
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        //check validator
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/articles', $image->hashName());

            //delete old image
            Storage::delete('public/articles/', $article->image);

            //update article
            $article->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {
            //update article but not image
            $article->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        return new ArticleResource(true, 'Article Updated Successfully', $article);
    }

    public function destroy(Article $article)
    {

        //delete storag
        Storage::delete('publc/articles/' . $article->image);

        //delete post
        $article->delete();

        return new ArticleResource(true, 'Article Deleted Successfully', null);
    }
}
