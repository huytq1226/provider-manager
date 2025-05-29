# Hệ thống đánh giá nhà cung cấp

Hệ thống đánh giá nhà cung cấp cho phép người dùng đánh giá nhà cung cấp dựa trên các tiêu chí khác nhau như chất lượng dịch vụ, thời gian giao hàng, thái độ phục vụ, v.v.

## Cài đặt

Để cài đặt hệ thống đánh giá, hãy làm theo các bước sau:

1. Đảm bảo bạn đã đăng nhập với tài khoản admin
2. Truy cập trang `setup_ratings.php` để chạy script SQL tạo các bảng cần thiết
3. Sau khi cài đặt thành công, hệ thống đánh giá sẽ hiển thị trong trang chi tiết nhà cung cấp

## Cấu trúc cơ sở dữ liệu

Hệ thống đánh giá sử dụng 3 bảng:

1. **RatingCriteria**: Lưu trữ các tiêu chí đánh giá

   - id: ID của tiêu chí
   - name: Tên tiêu chí
   - description: Mô tả chi tiết
   - weight: Trọng số (để tính điểm tổng hợp)
   - status: Trạng thái (Active/Inactive)
   - createDate: Ngày tạo

2. **ProviderRatings**: Lưu trữ các đánh giá

   - id: ID của đánh giá
   - providerId: ID của nhà cung cấp
   - userId: ID của người dùng (nếu có)
   - contractId: ID của hợp đồng liên quan (nếu có)
   - comment: Nhận xét
   - overall: Điểm tổng hợp
   - createDate: Ngày tạo

3. **RatingScores**: Lưu trữ điểm số cho từng tiêu chí
   - ratingId: ID của đánh giá
   - criteriaId: ID của tiêu chí
   - score: Điểm số (1-5)

## Tính năng

- Đánh giá nhà cung cấp theo nhiều tiêu chí khác nhau
- Hiển thị điểm trung bình cho từng tiêu chí
- Hiển thị danh sách đánh giá gần đây
- Tự động cập nhật điểm uy tín của nhà cung cấp dựa trên đánh giá
- Liên kết đánh giá với hợp đồng cụ thể (tùy chọn)

## Tùy chỉnh

Để thêm hoặc chỉnh sửa tiêu chí đánh giá, bạn có thể chạy các lệnh SQL sau:

```sql
-- Thêm tiêu chí mới
INSERT INTO RatingCriteria (name, description, weight)
VALUES ('Tên tiêu chí', 'Mô tả tiêu chí', 0.80);

-- Cập nhật tiêu chí
UPDATE RatingCriteria
SET weight = 0.90, description = 'Mô tả mới'
WHERE id = 1;

-- Vô hiệu hóa tiêu chí
UPDATE RatingCriteria
SET status = 'Inactive'
WHERE id = 1;
```
