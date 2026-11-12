@extends('admin.layout')
@section('title', $article->exists ? 'Edit article' : 'Write an article')
@section('content')
    <div class="page-heading"><div><span class="eyebrow">SERENOVIA JOURNAL</span><h1>{{ $article->exists ? 'Edit article' : 'Write a story' }}</h1><p>Stories are public when their status is set to Published.</p></div><a class="button secondary" href="{{ route('admin.articles.index') }}">← All articles</a></div>
    <form class="admin-form" method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
        @csrf @if($article->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="title">ARTICLE TITLE *</label><input id="title" name="title" value="{{ old('title', $article->title) }}" required></div>
            <div class="field"><label for="slug">PAGE ADDRESS</label><input id="slug" name="slug" value="{{ old('slug', $article->slug) }}" placeholder="Leave blank to make from title">@error('slug')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field"><label for="category">CATEGORY *</label><input id="category" name="category" value="{{ old('category', $article->category ?: 'Travel journal') }}" required></div>
            <div class="field"><label for="status">VISIBILITY</label><select id="status" name="status"><option value="draft" @selected(old('status', $article->status ?: 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $article->status) === 'published')>Published</option></select></div>
            <div class="field full"><label for="excerpt">SHORT INTRODUCTION</label><textarea id="excerpt" name="excerpt" rows="3" maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea></div>
            <div class="field full"><label for="body">ARTICLE BODY *</label><textarea class="tinymce-editor" id="body" name="body" rows="16" required>{{ old('body', $article->body) }}</textarea><small>Format your story with headings, links, lists, tables, and emphasis.</small>@error('body')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="field full"><label for="image">FEATURED PHOTO</label>@if($article->image_path)<img class="image-current" src="{{ asset($article->image_path) }}" alt="">@endif<input id="image" type="file" name="image" accept="image/*"><small>JPG, PNG or WebP. Maximum 8 MB.</small></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save article <span>↗</span></button><a class="button secondary" href="{{ route('admin.articles.index') }}">Cancel</a></div>
    </form>
@endsection
