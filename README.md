# CustomerHub — Cloud-Based Customer Contact Management SaaS on AWS

> Đồ án cuối kỳ môn Điện toán đám mây — UEH University  
> Triển khai tại: **[group-14.compsci.studio](http://group-14.compsci.studio)**

---

## Giới thiệu

**CustomerHub** là một ứng dụng web quản lý thông tin khách hàng được xây dựng theo mô hình **Software-as-a-Service (SaaS)** và triển khai hoàn toàn trên nền tảng **Amazon Web Services (AWS)**.

Ứng dụng cho phép người dùng:
- Quản lý danh sách khách hàng (thêm, sửa, xóa)
- Gửi email đơn lẻ hoặc hàng loạt đến khách hàng qua **Amazon SES**
- Theo dõi lịch sử hoạt động
- Quản lý tài khoản cá nhân

---

## Kiến trúc hệ thống

```
User / Browser
      │
      ▼ port 80/443
┌─────────────────────────────────────────┐
│              AWS Cloud (VPC)            │
│                                         │
│  ┌──────────────────┐                   │
│  │   Public Subnet  │                   │
│  │  ┌────────────┐  │                   │
│  │  │ Amazon EC2 │──────────────────► Amazon SES
│  │  │(Apache+PHP)│  │                   │
│  │  └─────┬──────┘  │                   │
│  └────────┼─────────┘                   │
│           │ MySQL port 3306             │
│  ┌────────┼─────────┐                   │
│  │ Private Subnet   │                   │
│  │  ┌─────▼──────┐  │                   │
│  │  │ Amazon RDS │  │                   │
│  │  │  (MySQL)   │  │                   │
│  │  └────────────┘  │                   │
│  └──────────────────┘                   │
│                                         │
│  IAM · CloudWatch · Security Groups     │
└─────────────────────────────────────────┘
```

---

## Tech Stack

| Thành phần | Công nghệ |
|---|---|
| Backend | PHP 8 |
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Database | MySQL — Amazon RDS |
| Web Server | Apache 2 — Amazon EC2 (Ubuntu) |
| Email Service | Amazon SES |
| Dependency Manager | Composer |
| Cloud Platform | Amazon Web Services (AWS) |

---

## Cấu trúc thư mục

```
/var/www/html/
├── index.php               # Redirect về login/customers
├── login.php               # Đăng nhập
├── register.php            # Đăng ký tài khoản
├── logout.php              # Đăng xuất
├── customers.php           # Quản lý khách hàng (CRUD)
├── history.php             # Lịch sử hoạt động
├── settings.php            # Cài đặt tài khoản
├── send_email.php          # Gửi email đơn lẻ
├── send_bulk_email.php     # Gửi email hàng loạt
├── auth.php                # Xác thực session
├── db.php                  # Kết nối RDS + hàm logActivity()
├── style.css               # CSS toàn cục
├── .htaccess               # URL rewriting (bỏ đuôi .php)
└── vendor/
    └── aws/                # AWS SDK for PHP
```

---

## Tính năng

### Quản lý khách hàng
- Thêm, sửa, xóa khách hàng qua giao diện modal (không reload trang)
- Chọn nhiều khách hàng để xóa hoặc gửi email hàng loạt
- Tìm kiếm và lọc danh sách

### Gửi email
- Gửi email đơn lẻ hoặc hàng loạt qua **Amazon SES**
- Tùy chỉnh tiêu đề và nội dung
- Hiển thị kết quả gửi thành công / thất bại

### Xác thực & Bảo mật
- Đăng ký, đăng nhập, đăng xuất
- Mật khẩu mã hóa bằng **bcrypt** (`password_hash`)
- Bảo vệ trang qua **PHP session** (`auth.php`)
- Chống XSS bằng `htmlspecialchars()`

### Lịch sử hoạt động
- Ghi log tự động mọi thao tác: thêm, sửa, xóa, gửi email
- Hiển thị 100 bản ghi gần nhất

---

## Cài đặt & Triển khai

### Yêu cầu
- Amazon EC2 (Ubuntu)
- Amazon RDS (MySQL)
- Amazon SES (email đã verified)
- PHP 8, Apache 2, Composer

### Bước 1 — Cài môi trường trên EC2

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 php php-mysql php-curl php-json php-mbstring -y
sudo a2enmod rewrite
sudo systemctl restart apache2
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Bước 2 — Clone và cài thư viện

```bash
cd /var/www/html
git clone https://github.com/ngmhuy05/AWS-Customer-Management .
composer install
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### Bước 3 — Tạo database

Kết nối vào RDS và chạy:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Bước 4 — Cấu hình kết nối

Cập nhật thông tin trong `db.php`:

```php
$servername = "YOUR_RDS_ENDPOINT";
$username   = "YOUR_DB_USERNAME";
$password   = "YOUR_DB_PASSWORD";
$dbname     = "customerdb";
```

> ⚠️ **Lưu ý bảo mật:** Trong môi trường production, nên dùng biến môi trường hoặc AWS Secrets Manager thay vì hardcode credentials.

---

## AWS Services

| Dịch vụ | Vai trò |
|---|---|
| Amazon EC2 | Máy chủ chạy Apache + PHP |
| Amazon RDS | Cơ sở dữ liệu MySQL |
| Amazon SES | Gửi email đến khách hàng |
| Amazon VPC | Mạng riêng ảo, cô lập tài nguyên |
| Security Group | Kiểm soát truy cập port 80/443 và 3306 |
| AWS IAM | Phân quyền truy cập SES |
| Amazon CloudWatch | Giám sát hệ thống |

---

## License

Dự án được thực hiện cho mục đích học thuật.
