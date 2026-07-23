@extends('layouts.app')

@section('content')
    <div class="admin-card-container">
        {{-- ヘッダーエリア --}}
        <div class="admin-header">
            <h2 class="admin-header-title">代理予約の登録</h2>
            <a href="{{ route('admin.reservations.index') }}" class="link-secondary">&larr; 一覧に戻る</a>
        </div>

        {{-- エラー表示エリア --}}
        @if ($errors->any())
            <div class="alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.reservations.store') }}" method="POST">
            @csrf

            {{-- 会員選択 --}}
            <div class="form-group">
                <label class="form-label">対象会員</label>
                <select name="user_id" class="form-control {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                    <option value="">会員を選択してください</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 施設選択 --}}
            <div class="form-group">
                <label class="form-label">対象施設</label>
                <select name="facility_id" class="form-control {{ $errors->has('facility_id') ? 'is-invalid' : '' }}">
                    <option value="">施設を選択してください</option>
                    @foreach ($facilities as $facility)
                        <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                            {{ $facility->name }} [{{ $facility->type === 'meeting_room' ? '個室・会議室' : 'エリア席' }}] (定員: {{ $facility->capacity }}名)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 利用日 --}}
            <div class="form-group">
                <label class="form-label">利用日</label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                    class="form-control {{ $errors->has('date') ? 'is-invalid' : '' }}">
            </div>

            {{-- 時間・コマ数（2カラム） --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">開始時間</label>
                    <select name="start_time" class="form-control {{ $errors->has('start_time') ? 'is-invalid' : '' }}">
                        @for ($h = 9; $h <= 20; $h++)
                            <option value="{{ sprintf('%02d:00', $h) }}" {{ old('start_time') === sprintf('%02d:00', $h) ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                            <option value="{{ sprintf('%02d:30', $h) }}" {{ old('start_time') === sprintf('%02d:30', $h) ? 'selected' : '' }}>{{ sprintf('%02d:30', $h) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">利用時間</label>
                    <select name="duration" class="form-control {{ $errors->has('duration') ? 'is-invalid' : '' }}">
                        <option value="1" {{ old('duration') == 1 ? 'selected' : '' }}>30分</option>
                        <option value="2" {{ old('duration') == 2 ? 'selected' : '' }}>1時間</option>
                        <option value="3" {{ old('duration') == 3 ? 'selected' : '' }}>1時間30分</option>
                        <option value="4" {{ old('duration') == 4 ? 'selected' : '' }}>2時間</option>
                        <option value="6" {{ old('duration') == 6 ? 'selected' : '' }}>3時間</option>
                    </select>
                </div>
            </div>

            {{-- 利用人数 --}}
            <div class="form-group">
                <label class="form-label">利用人数 (名)</label>
                <input type="number" name="reserved_seats" value="{{ old('reserved_seats', 1) }}" min="1"
                    class="form-control {{ $errors->has('reserved_seats') ? 'is-invalid' : '' }}">
            </div>

            {{-- ★ 支払方法選択（新規追加） --}}
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label">支払方法</label>
                <select name="payment_type" class="form-control {{ $errors->has('payment_type') ? 'is-invalid' : '' }}">
                    <option value="local" {{ old('payment_type', 'local') === 'local' ? 'selected' : '' }}>現地支払い</option>
                    <option value="free" {{ old('payment_type') === 'free' ? 'selected' : '' }}>無料対応</option>
                    <option value="stripe" {{ old('payment_type') === 'stripe' ? 'selected' : '' }}>クレジットカード（Stripe済）</option>
                </select>
            </div>

            {{-- アクションボタン --}}
            <div class="admin-header-actions">
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">
                    代理予約を登録する
                </button>
                <a href="{{ route('admin.reservations.index') }}" class="btn-cancel">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
@endsection