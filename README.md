# ĐỀ TÀI 3 – Website Quảng bá Sản phẩm (WordPress)

> Môn: Triển khai và Quản trị Hệ thống Phần mềm. Bộ mã nguồn mẫu chạy tại máy cục bộ (Windows + Docker Desktop) phục vụ thực hành, kiểm thử, báo cáo và demo. **Trạng thái triển khai:** Hệ thống đã được triển khai và kiểm thử thực tế trên Windows với Docker Desktop và WSL2.

- 11 container Docker hoạt động.
- WordPress, phpMyAdmin, Prometheus và Grafana trả về HTTP 200.
- Prometheus có 4/4 Targets UP.
- Grafana hiển thị đầy đủ 6 biểu đồ giám sát.
- Loki và Promtail thu thập log Nginx thành công.
- Đã kiểm thử 3 truy vấn LogQL.
- Đã áp dụng và kiểm tra các biện pháp Hardening.
- Đã push đủ 3 commit lên GitHub.
- Đã lưu 12 ảnh minh chứng tại `docs/screenshots/`.: người thực hiện phải tự chạy và chụp màn hình chứng minh.

## 1. Các thành phần và cổng

| Thành phần | Vai trò | Địa chỉ trên máy |
|---|---|---|
| WordPress + theme Product Showcase K23 | Website quảng bá sản phẩm và CRUD admin | http://localhost:8080 |
| MySQL 8.4 | CSDL tên `showcase` | Không xuất cổng ra host |
| phpMyAdmin | Quản trị MySQL | http://localhost:8081 |
| Nginx unprivileged | Reverse proxy, security headers | http://localhost:8080 |
| Prometheus | Thu thập metrics | http://localhost:9090 |
| Grafana | Dashboard giám sát và Explore LogQL | http://localhost:3000 |
| Nginx Exporter | Metrics web server | Chỉ nội bộ Docker |
| MySQL Exporter | Metrics database | Chỉ nội bộ Docker |
| cAdvisor | Metrics container | Chỉ nội bộ Docker |
| Loki + Promtail | Thu thập, truy vấn Nginx access/error log | Chỉ nội bộ Docker |

**Lưu ý Promtail:** bị nhà cung cấp ngừng hỗ trợ (EOL) từ 02/03/2026. Bộ thực hành vẫn sử dụng Promtail để đúng yêu cầu môn học. Môi trường sản xuất nên thay bằng Grafana Alloy và có HTTPS thực.

## 2. Chuẩn bị

- Windows 10/11, Docker Desktop + WSL2, Docker Engine Linux containers, Git, VS Code và PowerShell.
- Docker Desktop phải báo **Engine running**. Khuyến nghị tối thiểu 8 GB RAM máy; cấp Docker từ 4 GB RAM (nếu được), dành ít nhất 10 GB dung lượng trống vì nhiều image.
- Không cần cài MySQL/PHP/WordPress vào Windows. Tất cả dịch vụ chạy trong Docker Compose.
- Mở PowerShell tại thư mục chứa `compose.yaml` và chạy các lệnh bên dưới.

## 3. BƯỚC 1 – Tạo file mật khẩu và kiểm tra môi trường

```powershell
# Trong thư mục đã giải nén:
docker --version
docker compose version
git --version
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\setup.ps1
# Kiểm tra file .env đã có (KHÔNG chụp/đẩy nội dung mật khẩu lên GitHub)
Test-Path .env
```

**Không commit `.env`**. File `.gitignore` đã chặn `.env`, database dump, log. `setup.ps1` chỉ tạo `.env` nếu chưa tồn tại, với 4 mật khẩu hex mạnh.

## 4. BƯỚC 2 và 3 – WordPress + MySQL + phpMyAdmin + Nginx (Commit 1)

```powershell
# Xem trước cấu hình, phát hiện lỗi biến môi trường:
docker compose -f compose.yaml config --quiet
# Tải image và dựng 4 container nền tảng:
docker compose -f compose.yaml up -d --build
# Xác nhận trạng thái:
docker compose -f compose.yaml ps
```

Truy cập **http://localhost:8080**, hoàn tất wizard WordPress: ngôn ngữ Tiếng Việt → Tên website `NOVASTORE` → tài khoản admin mạnh → email liên hệ của bản thân → Install WordPress. Sau đó mở **http://localhost:8080/wp-admin** → **Giao diện (Appearance) → Themes → Product Showcase K23 → Kích hoạt (Activate)**. Chủ đề tự sinh 6 sản phẩm minh họa khi kích hoạt lần đầu.

