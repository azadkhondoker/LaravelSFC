@csrf

<div class="posts-form-grid">
    <div class="posts-field">
        <label for="title">Post title <span class="text-danger">*</span></label>
        <input
            id="title"
            type="text"
            name="title"
            value="{{ old('title', $post->title ?? '') }}"
            class="form-control @error('title') is-invalid @enderror"
            maxlength="255"
            required
        >
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="posts-field">
        <label for="post_date">Post date <span class="text-danger">*</span></label>
        <input
            id="post_date"
            type="date"
            name="post_date"
            value="{{ old('post_date', isset($post) ? ($post->post_date ?? $post->created_at)->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="form-control @error('post_date') is-invalid @enderror"
            required
        >
        <small class="form-text">The date this post was published.</small>
        @error('post_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="posts-field posts-field-full">
        <label for="content">Post content <span class="text-danger">*</span></label>
        <textarea
            id="content"
            name="content"
            rows="8"
            class="form-control @error('content') is-invalid @enderror"
            required
        >{{ old('content', $post->content ?? '') }}</textarea>
        @error('content')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
