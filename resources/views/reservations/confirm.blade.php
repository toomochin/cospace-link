@extends('layouts.app')

@section('content')
    <div style="max-width: 600px; margin: 0 auto;">
        <h2>予約内容の確認</h2>

        @if ($errors->any())
            <div style="color: red; margin-bottom: 15px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-top: 15px;">
            <h3>{{ $facility->name }}</h3>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="text-align: left; padding: 8px 0; color: #666;">利用日時</th>
                    <td style="text-align: right; font-weight: bold;">
                        {{ $startAt->format('Y年m月d日(D) H:i') }} 〜 {{ $endAt->format('H:i') }}
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="text-align: left; padding: 8px 0; color: #666;">利用時間</th>
                    <td style="text-align: right;">{{ $slots * 30 }}分（{{ $slots }}コマ）</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="text-align: left; padding: 8px 0; color: #666;">時間単価</th>
                    <td style="text-align: right;">¥{{ number_format($facility->price_per_30min) }} / 30分</td>
                </tr>
                <tr>
                    <th style="text-align: left; padding: 12px 0; font-size: 1.1em;">合計料金</th>
                    <td style="text-align: right; font-size: 1.3em; font-weight: bold; color: #007bff;">
                        ¥{{ number_format($totalPrice) }}
                    </td>
                </tr>
            </table>

            <form action="{{ route('reservations.store', $facility->id) }}" method="POST">
                @csrf
                <input type="hidden" name="start_time" value="{{ $startAt->toDateTimeString() }}">
                <input type="hidden" name="end_time" value="{{ $endAt->toDateTimeString() }}">
                <input type="hidden" name="total_price" value="{{ $totalPrice }}">

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <a href="{{ route('facilities.show', $facility->id) }}"
                        style="flex: 1; text-align: center; padding: 10px; background: #eee; color: #333; text-decoration: none; border-radius: 4px;">
                        やり直す
                    </a>
                    <button type="submit"
                        style="flex: 2; padding: 10px; background: #28a745; color: #fff; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">
                        この内容で予約を確定する
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection