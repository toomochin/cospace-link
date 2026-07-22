@extends('layouts.app')

@section('content')
    <div
        style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin-top: 0;">新規施設の登録</h2>

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

        {{-- ★ enctype="multipart/form-data" を追加 --}}
        <form action="{{ route('admin.facilities.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 施設名 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設名</label>
                <input type="text" name="name" value="{{ old('name') }}"
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
                    <option value="">選択してください</option>
                    <option value="meeting_room" {{ old('type') === 'meeting_room' ? 'selected' : '' }}>会議室・個室</option>
                    <option value="area" {{ old('type') === 'area' ? 'selected' : '' }}>フリーデスク・エリア</option>
                </select>
                @error('type')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 説明 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">説明</label>
                <textarea name="description" rows="4"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('description') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 料金 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">30分あたりの料金 (円)</label>
                <input type="number" name="price_per_30min" value="{{ old('price_per_30min') }}"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('price_per_30min') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('price_per_30min')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 定員 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">定員 (人)</label>
                <input type="number" name="capacity" value="{{ old('capacity') }}"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('capacity') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                @error('capacity')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>

            {{-- 施設画像 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設画像</label>

                {{-- ★ 選択した画像のプレビュー表示エリア --}}
                <div id="image_preview_container" style="margin-bottom: 10px; display: none;">
                    <p style="font-size: 0.85em; color: #666; margin-bottom: 4px;">選択中の画像プレビュー:</p>
                    <img id="image_preview" src="" alt="プレビュー" style="max-width: 200px; height: 130px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                </div>

                <input type="file" id="facility_image_input" name="image" accept="image/*"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('image') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                <p style="font-size: 0.8em; color: #666; margin: 4px 0 0 0;">※ 2MB以下の画像ファイル（jpg, png, webp等）をアップロードできます。</p>
                @error('image')
                    <span style="color: #dc2626; font-size: 0.85em;">{{ $message }}</span>
                @enderror
            </div>
            {{-- 公開フラグ --}}
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>公開（予約受付を有効にする）</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit"
                    style="padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">登録する</button>
                <a href="{{ route('admin.facilities.index') }}"
                    style="padding: 10px 20px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px;">キャンセル</a>
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