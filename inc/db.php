<?php
/** Tạo và dùng lại một kết nối PDO tới CSDL ltweb_nhom01.
 * Cấu hình mặc định khớp XAMPP: root, mật khẩu rỗng, MySQL cổng 3306.
 */
declare(strict_types=1);

function db(): PDO
{
    static $ketNoi = null;
    if ($ketNoi instanceof PDO) {
        return $ketNoi;
    }

    $ketNoi = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=ltweb_nhom01;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $ketNoi;
}
