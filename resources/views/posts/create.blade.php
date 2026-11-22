@extends('layouts.master')

@section('content')
<div class="container-fluid posts-page">
    <div class="posts-page-header">
        <div>
            <h1>Create post</h1>
            <p>Write a post and choose the date it was published.</p>
        </div>
        <a href="{{ route('posts.index') }}" class="btn btn-light border">
            <i class="ti-arrow-left me-1" aria-hidden="true"></i> Back to posts
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please review the form.</strong> Some details need your attention.
        </div>
    @endif

    <div class="card posts-form-card">
        <div class="card-header">
            <i class="ti-write me-2" aria-hidden="true"></i> Post information
        </div>
        <div class="card-body">
            <form action="{{ route('posts.store') }}" method="POST">
                @include('posts._form')

                <div class="posts-form-footer">
                    <a href="{{ route('posts.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti-check me-1" aria-hidden="true"></i> Create post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
