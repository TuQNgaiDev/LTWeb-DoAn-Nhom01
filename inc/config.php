<?php
/** Khởi tạo môi trường, dotenv, session, header bảo mật và CSRF tập trung. */
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required(['APP_DEBUG', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD']);
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
define('APP_DEBUG', $debug);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set('Asia/Ho_Chi_Minh');
foreach (['logs', 'storage', 'uploads'] as $thuMuc) {
    if (!is_dir(__DIR__ . '/../' . $thuMuc)) { mkdir(__DIR__ . '/../' . $thuMuc, 0775, true); }
}
ini_set('error_log', __DIR__ . '/../logs/php-error.log');
set_exception_handler(function (Throwable $loi) use ($debug): void {
    error_log((string) $loi);
    http_response_code(500);
    $chiTietLoi = $debug ? $loi->getMessage() : '';
    require __DIR__ . '/../500.php';
});
require_once __DIR__ . '/ham.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xac-thuc.php';
require_once __DIR__ . '/bao-mat.php';
$thuMucDuAn = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
$thuMucWeb = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/..'));
$duongDanCookie = str_starts_with($thuMucDuAn, $thuMucWeb) ? substr($thuMucDuAn, strlen($thuMucWeb)) : '';
$duongDanCookie = '/' . trim($duongDanCookie, '/') . '/';
$duongDanCookie = str_replace('//', '/', $duongDanCookie);
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'path' => $duongDanCookie]);
    session_start();
}
if (isset($_SESSION['hoat_dong_cuoi']) && is_int($_SESSION['hoat_dong_cuoi']) && time() - $_SESSION['hoat_dong_cuoi'] > 1800) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['hoat_dong_cuoi'] = time();
require_once __DIR__ . '/csrf.php';

header_remove('X-Powered-By');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self' https://api.open-meteo.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrfHopLe()) {
    ghiNhatKyBaoMat('csrf_sai');
    http_response_code(403);
    if (yeuCauJson()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['loi' => 'CSRF token không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    } else {
        $thongBao403 = 'Trang đã hết hạn. Hãy tải lại trang rồi gửi lại biểu mẫu.';
        require __DIR__ . '/../403.php';
    }
    exit;
}