Trong admin chọn **Sản phẩm → Thêm mới** để thêm/sửa/xóa sản phẩm. Có thể thêm ảnh đại diện, giá tham khảo, mô tả. Để tránh lỗi 404 khi mở trang chi tiết: **Cài đặt → Đường dẫn tĩnh (Permalinks) → Lưu thay đổi (Save Changes)**. Trang chủ `http://localhost:8080` hiển thị dữ liệu lấy từ WordPress/MySQL.

Truy cập **http://localhost:8081** để vào phpMyAdmin. Username: `showcase_user`; mật khẩu: lấy giá trị `MYSQL_PASSWORD` trong `.env` **trên máy mình**, không chụp ảnh mật khẩu. Kiểm tra database `showcase`, bảng `wp_posts` và dữ liệu sản phẩm.

Kiểm tra reverse proxy và HTTP security headers:

```powershell
curl.exe -I http://localhost:8080
# Phải thấy các header X-Content-Type-Options, X-Frame-Options, Referrer-Policy.
```

Dùng GitHub repository **mới và public/private theo yêu cầu môn**. Tài khoản GitHub đặt theo mã số SV nếu giảng viên yêu cầu. Tại thư mục dự án:

```powershell
git init
git branch -M main
git add .gitignore .env.example README.md compose.yaml scripts/setup.ps1 nginx mysql wordpress
git diff --cached --stat
git commit -m "commit 1: wordpress mysql phpmyadmin nginx reverse proxy"
```

> Nếu trước đó đã `git add .`, chạy `git restore --staged .` trước các lệnh add theo giai đoạn. Kiểm tra `git status` bảo đảm **.env không được theo dõi**.

## 5. BƯỚC 4 – Giám sát Prometheus + Grafana (Commit 2)

```powershell
docker compose -f compose.yaml -f compose.monitoring.yaml config --quiet
docker compose -f compose.yaml -f compose.monitoring.yaml up -d --build
docker compose -f compose.yaml -f compose.monitoring.yaml ps
```

Truy cập **http://localhost:9090/targets**. Sau một lúc phải thấy `prometheus`, `nginx`, `mysql` ở trạng thái `UP`; `cadvisor` tùy mức hỗ trợ Docker Desktop/WSL2 (xem ghi chú lỗi bên dưới). Mở **http://localhost:3000**; user Grafana `admin`, password là `GF_SECURITY_ADMIN_PASSWORD` trong `.env` của máy bạn. Vào **Dashboards → Do an 3 → Đề tài 3 | Website, DB và container**. Refresh trang WordPress nhiều lần để có biểu đồ yêu cầu web.

Lệnh kiểm tra bằng PromQL tại Prometheus:

```promql
up
rate(nginx_http_requests_total[5m])
mysql_global_status_threads_connected
container_memory_working_set_bytes
```

Nếu `up{job="mysql"}` = 0, xem log MySQL exporter và kiểm tra tài khoản `metrics_user` đã được tạo ở lần MySQL khởi tạo đầu tiên.

```powershell
git add compose.monitoring.yaml monitoring grafana
git commit -m "commit 2: prometheus grafana monitoring nginx mysql containers"
```

## 6. BƯỚC 5 – Loki + Promtail + LogQL (Commit 3)

```powershell
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml config --quiet
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml up -d --build
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml ps
```

Tạo log bằng cách tải lại trang chủ 5–10 lần và mở thêm một URL không tồn tại (`http://localhost:8080/khong-ton-tai-123`) để xuất hiện log truy cập. Mở **Grafana → Explore** → chọn nguồn dữ liệu **Loki**, chọn **Last 15 minutes**, rồi chạy 3 truy vấn LogQL riêng biệt:

```logql
{job="nginx-access"}
{job="nginx-access"} |= "GET"
sum(count_over_time({job="nginx-access"}[5m]))
```

Có thể thử thêm `{job="nginx-access"} |= " 404 "` hoặc `{job="nginx-error"}`. Log lỗi chỉ có khi Nginx ghi lỗi, nên query access là minh chứng chắc chắn hơn sau khi website có request.

```powershell
git add compose.logging.yaml logging
git commit -m "commit 3: loki promtail centralized logging logql"
```

## 7. BƯỚC 6 – Hardening và xác nhận

Đã tích hợp các biện pháp (ghi rõ giới hạn):

