<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    @if ($errors->any())
        <ul class='text-error'>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <label>設備・備品
        <textarea name='equipment' rows='3' placeholder='例：Wi-Fi、電源、モニター、ホワイトボード'>{{ old('equipment', $facility?->equipment) }}</textarea>
    </label>
    @if ($facility?->image_path)
        <div>
            <p>現在の施設画像</p>
            <img src='{{ Storage::url($facility->image_path) }}' alt='{{ $facility->name }}' style='max-width: 320px; height: auto;'>
        </div>
    @endif
    <label>施設名 <input name="name" value="{{ old('name', $facility?->name) }}" required></label>
    <label>種別
        <select name="type">
            <option value="meeting_room" @selected(old('type', $facility?->type) === 'meeting_room')>会議室</option>
            <option value="area" @selected(old('type', $facility?->type) === 'area')>エリア席</option>
        </select>
    </label>
    <label>30分料金 <input type="number" name="price_per_30min"
        value="{{ old('price_per_30min', $facility?->price_per_30min) }}" min="0" required></label>
    <label>定員 <input type="number" name="capacity" value="{{ old('capacity', $facility?->capacity) }}" min="1" required></label>
    <label>説明 <textarea name="description">{{ old('description', $facility?->description) }}</textarea></label>
    <label>画像 <input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
    <input type="hidden" name="is_active" value="0">
    <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $facility?->is_active ?? true))> 公開</label>
    <button type="submit">保存</button>
    <p><small>画像はJPEG・PNG・WebP、5MBまで。公開をオフにするとメンテナンス中として検索対象外になります。</small></p>
</form>
