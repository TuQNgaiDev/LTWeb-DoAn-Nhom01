<?php
declare(strict_types=1);

function ghiNhatKyBaoMat(string $suKien, array $duLieu = []): void
{
    $choPhep = ['csrf_sai', 'cam_truy_cap', 'dang_nhap_sai'];
    if (!in_array($suKien, $choPhep, true)) {
        return;
    }
    $banGhi = [
        'thoi_gian' => date(DATE_ATOM),
        'su_kien' => $suKien,
        'ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
        'duong_dan' => substr((string) ($_SERVER['REQUEST_URI'] ?? ''), 0, 500),
    ];
    foreach ($duLieu as $khoa => $giaTri) {
        if (in_array($khoa, ['mat_khau', 'password', 'csrf', 'cookie'], true)) {
            continue;
        }
        $banGhi[$khoa] = is_scalar($giaTri) ? substr((string) $giaTri, 0, 254) : '[du_lieu]';
    }
    error_log(json_encode($banGhi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, __DIR__ . '/../logs/bao-mat.log');
}

function yeuCauJson(): bool
{
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    return str_contains($contentType, 'application/json') || str_contains($accept, 'application/json') || str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/api/');
}