1. **Non-root**: Nginx dùng `nginxinc/nginx-unprivileged` (UID 101); Grafana UID 472, các exporter dùng user của image. **WordPress, phpMyAdmin và MySQL giữ cơ chế user mặc định của image**; không tuyên bố chúng hoàn toàn non-root.
2. **Network isolation**: `db_net` / `app_net` / `obs_net` là Docker internal networks; MySQL và WordPress không mở cổng trực tiếp ra máy chủ.
3. **Mật khẩu ngẫu nhiên mạnh**: sinh riêng cho root MySQL, app, exporter và Grafana; `.env` nằm ngoài Git.
4. **Hạn chế quyền DB**: WordPress dùng `showcase_user` chỉ trên DB `showcase`; exporter sử dụng `metrics_user` riêng với quyền cần thiết cho metrics.
5. **Nginx security headers**: `nosniff`, `SAMEORIGIN`, referrer-policy và permissions-policy.
6. **Hạn chế quyền container**: Nginx `cap_drop: ALL`, `read_only: true`, `no-new-privileges`; file theme mount `:ro`; phpMyAdmin và dashboard chỉ bind `127.0.0.1`.

Đề bài chấp nhận HTTPS **hoặc** security headers, vì thế bản localhost dùng headers chứ chưa có HTTPS. Việc sử dụng `no-new-privileges` trên MySQL và WordPress có thể phụ thuộc quyền mà entrypoint cần lúc khởi động; nếu gặp lỗi permission, xem phần xử lý sự cố và bảo toàn cách chạy được kiểm chứng.

```powershell
# Kiểm tra root process và mạng; không chia sẻ thông tin bí mật trong screenshot:
docker inspect de-tai-3-wordpress-nginx-1 --format '{{.Config.User}}'
docker network ls
curl.exe -I http://localhost:8080
```

## 8. BƯỚC 7 – GitHub

Sau 3 commit:

```powershell
git log --oneline -3
git status
git remote add origin https://github.com/MSSV/TenRepository.git
# Thay MSSV, TenRepository bằng tài khoản và repository thực tế:
git push -u origin main
```

Nếu repo đã có `origin`, dùng `git remote -v` để kiểm tra, không chạy `git remote add` lần nữa. Commit bổ sung sửa lỗi/README vẫn được, yêu cầu là *đủ ít nhất 3 commit có ý nghĩa*, không chỉ đúng 3 commit.

## 9. BƯỚC 8 – Báo cáo và demo

Xem `docs/BAO_CAO_KHUNG.md` và `docs/CHECKLIST_DEMO.md`. Báo cáo chính thức cần **tối thiểu 10 trang** và ảnh chứng minh **chụp từ máy đã chạy thành công**. Không sử dụng ảnh giả hoặc tuyên bố đã kiểm thử nếu chưa chạy.

## 10. Các lệnh quản lý

```powershell
# Xem mọi dịch vụ, gồm giám sát và log:
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml ps
# Xem log của 1 service:
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml logs --tail=100 nginx
# Dừng tất cả, bảo toàn dữ liệu:
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml down
# Chạy lại:
docker compose -f compose.yaml -f compose.monitoring.yaml -f compose.logging.yaml up -d --build
```

**KHÔNG chạy `down -v` nếu muốn giữ dữ liệu WordPress/MySQL/Grafana**, vì `-v` xóa named volumes.

## 11. Xử lý sự cố

| Hiện tượng | Kiểm tra / sửa |
|---|---|
| `docker` not recognized | Cài Docker Desktop, khởi động lại PowerShell và bật engine Linux container |
| `port is already allocated` | Kiểm tra app đang chiếm các cổng 8080/8081/9090/3000 hoặc sửa toàn bộ port tương ứng và `SITE_URL` |
| Trang chưa vào được khi mới chạy | Chờ MySQL healthy, xem `docker compose -f compose.yaml logs mysql wordpress nginx` |
| WordPress chuyển hướng sai cổng | Kiểm tra `SITE_URL=http://localhost:8080` trong `.env`, file compose và khởi động lại WordPress |
| Website trắng hoặc thiếu style | Kiểm tra theme đã `Activate`, refresh cache trang |
| Product detail 404 | WordPress admin → Settings → Permalinks → Save Changes |
| phpMyAdmin login thất bại | Dùng `showcase_user` và `MYSQL_PASSWORD`, không dùng `GF_SECURITY_ADMIN_PASSWORD` |
| Grafana login thất bại | Username `admin`, password `GF_SECURITY_ADMIN_PASSWORD` trong `.env`; mật khẩu thay đổi trong `.env` không tự reset tài khoản đã lưu trong volume |
| MySQL metrics `DOWN` | `docker compose ... logs mysql_exporter`, đối chiếu `MYSQL_EXPORTER_PASSWORD`, `01-exporter.sh` chỉ chạy khi DB khởi tạo lần đầu |
| cAdvisor `DOWN` hoặc metrics thiếu | Kiểm tra `docker compose ... logs cadvisor`; Docker Desktop/WSL2 giới hạn host mounts và cgroup, cần thử trên Linux VM nếu muốn số liệu container đầy đủ |
| Loki không có log | Chạy nhiều request tới website, xem `docker compose ... logs promtail`, trong Grafana Explore chọn Loki và Last 15 minutes |
| `permission denied` MySQL/WordPress | Kiểm tra `docker compose ... logs mysql wordpress`; thử bỏ `security_opt` của service gặp lỗi, ghi rõ đánh đổi bảo mật trong báo cáo |


