@extends('layouts.master')

@section('content')

<a href="{{ route('posts.create') }}">New Post</a>

@foreach ($posts as $post)
    <p>
        {{ $post->title }}
        <a href="{{ route('posts.edit', $post) }}">Edit</a>
        <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display:inline">
            @csrf
            @method('DELETE')
            <button>Delete</button>
        </form>
    </p>
@endforeach

@endsection
