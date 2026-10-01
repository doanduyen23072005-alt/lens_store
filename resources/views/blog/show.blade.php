{{-- resources/views/blog/show.blade.php --}}
@extends('layouts.shop')
@section('title', $post->title)

@section('content')
<a href="{{ route('blog.index') }}" class="text-muted" style="font-size:14px">← Về danh sách bài viết</a>

<article class="post-article mt-3">
    <h1 class="mb-2">{{ $post->title }}</h1>
    <div class="text-muted mb-3" style="font-size:13px">
        {{ $post->published_at?->format('d/m/Y') }}
        @if ($post->author) · {{ $post->author->name }} @endif
    </div>

    @if ($post->image)
        <img src="{{ $post->image_url }}" alt="{{ $post->title }}" class="post-article-image">
    @endif

    <div class="post-article-content">{!! nl2br(e($post->content)) !!}</div>
</article>

@if ($related->count())
    <h2 class="mt-5 mb-3" style="font-size:20px">Bài viết khác</h2>
    <div class="row g-3">
        @foreach ($related as $item)
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route('blog.show', $item->slug) }}" class="text-decoration-none text-dark">
                    <div class="lens-card">
                        @if ($item->image)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                        @else
                            <div class="no-img">Chưa có ảnh</div>
                        @endif
                        <div class="lens-body">
                            <h3 style="font-size:15px;margin:0">{{ $item->title }}</h3>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif

@push('styles')
<style>
    .post-article { max-width: 780px; margin: 0 auto; background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg, 16px); padding: 32px 36px; }
    .post-article-image { width: 100%; max-height: 380px; object-fit: cover; border-radius: 12px; margin: 12px 0 20px; }
    .post-article-content { line-height: 1.75; font-size: 15.5px; color: var(--ink); }
    @media (max-width: 600px) { .post-article { padding: 22px 18px; } }
</style>
@endpush
@endsection
