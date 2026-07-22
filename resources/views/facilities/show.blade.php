@extends('layouts.app')

@section('content')
    {{-- エラーメッセージ表示 --}}
    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; max-width: 900px; margin-left: auto; margin-right: auto;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="max-width: 900px; margin: 0 auto; padding-bottom: 40px;">
        <a href="{{ route('home') }}" style="color: #666; text-decoration: none;">&larr; 一覧に戻る</a>

        {{-- 施設詳細 ＆ 予約入力フォーム --}}
        <div style="background: #fff; border: 1px solid #ddd; padding: 25px; border-radius: 8px; margin-top: 15px;">
            <span style="display: inline-block; padding: 3px 8px; font-size: 0.85em; background: #eee; border-radius: 4px; margin-bottom: 10px;">
                {{ $facility->type === 'meeting_room' || $facility->type === 'room' ? '会議室' : 'エリア席' }}
            </span>

            <h2 style="margin: 0 0 15px 0;">{{ $facility->name }}</h2>
            <p style="color: #555; line-height: 1.6;">{{ $facility->description }}</p>

            @if ($facility->equipment)
                <p style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 0.9em; margin-top: 15px;">
                    <strong>設備・備品:</strong> {{ $facility->equipment }}
                </p>
            @endif

            <div style="display: flex; gap: 30px; margin: 20px 0; padding: 15px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                <div>
                    <span style="font-size: 0.85em; color: #666;">利用可能定員</span>
                    <p style="margin: 5px 0 0 0; font-size: 1.1em; font-weight: bold;">{{ $facility->capacity }} 名</p>
                </div>
                <div>
                    <span style="font-size: 0.85em; color: #666;">利用料金</span>
                    <p style="margin: 5px 0 0 0; font-size: 1.1em; font-weight: bold; color: #007bff;">
                        ¥{{ number_format($facility->price_per_30min) }}
                        <span style="font-size: 0.8em; color: #666; font-weight: normal;">/ 30分</span>
                    </p>
                </div>
            </div>

            <h3 style="margin-bottom: 15px;">予約日時を選択</h3>

            <form action="{{ route('reservations.confirm', $facility->id) }}" method="POST">
                @csrf

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">利用日</label>
                    <input type="date" id="reservation_date" name="date" value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%; box-sizing: border-box;">
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">開始時間</label>
                        <select id="reservation_time" name="start_time" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%; box-sizing: border-box;">
                            @for ($h = 9; $h <= 20; $h++)
                                <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                                <option value="{{ sprintf('%02d:30', $h) }}">{{ sprintf('%02d:30', $h) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">利用時間（コマ数）</label>
                        <select name="duration" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 100%; box-sizing: border-box;">
                            <option value="1">30分</option>
                            <option value="2">1時間</option>
                            <option value="3">1時間30分</option>
                            <option value="4">2時間</option>
                            <option value="6">3時間</option>
                        </select>
                    </div>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: #28a745; color: #fff; font-size: 1.05em; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">
                    予約確認に進む
                </button>
            </form>
        </div>

        {{-- 30日分カレンダー --}}
        @if (!empty($calendarDays))
            <div style="margin-top: 30px; background: #fff; border: 1px solid #ddd; padding: 25px; border-radius: 8px;">
                <h3 style="margin-top: 0; margin-bottom: 10px;">空き状況カレンダー（30日先まで）</h3>
                <p style="font-size: 0.85em; color: #666; margin-bottom: 15px;">
                    ※ <strong>〇</strong>（青色）をクリックすると、上の予約フォームに日付と時間が自動セットされます。
                </p>

                <div style="overflow-x: auto; max-width: 100%; border: 1px solid #eee; border-radius: 4px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85em; text-align: center; white-space: nowrap;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 1px solid #ddd;">
                                <th style="padding: 10px; position: sticky; left: 0; background: #f8f9fa; z-index: 10; min-width: 70px; border-right: 1px solid #ddd;">時間</th>
                                @foreach ($calendarDays as $day)
                                    <th style="padding: 10px; min-width: 65px; border-right: 1px solid #eee;">{{ $day['display'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($calendarDays[0]['time_slots']))
                                @foreach ($calendarDays[0]['time_slots'] as $sIndex => $firstSlot)
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 6px; font-weight: bold; position: sticky; left: 0; background: #fff; border-right: 1px solid #ddd; z-index: 5;">
                                            {{ $firstSlot['start'] }}
                                        </td>

                                        @foreach ($calendarDays as $day)
                                            @php $slot = $day['time_slots'][$sIndex]; @endphp
                                            <td style="padding: 4px; border-right: 1px solid #eee;">
                                                @if ($slot['is_available'])
                                                    <button type="button" class="slot-btn" data-date="{{ $day['date'] }}" data-time="{{ $slot['start'] }}" style="width: 100%; border: none; background: #e7f1ff; color: #0d6efd; font-weight: bold; padding: 4px 0; border-radius: 3px; cursor: pointer;">
                                                        〇
                                                    </button>
                                                @else
                                                    <span style="display: block; width: 100%; background: #f8f9fa; color: #ccc; padding: 4px 0; border-radius: 3px; cursor: not-allowed;">
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

    {{-- イベントリスナー形式にして Blade と JS を完全分離（DEVSENSE構文解析エラーを根本回避） --}}
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