<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Show all posts
    public function index(Request $request)
    {
        $search = $request->input('search');

        $posts = Post::when($search, function($query, $search) {
                        return $query->where('title', 'like', "%{$search}%")
                                     ->orWhere('content', 'like', "%{$search}%");
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(5);

        return view('posts.index', compact('posts'));
    }

    // Show create form
    public function create()
    {
        return view('posts.create');
    }

    // Store new post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $post = new Post($request->only(['title', 'content']));

        // Handle image upload
        if ($request->hasFile('image')) {
            $filename = time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->storeAs('public/images', $filename);
            $post->image = $filename;
        }

        $post->save();

        return redirect()->route('posts.index')
                         ->with('success', 'Post created successfully.');
    }

    // Show edit form
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    // Update post
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $post->fill($request->only(['title', 'content']));

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image && Storage::exists('public/images/' . $post->image)) {
                Storage::delete('public/images/' . $post->image);
            }

            $filename = time() . '.' . $request->image->getClientOriginalExtension();
            $request->image->storeAs('public/images', $filename);
            $post->image = $filename;
        }

        $post->save();

        return redirect()->route('posts.index')
                         ->with('success', 'Post updated successfully.');
    }

    // Delete post
    public function destroy(Post $post)
    {
        // Delete image if exists
        if ($post->image && Storage::exists('public/images/' . $post->image)) {
            Storage::delete('public/images/' . $post->image);
        }

        $post->delete();

        return redirect()->route('posts.index')
                         ->with('success', 'Post deleted successfully.');
    }
}