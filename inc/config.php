<?php
/** Khởi tạo môi trường, lỗi, Composer và phiên trước mọi output.
 * Nộp bài ở dev; đổi thành prod để kiểm thử trang lỗi 500.
 */
declare(strict_types=1);
const MOI_TRUONG = 'dev';
error_reporting(E_ALL);
ini_set('display_errors', MOI_TRUONG === 'dev' ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set('Asia/Ho_Chi_Minh');
foreach (['logs', 'storage', 'uploads'] as $thuMuc) {
    if (!is_dir(__DIR__ . '/../' . $thuMuc)) { mkdir(__DIR__ . '/../' . $thuMuc, 0775, true); }
}
ini_set('error_log', __DIR__ . '/../logs/php-error.log');
set_exception_handler(function (Throwable $loi): void {
    error_log((string) $loi);
    http_response_code(500);
    $chiTietLoi = MOI_TRUONG === 'dev' ? $loi->getMessage() : '';
    require __DIR__ . '/../500.php';
});
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/ham.php';
$duongDanCookie = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$duongDanCookie = rtrim($duongDanCookie, '/') . '/';
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'path' => $duongDanCookie]);
    session_start();
}
