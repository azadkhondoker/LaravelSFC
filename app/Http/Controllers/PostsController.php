<?php

namespace App\Http\Controllers;

use App\Models\posts;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = posts::orderByDesc('post_date')
            ->orderByDesc('created_at')
            ->get();
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $data = $request->validate([
            'title' => 'required|max:255',
            'content'  => 'required',
           'post_date' => 'required|date',
        ]);
        posts::create($data);
        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(posts $posts)
    {
        return view('posts.show', compact('posts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(posts $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, posts $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'post_date' => 'required|date',
        ]);
        $post->update($data);
        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(posts $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
