{{-- resources/views/blog/index.blade.php --}}
@extends('layouts.shop')
@section('title', 'Blog / Tin tức')

@section('content')
<div class="mb-4">
    <h1 class="mb-1">Blog / Tin tức nhiếp ảnh</h1>
    <p class="text-muted mb-0">Kinh nghiệm chọn ống kính, xu hướng nhiếp ảnh và tin tức ngành hàng.</p>
</div>

@if ($posts->count())
    <div class="row g-4">
        @foreach ($posts as $post)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('blog.show', $post->slug) }}" class="post-card">
                    @if ($post->image)
                        <img src="{{ $post->image_url }}" alt="{{ $post->title }}">
                    @else
                        <div class="post-card-noimg">Lens Store</div>
                    @endif
                    <div class="post-card-body">
                        <div class="post-card-date">{{ $post->published_at?->format('d/m/Y') }}</div>
                        <h3 class="post-card-title">{{ $post->title }}</h3>
                        <p class="post-card-excerpt">{{ $post->excerpt_text }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $posts->links() }}
    </div>
@else
    <div class="text-center text-muted py-5">
        <h3>Chưa có bài viết nào</h3>
        <p>Quay lại sau để đọc tin tức mới nhất từ Lens Store.</p>
    </div>
@endif

@push('styles')
<style>
    .post-card {
        display: flex; flex-direction: column;
        background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg, 16px);
        overflow: hidden; height: 100%; text-decoration: none; color: inherit;
        box-shadow: var(--shadow-xs, 0 1px 2px rgba(20,22,26,.05));
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .post-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md, 0 16px 36px rgba(20,22,26,.12)); color: inherit; }
    .post-card img { width: 100%; height: 170px; object-fit: cover; background: #F6F7F8; }
    .post-card-noimg {
        height: 170px; display: grid; place-items: center;
        background: linear-gradient(135deg, var(--barrel), #2A2E37); color: var(--coating);
        font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 18px;
    }
    .post-card-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .post-card-date { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
    .post-card-title { font-size: 16px; margin: 0; line-height: 1.35; }
    .post-card-excerpt { font-size: 13px; color: var(--muted); margin: 0; }
</style>
@endpush
@endsection
