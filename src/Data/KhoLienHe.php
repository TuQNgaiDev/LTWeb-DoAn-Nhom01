<?php
/** Lưu và đọc liên hệ JSON Lines có khóa tệp.
 * Chỉ lớp này truy cập tệp liên hệ, trả bản mới nhất trước.
 */
declare(strict_types=1);
namespace App\Data;

final class KhoLienHe
{
    public function __construct(private string $tep) {}
    public function them(array $lienHe): void
    {
        $dong = json_encode($lienHe, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
        if (file_put_contents($this->tep, $dong, FILE_APPEND | LOCK_EX) === false) { throw new \RuntimeException('Không lưu được liên hệ.'); }
    }
    public function tatCa(): array
    {
        if (!is_file($this->tep)) { return []; }
        $f = fopen($this->tep, 'rb');
        if ($f === false) { throw new \RuntimeException('Không đọc được liên hệ.'); }
        if (!flock($f, LOCK_SH)) { fclose($f); throw new \RuntimeException('Không khóa được tệp liên hệ.'); }
        try {
            $ds = [];
            while (($dong = fgets($f)) !== false) {
                if (trim($dong) === '') { continue; }
                $d = json_decode($dong, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($d)) { throw new \RuntimeException('Dữ liệu liên hệ không hợp lệ.'); }
                $ds[] = $d;
            }
            return array_reverse($ds);
        } finally { flock($f, LOCK_UN); fclose($f); }
    }
}
