{{-- resources/views/shop/partials/stars.blade.php --}}
{{-- Dùng: @include('shop.partials.stars', ['rating' => $product->reviews_avg_rating, 'count' => $product->reviews_count]) --}}
@php $rounded = (int) round($rating ?? 0); @endphp
<span class="stars" title="{{ number_format($rating ?? 0, 1) }}/5">
    @for ($i = 1; $i <= 5; $i++)
        <span class="{{ $i <= $rounded ? 'star-filled' : 'star-empty' }}">★</span>
    @endfor
</span>
@if (isset($count))
    <span class="text-muted" style="font-size:12px">({{ $count }})</span>
@endif
