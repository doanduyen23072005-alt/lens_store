{{-- resources/views/admin/pages/_form.blade.php --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel panel-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="title">Tiêu đề trang</label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="{{ old('title', $page->title) }}" placeholder="VD: Về chúng tôi" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="slug">Đường dẫn (slug)</label>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="{{ old('slug', $page->slug) }}" placeholder="ve-chung-toi">
                    <div class="form-text">Bỏ trống để tự tạo từ tiêu đề.</div>
                </div>

                <div class="col-12">
                    <label class="form-label" for="meta_description">Mô tả ngắn (SEO)</label>
                    <input type="text" id="meta_description" name="meta_description" class="form-control"
                           value="{{ old('meta_description', $page->meta_description) }}" maxlength="255">
                </div>

                <div class="col-12">
                    <label class="form-label" for="content">Nội dung</label>
                    <textarea id="content" name="content" rows="14" class="form-control" required
                              placeholder="Nội dung trang...">{{ old('content', $page->content) }}</textarea>
                    <div class="form-text">Xuống dòng sẽ được giữ nguyên khi hiển thị.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-head"><strong>Hiển thị</strong></div>
            <div class="panel-body">
                <input type="hidden" name="is_published" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                           @checked(old('is_published', $page->is_published ?? true))>
                    <label class="form-check-label" for="is_published">Đã xuất bản</label>
                </div>
                <div class="form-text mt-2">Tắt để ẩn trang khỏi website mà vẫn giữ nội dung.</div>

                @if ($page->exists)
                    <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="d-block mt-3 small">
                        Xem trang công khai →
                    </a>
                @endif
            </div>
        </div>

        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-ink">{{ $submitLabel }}</button>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-line">Huỷ</a>
        </div>
    </div>
</div>
