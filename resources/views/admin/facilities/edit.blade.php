@extends('layouts.app')

@section('content')
    <div class="admin-card-container">
        {{-- ヘッダーエリア --}}
        <div class="admin-header">
            <h2 class="admin-header-title">施設情報の編集</h2>
            <a href="{{ route('admin.facilities.index') }}" class="link-secondary">&larr; 一覧に戻る</a>
        </div>

        {{-- エラーメッセージ表示エリア --}}
        @if ($errors->any())
            <div class="alert-danger">
                <ul>
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
            <div class="form-group">
                <label class="form-label">施設名</label>
                <input type="text" name="name" value="{{ old('name', $facility->name) }}"
                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 施設タイプ --}}
            <div class="form-group">
                <label class="form-label">施設タイプ</label>
                <select name="type" class="form-control {{ $errors->has('type') ? 'is-invalid' : '' }}">
                    <option value="meeting_room" {{ old('type', $facility->type) === 'meeting_room' ? 'selected' : '' }}>
                        会議室・個室
                    </option>
                    <option value="area" {{ old('type', $facility->type) === 'area' ? 'selected' : '' }}>
                        フリーデスク・エリア
                    </option>
                </select>
                @error('type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 説明 --}}
            <div class="form-group">
                <label class="form-label">説明</label>
                <textarea name="description" rows="4"
                    class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}">{{ old('description', $facility->description) }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 料金 --}}
            <div class="form-group">
                <label class="form-label">30分あたりの料金 (円)</label>
                <input type="number" name="price_per_30min" value="{{ old('price_per_30min', $facility->price_per_30min) }}"
                    class="form-control {{ $errors->has('price_per_30min') ? 'is-invalid' : '' }}">
                @error('price_per_30min')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 定員 --}}
            <div class="form-group">
                <label class="form-label">定員 (人)</label>
                <input type="number" name="capacity" value="{{ old('capacity', $facility->capacity) }}"
                    class="form-control {{ $errors->has('capacity') ? 'is-invalid' : '' }}">
                @error('capacity')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 施設画像 --}}
            <div class="form-group">
                <label class="form-label">施設画像</label>
                
                {{-- プレビュー表示エリア --}}
                <div class="preview-container">
                    <span id="preview_label" class="preview-title" style="display: block;">
                        {{ $facility->image_path ? '現在の画像:' : 'プレビュー:' }}
                    </span>
                    <img id="image_preview" 
                         src="{{ $facility->image_path ? asset('storage/' . $facility->image_path) : '' }}" 
                         alt="施設画像"
                         class="preview-image"
                         style="{{ $facility->image_path ? 'display: block;' : 'display: none;' }}">
                </div>

                <input type="file" id="facility_image_input" name="image" accept="image/*"
                    class="form-control {{ $errors->has('image') ? 'is-invalid' : '' }}">
                <p class="form-help">※ 画像を変更する場合のみファイルを選択してください（2MB以下）。</p>
                @error('image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 公開フラグ --}}
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $facility->is_active) ? 'checked' : '' }}>
                    <span>公開（予約受付を有効にする）</span>
                </label>
            </div>

            {{-- アクションボタン --}}
            <div class="admin-header-actions">
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                    更新する
                </button>
                <a href="{{ route('admin.facilities.index') }}" class="btn-cancel">
                    キャンセル
                </a>
            </div>
        </form>
    </div>

    {{-- リアルタイムプレビュー用 JavaScript --}}
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