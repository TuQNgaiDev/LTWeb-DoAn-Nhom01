<?php
/** Tạo và dùng lại một kết nối PDO từ cấu hình .env. */
declare(strict_types=1);

function db(): PDO
{
    static $ketNoi = null;
    if ($ketNoi instanceof PDO) {
        return $ketNoi;
    }

    $ketNoi = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? '127.0.0.1',
            $_ENV['DB_PORT'] ?? '3306',
            $_ENV['DB_DATABASE'] ?? 'ltweb_nhom01'
        ),
        $_ENV['DB_USERNAME'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $ketNoi;
}
