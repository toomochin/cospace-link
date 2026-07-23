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

                {{-- ★ お支払い方法の選択エリア --}}
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label style="font-weight: bold; font-size: 0.95rem; display: block; margin-bottom: 10px; color: #334155;">
                        お支払い方法を選択してください
                    </label>

                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                            <input type="radio" name="payment_type" value="credit_card" {{ old('payment_type', 'credit_card') === 'credit_card' ? 'checked' : '' }}>
                            <span>💳 クレジットカード決済</span>
                        </label>

                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                            <input type="radio" name="payment_type" value="onsite" {{ old('payment_type') === 'onsite' ? 'checked' : '' }}>
                            <span>🏢 現地払い</span>
                        </label>
                    </div>

                    @error('payment_type')
                        <span style="color: #dc2626; font-size: 0.8rem; display: block; margin-top: 6px;">{{ $message }}</span>
                    @enderror
                </div>

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