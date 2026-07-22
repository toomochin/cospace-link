@extends('layouts.app')

@section('content')
    <div class="user-container" style="padding-bottom: 40px;">

        {{-- エラーメッセージ表示 --}}
        @if ($errors->any())
            <div class="alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('home') }}" class="btn-user-secondary">&larr; 一覧に戻る</a>

        {{-- 施設詳細 ＆ 予約入力フォーム --}}
        <div class="user-card" style="margin-top: 15px;">
            <span class="user-badge user-badge-gray" style="margin-bottom: 12px;">
                {{ $facility->type === 'meeting_room' || $facility->type === 'room' ? '会議室' : 'エリア席' }}
            </span>

            {{-- 施設画像表示エリア --}}
            @if ($facility->image_path)
                <div>
                    <img src="{{ asset('storage/' . $facility->image_path) }}" alt="{{ $facility->name }}" class="facility-detail-img">
                </div>
            @endif

            <h2 class="user-title" style="margin-bottom: 12px;">{{ $facility->name }}</h2>
            <p style="color: #4b5563; line-height: 1.6; margin: 0;">{{ $facility->description }}</p>

            @if ($facility->equipment)
                <div class="facility-equipment-box">
                    <strong>設備・備品:</strong> {{ $facility->equipment }}
                </div>
            @endif

            <div class="facility-meta-row">
                <div>
                    <span class="facility-meta-label">利用可能定員</span>
                    <p class="facility-meta-value">{{ $facility->capacity }} 名</p>
                </div>
                <div>
                    <span class="facility-meta-label">利用料金</span>
                    <p class="facility-meta-value price">
                        ¥{{ number_format($facility->price_per_30min) }}
                        <span class="facility-meta-unit">/ 30分</span>
                    </p>
                </div>
            </div>

            <h3 style="margin-bottom: 15px; font-size: 1.2rem; color: #1f2937;">予約日時を選択</h3>

            <form action="{{ route('reservations.confirm', $facility->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">利用日</label>
                    <input type="date" id="reservation_date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" class="form-control">
                </div>

                <div class="form-row" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label">開始時間</label>
                        <select id="reservation_time" name="start_time" class="form-control">
                            @for ($h = 9; $h <= 20; $h++)
                                <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                                <option value="{{ sprintf('%02d:30', $h) }}">{{ sprintf('%02d:30', $h) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">利用時間（コマ数）</label>
                        <select name="duration" class="form-control">
                            <option value="1">30分</option>
                            <option value="2">1時間</option>
                            <option value="3">1時間30分</option>
                            <option value="4">2時間</option>
                            <option value="6">3時間</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-user-submit">
                    予約確認に進む
                </button>
            </form>
        </div>

        {{-- 30日分カレンダー --}}
        @if (!empty($calendarDays))
            <div class="calendar-card">
                <h3 style="margin-top: 0; margin-bottom: 10px; font-size: 1.2rem; color: #1f2937;">空き状況カレンダー（30日先まで）</h3>
                <p style="font-size: 0.85em; color: #6b7280; margin-bottom: 15px;">
                    ※ <strong style="color: #2563eb;">〇</strong>（青色）をクリックすると、上の予約フォームに日付と時間が自動セットされます。
                </p>

                <div class="calendar-scroll-wrapper">
                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th class="calendar-sticky-header">時間</th>
                                @foreach ($calendarDays as $day)
                                    <th style="min-width: 65px;">{{ $day['display'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($calendarDays[0]['time_slots']))
                                @foreach ($calendarDays[0]['time_slots'] as $sIndex => $firstSlot)
                                    <tr>
                                        <td class="calendar-sticky-col">
                                            {{ $firstSlot['start'] }}
                                        </td>

                                        @foreach ($calendarDays as $day)
                                            @php $slot = $day['time_slots'][$sIndex]; @endphp
                                            <td style="padding: 4px;">
                                                @if ($slot['is_available'])
                                                    <button type="button" class="slot-btn" data-date="{{ $day['date'] }}" data-time="{{ $slot['start'] }}">
                                                        〇
                                                    </button>
                                                @else
                                                    <span class="slot-disabled">
                                                        ✕
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- イベントリスナー形式の JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var buttons = document.querySelectorAll('.slot-btn');
            buttons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var dateStr = this.getAttribute('data-date');
                    var timeStr = this.getAttribute('data-time');

                    var dateInput = document.getElementById('reservation_date');
                    var timeSelect = document.getElementById('reservation_time');

                    if (dateInput) dateInput.value = dateStr;
                    if (timeSelect) {
                        for (var i = 0; i < timeSelect.options.length; i++) {
                            if (timeSelect.options[i].value === timeStr) {
                                timeSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }

                    if (dateInput) {
                        window.scrollTo({
                            top: dateInput.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
@endsection