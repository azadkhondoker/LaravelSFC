@extends('layouts.master')

@section('content')
<div class="container-fluid posts-page">
    <div class="posts-page-header">
        <div>
            <h1>Posts</h1>
        </div>
        <a href="{{ route('posts.create') }}" class="btn btn-primary">
            <i class="ti-plus me-1" aria-hidden="true"></i> Create post
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card posts-list-card">
        <div class="card-header">
            <div>
                <h2>All posts</h2>
                <small class="text-muted">{{ $posts->count() }} {{ \Illuminate\Support\Str::plural('post', $posts->count()) }}</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table posts-table">
                <thead>
                    <tr>
                        <th scope="col">Post</th>
                        <th scope="col">Post date</th>
                        <th scope="col">Added</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>
                                <button
                                    type="button"
                                    class="post-open-button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#post-details-{{ $post->id }}"
                                >
                                    <span class="post-title">{{ $post->title }}</span>
                                    <span class="post-excerpt">{{ \Illuminate\Support\Str::limit($post->content, 120) }}</span>
                                </button>
                            </td>
                            <td class="post-date">
                                {{ ($post->post_date ?? $post->created_at)->format('M d, Y') }}
                            </td>
                            <td class="post-added">
                                {{ $post->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="post-actions">
                                    <a
                                        href="{{ route('posts.edit', $post) }}"
                                        class="btn btn-light border text-primary"
                                        aria-label="Edit {{ $post->title }}"
                                        title="Edit post"
                                    >
                                        <i class="ti-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form
                                        action="{{ route('posts.destroy', $post) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this post? This action cannot be undone.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-light border text-danger"
                                            aria-label="Delete {{ $post->title }}"
                                            title="Delete post"
                                        >
                                            <i class="ti-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="posts-empty">
                                <strong>No posts yet</strong>
                                <span class="text-muted">Create your first post to get started.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($posts as $post)
        <div
            class="modal fade post-details-modal"
            id="post-details-{{ $post->id }}"
            tabindex="-1"
            aria-labelledby="post-details-title-{{ $post->id }}"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <span class="post-modal-eyebrow">Post details</span>
                            <h2 class="modal-title" id="post-details-title-{{ $post->id }}">{{ $post->title }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="post-modal-date">
                            <i class="ti-calendar me-2" aria-hidden="true"></i>
                            Posted on {{ ($post->post_date ?? $post->created_at)->format('M d, Y') }}
                        </div>
                        <p class="post-modal-content">{{ $post->content }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
