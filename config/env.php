<?php
return [
    'db' => [
        'dsn'  => 'mysql:host=127.0.0.1;dbname=apiphp;charset=utf8mb4',
        'user' => 'root',
        'pass' => ''
    ],
    'app' => [
        'env' => 'local',
        'debug' => true,
        'base_url' => 'http://localhost/api-php-native-adit/public',
        'jwt_secret' => 'Saya_proplayer_nama_kirito__',
        'allowed_origins' => [
            'http://localhost:3000',
            'http://localhost'
        ]
    ]
];