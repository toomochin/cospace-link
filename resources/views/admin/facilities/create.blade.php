@extends('layouts.app')

@section('content')
    <div class="admin-card-container">
        {{-- ヘッダーエリア --}}
        <div class="admin-header">
            <h2 class="admin-header-title">新規施設の登録</h2>
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

        <form action="{{ route('admin.facilities.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 施設名 --}}
            <div class="form-group">
                <label class="form-label">施設名</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 施設タイプ --}}
            <div class="form-group">
                <label class="form-label">施設タイプ</label>
                <select name="type" class="form-control {{ $errors->has('type') ? 'is-invalid' : '' }}">
                    <option value="">選択してください</option>
                    <option value="meeting_room" {{ old('type') === 'meeting_room' ? 'selected' : '' }}>会議室・個室</option>
                    <option value="area" {{ old('type') === 'area' ? 'selected' : '' }}>フリーデスク・エリア</option>
                </select>
                @error('type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 説明 --}}
            <div class="form-group">
                <label class="form-label">説明</label>
                <textarea name="description" rows="4"
                    class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}">{{ old('description') }}</textarea>
                @error('description')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 料金 --}}
            <div class="form-group">
                <label class="form-label">30分あたりの料金 (円)</label>
                <input type="number" name="price_per_30min" value="{{ old('price_per_30min') }}"
                    class="form-control {{ $errors->has('price_per_30min') ? 'is-invalid' : '' }}">
                @error('price_per_30min')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 定員 --}}
            <div class="form-group">
                <label class="form-label">定員 (人)</label>
                <input type="number" name="capacity" value="{{ old('capacity') }}"
                    class="form-control {{ $errors->has('capacity') ? 'is-invalid' : '' }}">
                @error('capacity')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 施設画像 --}}
            <div class="form-group">
                <label class="form-label">施設画像</label>

                {{-- 選択した画像のプレビュー表示エリア --}}
                <div id="image_preview_container" class="preview-container" style="display: none;">
                    <p class="preview-title">選択中の画像プレビュー:</p>
                    <img id="image_preview" src="" alt="プレビュー" class="preview-image">
                </div>

                <input type="file" id="facility_image_input" name="image" accept="image/*"
                    class="form-control {{ $errors->has('image') ? 'is-invalid' : '' }}">
                <p class="form-help">※ 2MB以下の画像ファイル（jpg, png, webp等）をアップロードできます。</p>
                @error('image')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- 公開フラグ --}}
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>公開（予約受付を有効にする）</span>
                </label>
            </div>

            {{-- アクションボタン --}}
            <div class="admin-header-actions">
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                    登録する
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
            var previewContainer = document.getElementById('image_preview_container');
            var previewImage = document.getElementById('image_preview');

            if (imageInput) {
                imageInput.addEventListener('change', function (e) {
                    var file = e.target.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            previewImage.src = e.target.result;
                            previewContainer.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewImage.src = '';
                        previewContainer.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection