@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2>施設編集</h2>
        @include('owner.facilities.form', [
            'action' => route('owner.facilities.update', $facility),
            'method' => 'PUT',
            'facility' => $facility,
        ])
    </div>
@endsection
