<?php
/** Tiện ích REST API: trả JSON thống nhất và thay handler HTML bằng JSON.
 * Mỗi endpoint gọi dangKyLoiApi() trước khi truy vấn CSDL.
 */
declare(strict_types=1);

function traJson(array $duLieu, int $ma = 200): never
{
    http_response_code($ma);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function dangKyLoiApi(): void
{
    set_exception_handler(function (Throwable $loi): void {
        error_log((string) $loi);
        traJson(['loi' => MOI_TRUONG === 'dev' ? $loi->getMessage() : 'Lỗi máy chủ'], 500);
    });
}
