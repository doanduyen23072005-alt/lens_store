{{-- resources/views/admin/posts/_form.blade.php --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel panel-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="title">Tiêu đề bài viết</label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="{{ old('title', $post->title) }}" placeholder="VD: Cách chọn ống kính phù hợp" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="slug">Đường dẫn (slug)</label>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="{{ old('slug', $post->slug) }}" placeholder="cach-chon-ong-kinh">
                    <div class="form-text">Bỏ trống để tự tạo từ tiêu đề.</div>
                </div>

                <div class="col-12">
                    <label class="form-label" for="excerpt">Tóm tắt</label>
                    <input type="text" id="excerpt" name="excerpt" class="form-control" maxlength="300"
                           value="{{ old('excerpt', $post->excerpt) }}" placeholder="Hiện ở trang danh sách bài viết">
                </div>

                <div class="col-12">
                    <label class="form-label" for="content">Nội dung</label>
                    <textarea id="content" name="content" rows="14" class="form-control" required
                              placeholder="Nội dung bài viết...">{{ old('content', $post->content) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel mb-3">
            <div class="panel-head"><strong>Ảnh đại diện</strong></div>
            <div class="panel-body">
                @if ($post->image)
                    <img src="{{ $post->image_url }}" class="thumb mb-2" style="width:100%;height:140px" alt="">
                @endif
                <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp">
                <div class="form-text">JPG/PNG/WEBP, tối đa 2MB.</div>
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-head"><strong>Xuất bản</strong></div>
            <div class="panel-body">
                <input type="hidden" name="is_published" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                           @checked(old('is_published', $post->is_published ?? true))>
                    <label class="form-check-label" for="is_published">Đăng ngay</label>
                </div>

                <label class="form-label" for="published_at">Ngày đăng</label>
                <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                       value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
                <div class="form-text">Bỏ trống = đăng ngay lúc lưu.</div>

                @if ($post->exists)
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="d-block mt-3 small">
                        Xem bài viết công khai →
                    </a>
                @endif
            </div>
        </div>

        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-ink">{{ $submitLabel }}</button>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-line">Huỷ</a>
        </div>
    </div>
</div>
