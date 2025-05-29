# Hệ thống Thông báo (Notification System)

Hệ thống thông báo giúp cảnh báo người dùng về các sự kiện quan trọng như hóa đơn sắp đến hạn thanh toán, hợp đồng sắp hết hạn và các thông báo khác trong hệ thống.

## Tính năng chính

- **Thông báo thời gian thực**: Hiển thị thông báo tức thì cho các sự kiện quan trọng.
- **Phân loại mức độ ưu tiên**: Thông báo được phân loại thành khẩn cấp (đỏ), cảnh báo (vàng) và thông tin (xanh).
- **Tự động sinh thông báo**: Hệ thống tự động kiểm tra và tạo thông báo cho hóa đơn sắp đến hạn và hợp đồng sắp hết hạn.
- **Quản lý thông báo**: Đánh dấu đã đọc, xóa thông báo, và lọc thông báo theo các tiêu chí khác nhau.
- **Thông báo dạng Toast**: Hiển thị thông báo quan trọng dưới dạng toast khi người dùng mới vào trang web.

## Cài đặt

Để cài đặt hệ thống thông báo, hãy thực hiện các bước sau:

1. Đăng nhập với tài khoản admin
2. Truy cập trang `setup_notifications.php` để chạy script SQL tạo bảng và các stored procedure cần thiết
3. Sau khi cài đặt thành công, hệ thống thông báo sẽ tự động hoạt động

## Cấu trúc cơ sở dữ liệu

Hệ thống thông báo sử dụng bảng `Notifications` với cấu trúc như sau:

```sql
CREATE TABLE IF NOT EXISTS Notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL, -- 'bill_due', 'contract_expiring', etc.
    severity VARCHAR(20) NOT NULL, -- 'urgent', 'normal', 'info'
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    relatedId INT, -- ID of the related entity (bill ID, contract ID, etc.)
    relatedTable VARCHAR(50), -- 'Bills', 'Contracts', etc.
    isRead BOOLEAN DEFAULT FALSE,
    createdDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    readDate DATETIME NULL,
    userId INT NULL, -- If notification is for a specific user
    expiryDate DATETIME NULL -- When the notification should expire/be automatically dismissed
);
```

## Stored Procedures

Hệ thống sử dụng hai stored procedure chính để tự động tạo thông báo:

1. **GenerateBillDueNotifications**: Tạo thông báo cho các hóa đơn sắp đến hạn thanh toán trong 7 ngày tới.
2. **GenerateContractExpiringNotifications**: Tạo thông báo cho các hợp đồng sắp hết hạn trong 30 ngày tới.

Các stored procedure này được tự động chạy hàng ngày thông qua một sự kiện MySQL.

## Mức độ Thông báo

Hệ thống phân loại thông báo theo 3 mức độ:

- **Khẩn cấp (urgent)**: Màu đỏ - Hóa đơn sắp đến hạn trong 2 ngày hoặc hợp đồng sắp hết hạn trong 7 ngày.
- **Cảnh báo (warning)**: Màu vàng - Hóa đơn sắp đến hạn trong 5 ngày hoặc hợp đồng sắp hết hạn trong 15 ngày.
- **Thông tin (info)**: Màu xanh - Các thông báo còn lại.

## Giao diện người dùng

1. **Dropdown Thông báo**: Hiển thị trên thanh điều hướng trên cùng của trang web.
2. **Trang Thông báo**: Hiển thị tất cả các thông báo với các bộ lọc và tùy chọn quản lý.
3. **Toast Notifications**: Hiển thị thông báo khẩn cấp dưới dạng toast khi người dùng vào trang web.

## Tùy chỉnh

Để thay đổi các thiết lập của hệ thống thông báo, bạn có thể chỉnh sửa các giá trị trong các stored procedure. Ví dụ:

- Thay đổi khoảng thời gian cảnh báo hóa đơn từ 7 ngày thành 10 ngày
- Thay đổi mức độ cảnh báo cho các loại thông báo khác nhau
- Thêm các loại thông báo mới

## Các tệp liên quan

- `includes/notifications.php`: Chứa các hàm xử lý thông báo
- `process_notification.php`: Xử lý các hành động liên quan đến thông báo (đánh dấu đã đọc, xóa, ...)
- `notifications.php`: Trang hiển thị và quản lý thông báo
- `setup_notifications.php`: Script thiết lập hệ thống thông báo
- `add_notifications.sql`: SQL script tạo bảng và stored procedure

## Mở rộng

Hệ thống thông báo có thể được mở rộng để hỗ trợ:

- Thông báo qua email cho các sự kiện quan trọng
- Thông báo cá nhân hóa cho từng người dùng
- Thông báo cho các loại sự kiện khác như thay đổi trạng thái hợp đồng, thay đổi giá, ...

## Xử lý sự cố

Nếu hệ thống thông báo không hoạt động:

1. Kiểm tra xem Event Scheduler của MySQL có được bật không
2. Kiểm tra xem các stored procedure có tồn tại và hoạt động đúng không
3. Kiểm tra logs của cơ sở dữ liệu để tìm lỗi
4. Thử chạy lại `setup_notifications.php` để tái thiết lập hệ thống
