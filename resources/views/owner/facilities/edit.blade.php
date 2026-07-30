@extends('layouts.app')

@section('content')
<div class='admin-card'>
    <div class='admin-header-flex'>
        <h2 class='admin-header-title'>施設編集</h2>
        <a href='{{ route('owner.facilities.index') }}' class='link-secondary'>&larr; 施設一覧に戻る</a>
    </div>
    @include('owner.facilities.form', [
        'action' => route('owner.facilities.update', $facility),
        'method' => 'PUT',
        'facility' => $facility,
    ])
</div>
@endsection
