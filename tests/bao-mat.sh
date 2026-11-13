#!/usr/bin/env bash
set -u

GOC="${1:-http://localhost:8000}"
HTACCESS_GOC="${HTACCESS_GOC:-$GOC}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
COOKIE="$TMP/cookie.txt"
PASS=0
FAIL=0

kiem_tra() {
  local ten="$1" mong="$2" thuc="$3"
  if [[ "$thuc" == "$mong" ]]; then
    printf 'PASS %-48s %s\n' "$ten" "$thuc"
    PASS=$((PASS + 1))
  else
    printf 'FAIL %-48s mong %s, nhan %s\n' "$ten" "$mong" "$thuc"
    FAIL=$((FAIL + 1))
  fi
}

lay_token() {
  curl -sS -c "$COOKIE" "$GOC/dang-nhap.php" -o "$TMP/login.html"
  sed -n 's/.*name="csrf-token" content="\([^"]*\)".*/\1/p' "$TMP/login.html" | head -n 1
}

TOKEN="$(lay_token)"

kiem_tra '01 POST form thiếu CSRF' 403 "$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE" -X POST "$GOC/dang-ky.php")"
kiem_tra '02 POST form CSRF sai' 403 "$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE" -d 'csrf=sai' "$GOC/dang-ky.php")"
kiem_tra '03 API thiếu CSRF trả 403' 403 "$(curl -sS -o "$TMP/api-thieu.json" -w '%{http_code}' -b "$COOKIE" -H 'Content-Type: application/json' -d '{"id":1,"sl":1}' "$GOC/api/gio-hang.php")"
kiem_tra '04 API CSRF sai trả 403' 403 "$(curl -sS -o "$TMP/api-sai.json" -w '%{http_code}' -b "$COOKIE" -H 'Content-Type: application/json' -H 'X-CSRF-Token: sai' -d '{"id":1,"sl":1}' "$GOC/api/gio-hang.php")"
grep -q 'CSRF token' "$TMP/api-thieu.json" && kiem_tra '05 lỗi CSRF API là JSON' 1 1 || kiem_tra '05 lỗi CSRF API là JSON' 1 0
kiem_tra '06 đăng xuất bằng GET bị từ chối' 405 "$(curl -sS -o /dev/null -w '%{http_code}' "$GOC/dang-xuat.php")"
kiem_tra '07 cửa sau quản trị đã xóa' 302 "$(curl -sS -o /dev/null -w '%{http_code}' "$GOC/quan-tri.php?dang_nhap_admin=1")"
kiem_tra '08 cửa sau khách đã xóa' 302 "$(curl -sS -o /dev/null -w '%{http_code}' "$GOC/don-hang.php?dang_nhap_khach=1")"
kiem_tra '09 chặn tệp .env' 404 "$(curl -sS -o /dev/null -w '%{http_code}' "$HTACCESS_GOC/.env")"
kiem_tra '10 chặn database/schema.sql' 403 "$(curl -sS -o /dev/null -w '%{http_code}' "$HTACCESS_GOC/database/schema.sql")"
kiem_tra '11 chặn inc/config.php' 404 "$(curl -sS -o /dev/null -w '%{http_code}' "$HTACCESS_GOC/inc/config.php")"
kiem_tra '12 POST API token đúng' 200 "$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE" -H 'Content-Type: application/json' -H "X-CSRF-Token: $TOKEN" -d '{"id":1,"sl":1}' "$GOC/api/gio-hang.php")"

# Hai ca riêng của UEDBookHub: không thay đổi trạng thái qua GET và IDOR đơn hàng.
kiem_tra '13 GET không được xóa giáo trình' 405 "$(curl -sS -o /dev/null -w '%{http_code}' "$GOC/xoa-giao-trinh.php?id=1")"
kiem_tra '14 khách chưa đăng nhập không xem đơn' 302 "$(curl -sS -o /dev/null -w '%{http_code}' "$GOC/don-hang.php?id=1")"

# Brute force dùng email không thuộc tài khoản mẫu để không khóa tài khoản thử.
EMAIL="bruteforce-test@invalid.example"
for i in 1 2 3 4 5; do
  TOKEN="$(lay_token)"
  curl -sS -o /dev/null -b "$COOKIE" -c "$COOKIE" -d "csrf=$TOKEN&email=$EMAIL&mat_khau=sai-mat-khau" "$GOC/dang-nhap.php"
done
TOKEN="$(lay_token)"
kiem_tra '15 lần thứ sáu bị giới hạn' 429 "$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE" -d "csrf=$TOKEN&email=$EMAIL&mat_khau=sai-mat-khau" "$GOC/dang-nhap.php")"

printf '\nTong: %d PASS, %d FAIL\n' "$PASS" "$FAIL"
[[ "$FAIL" -eq 0 ]]
