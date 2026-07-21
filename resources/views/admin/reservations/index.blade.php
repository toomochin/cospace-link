@extends('layouts.app')

@section('content')
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>管理画面：全予約一覧</h2>
            <a href="{{ route('admin.facilities.index') }}"
                style="padding: 8px 16px; background: #4b5563; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9em;">施設管理に戻る</a>
        </div>

        <table
            style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">予約者</th>
                    <th style="padding: 12px;">施設名</th>
                    <th style="padding: 12px;">利用日時</th>
                    <th style="padding: 12px;">ステータス</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px;">{{ $reservation->id }}</td>
                        <td style="padding: 12px;">{{ $reservation->user->name ?? '不明' }}</td>
                        <td style="padding: 12px; font-weight: bold;">{{ $reservation->reservable->name ?? '施設情報なし' }}</td>
                        <td style="padding: 12px; font-size: 0.9em;">
                            {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y/m/d H:i') }} 〜
                            {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
                        </td>
                        <td style="padding: 12px;">
                            <span
                                style="padding: 2px 8px; font-size: 0.8em; border-radius: 4px; 
                                            {{ $reservation->status === 'confirmed' ? 'background: #e6f4ea; color: #137333;' : ($reservation->status === 'cancelled' ? 'background: #fce8e6; color: #c5221f;' : 'background: #fff3cd; color: #856404;') }}">
                                {{ $reservation->status === 'confirmed' ? '予約確定' : ($reservation->status === 'cancelled' ? 'キャンセル済' : '決済待ち') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #6b7280;">予約データはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 15px;">
            {{ $reservations->links() }}
        </div>
    </div>
@endsection