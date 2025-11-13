<?php
$api_contract = [
    [
        "endpoint" => "/api/v1/auth/login",
        "method" => "POST",
        "description" => "Autentikasi user menggunakan email dan password",
        "request_body" => [
            "email" => "string",
            "password" => "string"
        ],
        "response" => [
            "status" => "success",
            "token" => "string"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users",
        "method" => "GET",
        "description" => "Menampilkan daftar semua user",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "data" => "array of users"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/health",
        "method" => "GET",
        "description" => "Menampilkan status kesehatan sistem API",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "message" => "API is running"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "GET",
        "description" => "Menampilkan detail user berdasarkan ID",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "data" => "user object"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users",
        "method" => "POST",
        "description" => "Menambahkan user baru ke dalam sistem",
        "request_body" => [
            "username" => "string",
            "email" => "string",
            "password" => "string",
            "role" => "string (optional, default: user)"
        ],
        "response" => [
            "status" => "success",
            "message" => "User created successfully",
            "data" => "user object"
        ],
        "status_code" => 201,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "PUT",
        "description" => "Memperbarui data user berdasarkan ID",
        "request_body" => [
            "username" => "string",
            "email" => "string",
            "password" => "string (optional)",
            "role" => "string (optional)"
        ],
        "response" => [
            "status" => "success",
            "message" => "User updated successfully",
            "data" => "updated user object"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "DELETE",
        "description" => "Menghapus user berdasarkan ID",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "message" => "User deleted successfully"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/upload",
        "method" => "POST",
        "description" => "Mengunggah file ke server",
        "request_body" => [
            "file" => "binary"
        ],
        "response" => [
            "status" => "success",
            "message" => "File uploaded successfully",
            "file_url" => "string"
        ],
        "status_code" => 201,
        "version" => "v1"
    ]
];

header('Content-Type: application/json');
echo json_encode($api_contract, JSON_PRETTY_PRINT);
?>