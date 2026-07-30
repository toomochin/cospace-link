@extends('layouts.app')

@section('content')
    <div class="user-container">
        <h2>施設登録</h2>
        @include('owner.facilities.form', [
            'action' => route('owner.facilities.store'),
            'method' => 'POST',
            'facility' => null,
        ])
    </div>
@endsection
