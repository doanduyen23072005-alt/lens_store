{{-- resources/views/products/_form.blade.php --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="code">Mã sản phẩm</label>
                        <input type="text" id="code" name="code" class="form-control"
                               value="{{ old('code', $product->code) }}" placeholder="VD: RF50F18" required>
                        <div class="form-text">Không trùng với ống kính khác.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="name">Tên ống kính</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name', $product->name) }}"
                               placeholder="VD: Canon RF 50mm f/1.8 STM" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="category_id">Phân loại sản phẩm</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">— Chọn phân loại —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Thiếu phân loại phù hợp?
                            <a href="{{ route('categories.create') }}">Thêm phân loại mới</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="price">Giá (VND)</label>
                        <input type="number" id="price" name="price" class="form-control num"
                               value="{{ old('price', $product->price ?? 0) }}" min="0" step="1000" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="quantity">Số lượng</label>
                        <input type="number" id="quantity" name="quantity" class="form-control num"
                               value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="description">Mô tả</label>
                        <textarea id="description" name="description" rows="6" class="form-control"
                                  placeholder="Tiêu cự, khẩu độ, ngàm, chống rung, tình trạng…">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-head"><strong>Ảnh sản phẩm</strong></div>
            <div class="panel-body">
                <div class="mb-3 text-center">
                    <img id="imagePreview"
                         src="{{ $product->image ? $product->image_url : 'https://placehold.co/400x300/F1F2F4/9AA1AA?text=Chua+co+anh' }}"
                         alt="Ảnh xem trước"
                         style="width:100%;max-height:230px;object-fit:cover;border-radius:11px;border:1px solid var(--line)">
                </div>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                <div class="form-text">JPG, PNG hoặc WEBP, tối đa 2MB. Để trống nếu giữ ảnh cũ.</div>
            </div>
        </div>

        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-ink">{{ $submitLabel }}</button>
            <a href="{{ route('products.index') }}" class="btn btn-line">Huỷ</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('imagePreview').src = URL.createObjectURL(file);
        }
    });
</script>
@endpush