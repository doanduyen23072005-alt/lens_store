{{-- resources/views/categories/_form.blade.php --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel panel-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="code">Mã phân loại</label>
                    <input type="text" id="code" name="code" class="form-control"
                           value="{{ old('code', $category->code) }}" placeholder="VD: PRIME" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="name">Tên phân loại</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="{{ old('name', $category->name) }}" placeholder="VD: Ống kính một tiêu cự" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label" for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="{{ old('slug', $category->slug) }}" placeholder="ong-kinh-mot-tieu-cu">
                    <div class="form-text">Bỏ trống để hệ thống tự tạo từ tên.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="sort_order">Thứ tự hiển thị</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control num"
                           value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
                </div>

                <div class="col-12">
                    <label class="form-label" for="description">Mô tả</label>
                    <textarea id="description" name="description" rows="5" class="form-control"
                              placeholder="Nhóm này gồm những ống kính nào?">{{ old('description', $category->description) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-head"><strong>Hiển thị</strong></div>
            <div class="panel-body">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $category->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Cho phép chọn khi thêm sản phẩm</label>
                </div>
                <div class="form-text mt-2">Tắt để ẩn phân loại khỏi danh sách chọn mà vẫn giữ dữ liệu cũ.</div>
            </div>
        </div>

        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-ink">{{ $submitLabel }}</button>
            <a href="{{ route('categories.index') }}" class="btn btn-line">Huỷ</a>
        </div>
    </div>
</div>