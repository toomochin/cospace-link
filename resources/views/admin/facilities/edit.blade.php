@extends('layouts.app')

@section('content')
    <div
        style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <h2 style="margin-top: 0;">施設情報の編集</h2>

        <form action="{{ route('admin.facilities.update', $facility->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設名</label>
                <input type="text" name="name" value="{{ old('name', $facility->name) }}" required
                    style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">施設タイプ</label>
                <select name="type" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="meeting_room" {{ old('type', $facility->type) === 'meeting_room' ? 'selected' : '' }}>
                        会議室・個室
                    </option>
                    <option value="area" {{ old('type', $facility->type) === 'area' ? 'selected' : '' }}>フリーデスク・エリア</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">説明</label>
                <textarea name="description" rows="4"
                    style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">{{ old('description', $facility->description) }}</textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">30分あたりの料金 (円)</label>
                <input type="number" name="price_per_30min" value="{{ old('price_per_30min', $facility->price_per_30min) }}"
                    required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">定員 (人)</label>
                <input type="number" name="capacity" value="{{ old('capacity', $facility->capacity) }}" required
                    style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ $facility->is_active ? 'checked' : '' }}>
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
@endsection