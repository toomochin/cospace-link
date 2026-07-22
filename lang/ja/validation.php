<?php

return [
    'required'  => ':attribute を入力してください。',
    'email'     => '有効なメールアドレス形式で入力してください。',
    'confirmed' => ':attribute（確認用）と一致しません。',
    'min'       => [
        'string' => ':attribute は :min 文字以上で入力してください。',
    ],

    'attributes' => [
        'name'     => 'お名前',
        'email'    => 'メールアドレス',
        'password' => 'パスワード',
    ],
];
