@extends('layouts.app')

@section('content')
    <div
        style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin-top: 0;">施設情報の編集</h2>

        {{-- エラーメッセージ表示エリア --}}
        @if ($errors->any())
            <div
                style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 施設名 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設名</label>
                <input type="text" name="name" value="{{ old('name', $facility->name) }}"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('name') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('name')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 施設タイプ --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設タイプ</label>
                <select name="type"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('type') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                    <option value="meeting_room" {{ old('type', $facility->type) === 'meeting_room' ? 'selected' : '' }}>
                        会議室・個室
                    </option>
                    <option value="area" {{ old('type', $facility->type) === 'area' ? 'selected' : '' }}>
                        フリーデスク・エリア
                    </option>
                </select>
                @error('type')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 説明 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">説明</label>
                <textarea name="description" rows="4"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('description') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">{{ old('description', $facility->description) }}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 料金 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">30分あたりの料金 (円)</label>
                <input type="number" name="price_per_30min" value="{{ old('price_per_30min', $facility->price_per_30min) }}"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('price_per_30min') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('price_per_30min')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 定員 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">定員 (人)</label>
                <input type="number" name="capacity" value="{{ old('capacity', $facility->capacity) }}"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('capacity') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('capacity')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- ★ 施設画像 (プレビュー＆リアルタイム切り替え) --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設画像</label>
                
                {{-- プレビュー表示エリア --}}
                <div style="margin-bottom: 10px;">
                    <span id="preview_label" style="font-size: 0.85em; color: #666; display: block; margin-bottom: 4px;">
                        {{ $facility->image_path ? '現在の画像:' : 'プレビュー:' }}
                    </span>
                    <img id="image_preview" 
                         src="{{ $facility->image_path ? asset('storage/' . $facility->image_path) : '' }}" 
                         alt="施設画像"
                         style="max-width: 200px; height: 130px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; {{ $facility->image_path ? 'display: block;' : 'display: none;' }}">
                </div>

                <input type="file" id="facility_image_input" name="image" accept="image/*"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('image') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                <p style="font-size: 0.8em; color: #666; margin: 4px 0 0 0;">※ 画像を変更する場合のみファイルを選択してください（2MB以下）。</p>
                @error('image')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 公開フラグ --}}
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $facility->is_active) ? 'checked' : '' }}>
                    <span>公開（予約受付を有効にする）</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit"
                    style="padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">更新する</button>
                <a href="{{ route('admin.facilities.index') }}"
                    style="padding: 10px 20px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px;">キャンセル</a>
            </div>
        </form>
    </div>

    {{-- ★ リアルタイムプレビュー用 JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var imageInput = document.getElementById('facility_image_input');
            var previewImage = document.getElementById('image_preview');
            var previewLabel = document.getElementById('preview_label');
            var originalSrc = previewImage ? previewImage.src : '';

            if (imageInput && previewImage) {
                imageInput.addEventListener('change', function (e) {
                    var file = e.target.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            previewImage.src = e.target.result;
                            previewImage.style.display = 'block';
                            if (previewLabel) previewLabel.textContent = '選択中の新しい画像:';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        if (originalSrc) {
                            previewImage.src = originalSrc;
                            if (previewLabel) previewLabel.textContent = '現在の画像:';
                        } else {
                            previewImage.style.display = 'none';
                        }
                    }
                });
            }
        });
    </script>
@endsection