{{-- resources/views/pages/show.blade.php --}}
@extends('layouts.shop')
@section('title', $page->title)

@section('content')
<div class="static-page">
    <h1 class="mb-4">{{ $page->title }}</h1>
    <div class="static-content">{!! nl2br(e($page->content)) !!}</div>
</div>

@push('styles')
<style>
    .static-page { max-width: 780px; margin: 0 auto; background: #fff; border: 1px solid var(--line); border-radius: var(--radius-lg, 16px); padding: 36px 40px; }
    .static-content { color: var(--ink); line-height: 1.75; font-size: 15.5px; }
    @media (max-width: 600px) { .static-page { padding: 24px 20px; } }
</style>
@endpush
@endsection