### 11.1. Khắc phục lỗi MySQL Exporter (mysql_up = 0)

**Hiện tượng:** Grafana không hiển thị dữ liệu MySQL. Prometheus trả về `mysql_up = 0`.

**Lỗi thực tế:**
`Access denied for user 'metrics_user'`

**Nguyên nhân có thể xảy ra:**
- Tài khoản `metrics_user` chưa được khởi tạo.
- Mật khẩu MySQL Exporter không khớp.
- Script `mysql/initdb/01-exporter.sh` chỉ chạy khi MySQL khởi tạo dữ liệu lần đầu.

**Cách kiểm tra:**

```powershell
docker compose -f compose.yaml -f compose.monitoring.yaml logs --tail=50 mysql_exporter
```

**Hướng khắc phục:**
1. Kiểm tra biến `MYSQL_EXPORTER_PASSWORD` trong `.env`.
2. Kiểm tra tài khoản `metrics_user` trong MySQL.
3. Đồng bộ mật khẩu tài khoản với cấu hình Exporter.
4. Kiểm tra các quyền `PROCESS`, `REPLICATION CLIENT`, `SELECT`.
5. Chạy lại truy vấn `mysql_up` trong Prometheus.

**Kết quả thực tế:** Đã khắc phục thành công, `mysql_up = 1`. Grafana hiển thị dữ liệu truy vấn và kết nối MySQL.

### 11.2. Khắc phục lỗi cAdvisor không nhận diện container

**Hiện tượng:** Hai biểu đồ CPU và RAM trên Grafana hiển thị `No data`.

**Nguyên nhân đã kiểm tra:**
- Docker Desktop sử dụng `containerd snapshotter`.
- cAdvisor v0.53.0 không nhận diện được từng container trong môi trường thực hành.

**Giải pháp đã áp dụng:**
1. Nâng cấp cAdvisor lên `ghcr.io/google/cadvisor:v0.60.6`.
2. Giữ nguyên dữ liệu Docker volumes.
3. Tạo lại riêng container cAdvisor.
4. Kiểm tra metrics trên Prometheus.

**Lệnh cập nhật:**

```powershell
docker compose -f compose.yaml -f compose.monitoring.yaml pull cadvisor
docker compose -f compose.yaml -f compose.monitoring.yaml up -d --no-deps --force-recreate cadvisor
```

**Truy vấn kiểm tra RAM:**

```promql
container_memory_working_set_bytes{name!=""}
```

**Truy vấn kiểm tra CPU:**

```promql
rate(container_cpu_usage_seconds_total{name!=""}[5m])
```

**Kết quả thực tế:** Prometheus nhận diện được từng container WordPress, MySQL, Nginx, Grafana và các dịch vụ khác. Hai biểu đồ CPU/RAM trong Grafana đã hiển thị dữ liệu.


## 12. Sơ đồ luồng

```text
Browser (localhost:8080)
       |
   Nginx [non-root + headers + access/error logs] <---- nginx exporter
       |
   WordPress (theme Product Showcase K23)
       |
      MySQL <---- MySQL exporter
       |
   phpMyAdmin (localhost:8081)

Prometheus <-- nginx exporter / mysql exporter / cAdvisor
     |
  Grafana (localhost:3000)
     |
     +----> Loki <---- Promtail <---- Nginx log files
```

Không sử dụng nội dung tài liệu này để công bố hệ thống trực tiếp lên Internet mà không bổ sung HTTPS, kiểm soát truy cập admin, bảo mật WordPress và chiến lược sao lưu/khôi phục.
