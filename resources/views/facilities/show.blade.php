@extends('layouts.app')

@section('content')
    {{-- 重複エラー等の表示 --}}
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
    <div style="max-width: 700px; margin: 0 auto;">
        <a href="{{ route('home') }}" style="color: #666; text-decoration: none;">&larr; 一覧に戻る</a>

        <div style="background: #fff; border: 1px solid #ddd; padding: 25px; border-radius: 8px; margin-top: 15px;">
            <span
                style="display: inline-block; padding: 3px 8px; font-size: 0.85em; background: #eee; border-radius: 4px; margin-bottom: 10px;">
                {{ $facility->type === 'meeting_room' ? '会議室' : 'エリア席' }}
            </span>

            <h2 style="margin: 0 0 15px 0;">{{ $facility->name }}</h2>
            <p style="color: #555; line-height: 1.6;">{{ $facility->description }}</p>

            @if ($facility->equipment)
                <p style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 0.9em; margin-top: 15px;">
                    <strong>設備・備品:</strong> {{ $facility->equipment }}
                </p>
            @endif

            <div
                style="display: flex; gap: 30px; margin: 20px 0; padding: 15px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                <div>
                    <span style="font-size: 0.85em; color: #666;">利用可能定員</span>
                    <p style="margin: 5px 0 0 0; font-size: 1.1em; font-weight: bold;">{{ $facility->capacity }} 名</p>
                </div>
                <div>
                    <span style="font-size: 0.85em; color: #666;">利用料金</span>
                    <p style="margin: 5px 0 0 0; font-size: 1.1em; font-weight: bold; color: #007bff;">
                        ¥{{ number_format($facility->price_per_30min) }} <span
                            style="font-size: 0.8em; color: #666; font-weight: normal;">/ 30分</span>
                    </p>
                </div>
            </div>

            {{-- 予約フォーム（仮設置） --}}
            <h3 style="margin-bottom: 15px;">予約日時を選択</h3>

            <form action="{{ route('reservations.confirm', $facility->id) }}" method="POST">
                @csrf

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">利用日</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}"
                        style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%;">
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">開始時間</label>
                        <select name="start_time"
                            style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%;">
                            @for ($h = 9; $h <= 20; $h++)
                                <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                                <option value="{{ sprintf('%02d:30', $h) }}">{{ sprintf('%02d:30', $h) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">利用時間（コマ数）</label>
                        <select name="duration"
                            style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%;">
                            <option value="1">30分</option>
                            <option value="2">1時間</option>
                            <option value="3">1時間30分</option>
                            <option value="4">2時間</option>
                            <option value="6">3時間</option>
                        </select>
                    </div>
                </div>

                <button type="submit"
                    style="width: 100%; padding: 12px; background: #28a745; color: #fff; font-size: 1.05em; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">
                    予約確認に進む
                </button>
            </form>
        </div>
    </div>
@endsection