@extends('layouts.app')

@section('content')
    <div style="max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0;">代理予約の登録</h2>
            <a href="{{ route('admin.reservations.index') }}" style="color: #2563eb; text-decoration: none;">&larr; 一覧に戻る</a>
        </div>

        {{-- エラー表示エリア --}}
        @if ($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.reservations.store') }}" method="POST">
            @csrf

            {{-- 会員選択 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">対象会員</label>
                <select name="user_id" style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('user_id') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                    <option value="">会員を選択してください</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 施設選択 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">対象施設</label>
                <select name="facility_id" style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('facility_id') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                    <option value="">施設を選択してください</option>
                    @foreach ($facilities as $facility)
                        <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                            {{ $facility->name }} [{{ $facility->type === 'meeting_room' ? '個室・会議室' : 'エリア席' }}] (定員: {{ $facility->capacity }}名)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 利用日 --}}
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">利用日</label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('date') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
            </div>

            {{-- 時間・コマ数 --}}
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">開始時間</label>
                    <select name="start_time" style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('start_time') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                        @for ($h = 9; $h <= 20; $h++)
                            <option value="{{ sprintf('%02d:00', $h) }}" {{ old('start_time') === sprintf('%02d:00', $h) ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                            <option value="{{ sprintf('%02d:30', $h) }}" {{ old('start_time') === sprintf('%02d:30', $h) ? 'selected' : '' }}>{{ sprintf('%02d:30', $h) }}</option>
                        @endfor
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">利用時間</label>
                    <select name="duration" style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('duration') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
                        <option value="1" {{ old('duration') == 1 ? 'selected' : '' }}>30分</option>
                        <option value="2" {{ old('duration') == 2 ? 'selected' : '' }}>1時間</option>
                        <option value="3" {{ old('duration') == 3 ? 'selected' : '' }}>1時間30分</option>
                        <option value="4" {{ old('duration') == 4 ? 'selected' : '' }}>2時間</option>
                        <option value="6" {{ old('duration') == 6 ? 'selected' : '' }}>3時間</option>
                    </select>
                </div>
            </div>

            {{-- 利用人数 --}}
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">利用人数 (名)</label>
                <input type="number" name="reserved_seats" value="{{ old('reserved_seats', 1) }}" min="1"
                    style="width: 100%; padding: 8px; border: 1px solid {{ $errors->has('reserved_seats') ? '#dc2626' : '#ccc' }}; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 10px 20px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    代理予約を登録する
                </button>
                <a href="{{ route('admin.reservations.index') }}" style="padding: 10px 20px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px;">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
@endsection