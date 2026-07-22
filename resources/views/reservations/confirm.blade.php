@extends('layouts.app')

@section('content')
    <div class="user-container" style="max-width: 600px;">
        <h2 class="user-title" style="margin-bottom: 20px;">予約内容の確認</h2>

        {{-- バリデーションエラー・重複エラーの表示 --}}
        @if ($errors->any())
            <div class="alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="user-card">
            <h3 style="margin: 0; font-size: 1.2rem; color: #1f2937;">{{ $facility->name }}</h3>

            <table class="confirm-table">
                <tr>
                    <th>利用日時</th>
                    <td style="font-weight: bold;">
                        {{ $startAt->format('Y年m月d日(D) H:i') }} 〜 {{ $endAt->format('H:i') }}
                    </td>
                </tr>
                <tr>
                    <th>利用時間</th>
                    <td>{{ $slots * 30 }}分（{{ $slots }}コマ）</td>
                </tr>
                <tr>
                    <th>時間単価</th>
                    <td>¥{{ number_format($facility->price_per_30min) }} / 30分</td>
                </tr>
                <tr style="border-bottom: none;">
                    <th class="confirm-table-total-label">合計料金</th>
                    <td class="confirm-table-total-value">
                        ¥{{ number_format($totalPrice) }}
                    </td>
                </tr>
            </table>

            <form action="{{ route('reservations.store', $facility->id) }}" method="POST">
                @csrf
                <input type="hidden" name="start_time" value="{{ $startAt->toDateTimeString() }}">
                <input type="hidden" name="end_time" value="{{ $endAt->toDateTimeString() }}">
                <input type="hidden" name="total_price" value="{{ $totalPrice }}">

                <div class="confirm-actions">
                    <a href="{{ route('facilities.show', $facility->id) }}" class="btn-user-secondary">
                        やり直す
                    </a>
                    <button type="submit" class="btn-user-submit">
                        この内容で予約を確定する
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection