@extends('layouts.app')

@section('content')
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>管理画面：施設一覧</h2>
            <div>
                <a href="{{ route('admin.facilities.create') }}"
                    style="padding: 8px 16px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9em;">+
                    新規施設登録</a>
                <a href="{{ route('admin.reservations.index') }}"
                    style="padding: 8px 16px; background: #4b5563; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9em; margin-left: 10px;">全予約一覧を見る</a>
            </div>
        </div>

        @if (session('status'))
            <div
                style="color: green; margin-bottom: 15px; padding: 10px; background: #e6ffe6; border: 1px solid #b3ffb3; border-radius: 4px;">
                {{ session('status') }}
            </div>
        @endif

        <table
            style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">施設名</th>
                    <th style="padding: 12px;">30分単価</th>
                    <th style="padding: 12px;">定員</th>
                    <th style="padding: 12px;">状態</th>
                    <th style="padding: 12px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($facilities as $facility)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px;">{{ $facility->id }}</td>
                        <td style="padding: 12px; font-weight: bold;">{{ $facility->name }}</td>
                        <td style="padding: 12px;">¥{{ number_format($facility->price_per_30min) }}</td>
                        <td style="padding: 12px;">{{ $facility->capacity }}名</td>
                        <td style="padding: 12px;">
                            <span
                                style="padding: 2px 8px; font-size: 0.8em; border-radius: 4px; {{ $facility->is_active ? 'background: #e6f4ea; color: #137333;' : 'background: #fce8e6; color: #c5221f;' }}">
                                {{ $facility->is_active ? '公開中' : '非公開' }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <a href="{{ route('admin.facilities.edit', $facility->id) }}"
                                style="color: #2563eb; text-decoration: none;">編集</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #6b7280;">登録されている施設はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection