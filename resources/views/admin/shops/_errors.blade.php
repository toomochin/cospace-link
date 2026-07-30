@if (session('status'))
    <div class="alert-success" role="status">{{ session('status') }}</div>
@endif

@if (session('warning'))
    <div class="alert-error" role="alert">{{ session('warning') }}</div>
@endif

@if ($errors->any())
    <div class="alert-error" role="alert">
        <strong>入力内容を確認してください。</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
