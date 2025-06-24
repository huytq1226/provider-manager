-- Export from database: provider_management
-- Generated on: 2025-06-24 14:49:02

-- Create database
CREATE DATABASE IF NOT EXISTS `provider_management`;
USE `provider_management`;

-- Drop tables if they exist (reverse dependency order)
DROP TABLE IF EXISTS `ratingscores`;
DROP TABLE IF EXISTS `provideservice`;
DROP TABLE IF EXISTS `providerratings`;
DROP TABLE IF EXISTS `ratingcriteria`;
DROP TABLE IF EXISTS `bills`;
DROP TABLE IF EXISTS `contracts`;
DROP TABLE IF EXISTS `providers`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `notifications`;

-- Create tables in correct dependency order

-- 1. services
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `des` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `createDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table services
INSERT INTO `services` (`id`, `name`, `des`, `status`, `createDate`, `updateDate`) VALUES
('1', 'Dịch vụ viễn thông', 'Cung cấp dịch vụ internet, di động và truyền hình', 'Inactive', '2025-05-14 09:13:51', '2025-05-24 11:06:57'),
('2', 'Quản lý bất động sản', 'Quản lý và vận hành các dự án bất động sản cao cấp', 'Active', '2025-05-14 09:13:51', '2025-05-14 09:13:51'),
('3', 'Phát triển phần mềm', 'Phát triển ứng dụng và giải pháp CNTT', 'Active', '2025-05-14 09:13:51', '2025-05-14 09:13:51'),
('4', 'Sản xuất sữa', 'Sản xuất và phân phối các sản phẩm sữa', 'Active', '2025-05-14 09:13:51', '2025-05-14 09:13:51'),
('5', 'Khai thác dầu khí', 'Khai thác và chế biến dầu khí', 'Active', '2025-05-14 09:13:51', '2025-05-14 09:13:51'),
('6', 'Dịch vụ ngân hàng', 'Cung cấp các dịch vụ tài chính và ngân hàng', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('7', 'Bán lẻ tiêu dùng', 'Kinh doanh các sản phẩm tiêu dùng nhanh', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('8', 'Vận tải hàng không', 'Dịch vụ vận chuyển hành khách và hàng hóa bằng đường hàng không', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('9', 'Sản xuất sữa tươi', 'Sản xuất và phân phối sữa tươi sạch', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('10', 'Sản xuất thép', 'Sản xuất và cung cấp thép xây dựng', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('11', 'Thương mại điện tử', 'Cung cấp nền tảng mua sắm trực tuyến', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('12', 'Dịch vụ cà phê', 'Kinh doanh chuỗi quán cà phê và đồ uống', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('13', 'Viễn thông di động', 'Cung cấp dịch vụ di động và internet', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('14', 'Dịch vụ tài chính', 'Cung cấp các khoản vay và dịch vụ tài chính', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('15', 'Dịch vụ logistics', 'Vận chuyển và giao hàng quốc tế', 'Active', '2025-05-14 09:16:03', '2025-05-14 09:16:03'),
('16', 'Dịch vụ ngân hàng', 'Cung cấp các dịch vụ tài chính và ngân hàng', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('17', 'Bán lẻ tiêu dùng', 'Kinh doanh các sản phẩm tiêu dùng nhanh', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('18', 'Vận tải hàng không', 'Dịch vụ vận chuyển hành khách và hàng hóa bằng đường hàng không', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('19', 'Sản xuất sữa tươi', 'Sản xuất và phân phối sữa tươi sạch', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('20', 'Sản xuất thép', 'Sản xuất và cung cấp thép xây dựng', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('21', 'Thương mại điện tử', 'Cung cấp nền tảng mua sắm trực tuyến', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('22', 'Dịch vụ cà phê', 'Kinh doanh chuỗi quán cà phê và đồ uống', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('23', 'Viễn thông di động', 'Cung cấp dịch vụ di động và internet', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('24', 'Dịch vụ tài chính', 'Cung cấp các khoản vay và dịch vụ tài chính', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('25', 'Dịch vụ logistics', 'Vận chuyển và giao hàng quốc tế', 'Active', '2025-05-14 09:16:27', '2025-05-14 09:16:27'),
('26', 'Sản xuất ô tô', 'Sản xuất và phân phối xe ô tô điện và xăng', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('27', 'Thương mại điện tử', 'Nền tảng mua sắm trực tuyến', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('28', 'Đầu tư tài chính', 'Đầu tư vào các dự án bất động sản và hàng không', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('29', 'Chuỗi cà phê', 'Kinh doanh chuỗi quán cà phê', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('30', 'Phát triển game', 'Phát triển và phân phối game trực tuyến', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('31', 'Du lịch và nghỉ dưỡng', 'Kinh doanh khu du lịch và khách sạn cao cấp', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('32', 'Bán lẻ trực tuyến', 'Cung cấp dịch vụ bán lẻ qua nền tảng trực tuyến', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('33', 'Sản xuất cà phê', 'Sản xuất và phân phối cà phê hòa tan', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('34', 'Trang sức', 'Sản xuất và kinh doanh trang sức vàng, bạc', 'Active', '2025-05-14 09:21:42', '2025-05-14 09:21:42'),
('35', 'Phát triển đô thị', 'Phát triển các khu đô thị xanh', 'Active', '2025-05-14 09:21:42', '2025-05-30 22:01:17'),
('36', 'Đầu tự tài chính 2', 'Đầu tư vào các dự án năng lượng tái tạo', 'Active', '2025-05-27 22:52:31', '2025-05-27 22:52:31'),
('37', 'Sản xuất Thép Xây Dựng Chất Lượng Cao', 'Cung cấp các loại thép cuộn, thép cây, phôi thép đạt tiêu chuẩn quốc tế.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('38', 'Thức Ăn Chăn Nuôi Gia Súc Gia Cầm', 'Sản xuất và cung ứng thức ăn chăn nuôi công nghệ cao.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('39', 'Trứng Gà Sạch An Toàn Sinh Học', 'Cung cấp trứng gà sạch từ trang trại chăn nuôi theo tiêu chuẩn VietGAP.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('40', 'Dịch vụ Giao Hàng Thương Mại Điện Tử', 'Giải pháp giao hàng chuyên biệt cho các sàn TMĐT và shop online.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('41', 'Dịch vụ Kho Bãi và Hoàn Tất Đơn Hàng (Fulfillment)', 'Cung cấp dịch vụ lưu kho, đóng gói và xử lý đơn hàng.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('42', 'Tư vấn Chiến lược Marketing Online', 'Tư vấn và triển khai các chiến dịch marketing trên nền tảng số.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('43', 'Giải pháp An Ninh Mạng Doanh Nghiệp', 'Cung cấp các giải pháp bảo mật toàn diện cho hệ thống CNTT của doanh nghiệp.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('44', 'Dịch vụ Viettel Cloud Server', 'Cung cấp hạ tầng máy chủ ảo đám mây linh hoạt và bảo mật.', 'Active', '2025-05-30 23:20:01', '2025-05-30 23:20:01'),
('45', 'Chuyển Đổi Số Doanh Nghiệp Toàn Diện', 'Tư vấn và triển khai lộ trình chuyển đổi số cho các doanh nghiệp.', 'Inactive', '2025-05-30 23:20:01', '2025-05-31 10:44:49');

-- 2. providers
CREATE TABLE `providers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `taxCode` varchar(15) DEFAULT NULL,
  `vat` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `createDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `website` varchar(255) DEFAULT NULL,
  `reputation` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table providers
INSERT INTO `providers` (`id`, `name`, `taxCode`, `vat`, `status`, `address`, `email`, `phone`, `createDate`, `updateDate`, `website`, `reputation`) VALUES
('1', 'Viettel Group', '0100151819', 'VN0100151819', 'Active', 'Số 1 Giang Văn Minh, Ba Đình, Hà Nội', 'contact@viettel.com.vn', '02462556789', '2025-05-14 09:13:51', '2025-05-29 09:48:14', 'https://viettel.com.vn', '64'),
('2', 'Vingroup JSC', '0101245678', 'VN0101245678', 'Active', 'Số 7 Đường Bằng Lăng 1, Vinhomes Riverside, Hà Nội', 'info@vingroup.net', '02439779999', '2025-05-14 09:13:51', '2025-05-14 09:13:51', 'https://vingroup.net', '90'),
('3', 'FPT Corporation', '0102345678', 'VN0102345678', 'Active', 'Tòa nhà FPT, 17 Duy Tân, Cầu Giấy, Hà Nội', 'fpt@fpt.com.vn', '02473007300', '2025-05-14 09:13:51', '2025-05-14 09:13:51', 'https://fpt.com.vn', '85'),
('4', 'Vinamilk', '0300588569', 'VN0300588569', 'Active', 'Số 10 Tân Trào, Quận 7, TP.HCM', 'vinamilk@vinamilk.com.vn', '02854155555', '2025-05-14 09:13:51', '2025-05-14 09:13:51', 'https://vinamilk.com.vn', '88'),
('5', 'Petrovietnam', '0101456789', 'VN0101456789', 'Active', 'Số 18 Láng Hạ, Ba Đình, Hà Nội', 'info@petrovietnam.com.vn', '02438252526', '2025-05-14 09:13:51', '2025-05-29 09:58:26', 'https://petrovietnam.pvn.vn', '92'),
('6', 'Techcombank', '0100235634', 'VN0100235634', 'Active', '191 Bà Triệu, Hai Bà Trưng, Hà Nội', 'contact@techcombank.com.vn', '02439446699', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://techcombank.com.vn', '90'),
('7', 'Masan Group', '0303576609', 'VN0303576609', 'Active', 'Tầng 12, Tòa nhà MPlaza, 39 Lê Duẩn, Quận 1, TP.HCM', 'info@masangroup.com', '02862563888', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://masangroup.com', '87'),
('8', 'Bamboo Airways', '0108296086', 'VN0108296086', 'Active', '22 Ngõ 106 Lê Trọng Tấn, Thanh Xuân, Hà Nội', 'info@bambooairways.com', '02432337333', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://bambooairways.com', '82'),
('9', 'TH True Milk', '0102859634', 'VN0102859634', 'Active', '166 Phố Huế, Hai Bà Trưng, Hà Nội', 'contact@thtrue milk.vn', '02439765555', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://thtruemilk.vn', '85'),
('10', 'Hoa Sen Group', '3700381324', 'VN3700381324', 'Active', 'Số 9 Đại lộ Thống Nhất, Dĩ An, Bình Dương', 'info@hoasengroup.vn', '02743799199', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://hoasengroup.vn', '80'),
('11', 'Tiki Corporation', '0309532909', 'VN0309532909', 'Active', '52 Út Tịch, Quận Tân Bình, TP.HCM', 'support@tiki.vn', '02873023456', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://tiki.vn', '83'),
('12', 'The Coffee House', '0312867178', 'VN0312867178', 'Active', '86-88 Cao Thắng, Quận 3, TP.HCM', 'hello@coffeehouse.vn', '02871087088', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://thecoffeehouse.com', '78'),
('13', 'Vietnam Airlines', '0100107518', 'VN0100107518', 'Active', '200 Nguyễn Sơn, Long Biên, Hà Nội', 'contact@vietnamairlines.com', '02438320320', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://vietnamairlines.com', '88'),
('14', 'Mobifone', '0100686209', 'VN0100686209', 'Active', 'Số 1 Phạm Văn Bạch, Cầu Giấy, Hà Nội', 'support@mobifone.vn', '02437831666', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://mobifone.vn', '86'),
('15', 'VPBank', '0100235792', 'VN0100235792', 'Active', '89 Láng Hạ, Đống Đa, Hà Nội', 'info@vpbank.com.vn', '02439288888', '2025-05-14 09:16:03', '2025-05-14 09:16:03', 'https://vpbank.com.vn', '89'),
('16', 'Techcombank', '0100235634', 'VN0100235634', 'Active', '191 Bà Triệu, Hai Bà Trưng, Hà Nội', 'contact@techcombank.com.vn', '02439446699', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://techcombank.com.vn', '90'),
('17', 'Masan Group', '0303576609', 'VN0303576609', 'Active', 'Tầng 12, Tòa nhà MPlaza, 39 Lê Duẩn, Quận 1, TP.HCM', 'info@masangroup.com', '02862563888', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://masangroup.com', '87'),
('18', 'Bamboo Airways', '0108296086', 'VN0108296086', 'Active', '22 Ngõ 106 Lê Trọng Tấn, Thanh Xuân, Hà Nội', 'info@bambooairways.com', '02432337333', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://bambooairways.com', '82'),
('19', 'TH True Milk', '0102859634', 'VN0102859634', 'Active', '166 Phố Huế, Hai Bà Trưng, Hà Nội', 'contact@thtrue milk.vn', '02439765555', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://thtruemilk.vn', '85'),
('20', 'Hoa Sen Group', '3700381324', 'VN3700381324', 'Active', 'Số 9 Đại lộ Thống Nhất, Dĩ An, Bình Dương', 'info@hoasengroup.vn', '02743799199', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://hoasengroup.vn', '80'),
('21', 'Tiki Corporation', '0309532909', 'VN0309532909', 'Active', '52 Út Tịch, Quận Tân Bình, TP.HCM', 'support@tiki.vn', '02873023456', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://tiki.vn', '83'),
('22', 'The Coffee House', '0312867178', 'VN0312867178', 'Active', '86-88 Cao Thắng, Quận 3, TP.HCM', 'hello@coffeehouse.vn', '02871087088', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://thecoffeehouse.com', '78'),
('23', 'Vietnam Airlines', '0100107518', 'VN0100107518', 'Active', '200 Nguyễn Sơn, Long Biên, Hà Nội', 'contact@vietnamairlines.com', '02438320320', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://vietnamairlines.com', '88'),
('24', 'Mobifone', '0100686209', 'VN0100686209', 'Active', 'Số 1 Phạm Văn Bạch, Cầu Giấy, Hà Nội', 'support@mobifone.vn', '02437831666', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://mobifone.vn', '86'),
('25', 'VPBank', '0100235792', 'VN0100235792', 'Active', '89 Láng Hạ, Đống Đa, Hà Nội', 'info@vpbank.com.vn', '02439288888', '2025-05-14 09:16:27', '2025-05-14 09:16:27', 'https://vpbank.com.vn', '89'),
('26', 'VinFast', '0108140137', 'VN0108140137', 'Active', 'Khu công nghiệp Đình Vũ, Hải Phòng', 'info@vinfast.vn', '02439779988', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://vinfastauto.com', '85'),
('27', 'Shopee Vietnam', '0315836966', 'VN0315836966', 'Active', 'Tầng 29, Tòa nhà Viettel, 285 Cách Mạng Tháng Tám, Quận 10, TP.HCM', 'support@shopee.vn', '02873081234', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://shopee.vn', '84'),
('28', 'Sovico Group', '0302585648', 'VN0302585648', 'Active', 'Tầng 15, Tòa nhà Vincom, 72 Lê Thánh Tôn, Quận 1, TP.HCM', 'contact@sovico.com', '02838273333', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://sovico.com', '80'),
('29', 'Highlands Coffee', '0312135478', 'VN0312135478', 'Active', 'Tầng 3, Tòa nhà E.Town, 364 Cộng Hòa, Quận Tân Bình, TP.HCM', 'info@highlandscoffee.com.vn', '02838125888', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://highlandscoffee.com.vn', '82'),
('30', 'VNG Corporation', '0303496297', 'VN0303496297', 'Active', 'Tòa nhà VNG Campus, Quận 7, TP.HCM', 'contact@vng.com.vn', '02839623888', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://vng.com.vn', '83'),
('31', 'Sun Group', '0103023456', 'VN0103023456', 'Active', 'Tầng 9, Tòa nhà Sun City, 13 Hai Bà Trưng, Hà Nội', 'info@sungroup.com.vn', '02439393333', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://sungroup.com.vn', '87'),
('32', 'Lazada Vietnam', '0313578989', 'VN0313578989', 'Active', 'Tầng 19, Tòa nhà Saigon Centre, 67 Lê Lợi, Quận 1, TP.HCM', 'support@lazada.vn', '02873066888', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://lazada.vn', '81'),
('33', 'Trung Nguyên Group', '5800000219', 'VN5800000219', 'Active', '82 Nguyễn Huệ, TP. Buôn Ma Thuột, Đắk Lắk', 'info@trungnguyen.com.vn', '02623952345', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://trungnguyen.vn', '84'),
('34', 'PNJ', '0300526318', 'VN0300526318', 'Active', '170E Phan Đăng Lưu, Quận Phú Nhuận, TP.HCM', 'contact@pnj.com.vn', '02839951703', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://pnj.com.vn', '86'),
('35', 'Ecopark', '0102234567', 'VN0102234567', 'Active', 'Khu đô thị Ecopark, Văn Giang, Hưng Yên', 'info@ecopark.com.vn', '02462628888', '2025-05-14 09:21:42', '2025-05-14 09:21:42', 'https://ecopark.com.vn', '80'),
('36', 'Tập đoàn Hòa Phát', '0900189286', 'VN0900189286', 'Active', 'Khu Công nghiệp Phố Nối A, Văn Lâm, Hưng Yên', 'contact@hoaphat.com.vn', '02213987888', '2025-05-30 23:19:24', '2025-05-30 23:39:58', 'https://hoaphat.com.vn', '86'),
('37', 'Công ty Cổ phần Dịch vụ Giao Hàng Nhanh', '0311907295', 'VN0311907295', 'Active', 'Tòa nhà Pico Plaza, 20 Cộng Hòa, P.12, Q.Tân Bình, TP.HCM', 'support@ghn.vn', '1900636677', '2025-05-30 23:19:24', '2025-05-30 23:19:24', 'https://ghn.vn', '88'),
('38', 'Công ty TNHH Tư vấn Giải pháp ABC', '0109876543', 'VN0109876543', 'Active', 'Số 10, Ngõ 55, Đường Cầu Giấy, Hà Nội', 'info@tuvanabc.vn', '02438889999', '2025-05-30 23:19:24', '2025-05-30 23:19:24', 'https://tuvanabc.vn', '75');

-- 3. contracts
CREATE TABLE `contracts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `price` decimal(18,2) DEFAULT NULL,
  `currency` varchar(15) DEFAULT NULL,
  `unit` varchar(45) DEFAULT NULL,
  `signedDate` datetime DEFAULT NULL,
  `expiredDate` datetime DEFAULT NULL,
  `nameA` varchar(50) DEFAULT NULL,
  `phoneA` varchar(15) DEFAULT NULL,
  `nameB` varchar(50) DEFAULT NULL,
  `phoneB` varchar(15) DEFAULT NULL,
  `contractUrl` varchar(255) DEFAULT NULL,
  `serviceId` int DEFAULT NULL,
  `providerId` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `serviceId` (`serviceId`),
  KEY `providerId` (`providerId`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `services` (`id`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`providerId`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table contracts
INSERT INTO `contracts` (`id`, `name`, `status`, `price`, `currency`, `unit`, `signedDate`, `expiredDate`, `nameA`, `phoneA`, `nameB`, `phoneB`, `contractUrl`, `serviceId`, `providerId`) VALUES
('1', 'Hợp đồng cung cấp viễn thông Viettel', 'Signed', '6000000.00', 'VND', 'Gói/tháng', '2025-01-01 00:00:00', '2026-01-01 00:00:00', 'Nguyễn Văn A', '0987654321', 'Trần Thị B', '0912345678', 'https://viettel.com.vn/contract1.pdf', '1', '1'),
('2', 'Hợp đồng quản lý Vinhomes', 'Signed', '12000000.00', 'VND', 'Dự án', '2025-02-01 00:00:00', '2027-02-01 00:00:00', 'Lê Văn C', '0971234567', 'Phạm Thị D', '0908765432', 'https://vingroup.net/contract2.pdf', '2', '2'),
('3', 'Hợp đồng phát triển phần mềm FPT', 'Pending', '25000000.00', 'VND', 'Dự án', '2025-03-01 00:00:00', '2026-03-01 00:00:00', 'Hoàng Văn E', '0967891234', 'Nguyễn Thị F', '0932145678', 'https://fpt.com.vn/contract3.pdf', '3', '3'),
('4', 'Hợp đồng cung cấp sữa Vinamilk', 'Signed', '600000.00', 'VND', 'Sản phẩm', '2025-04-01 00:00:00', '2026-04-01 00:00:00', 'Trần Văn G', '0945678901', 'Lê Thị H', '0923456789', 'https://vinamilk.com.vn/contract4.pdf', '4', '4'),
('5', 'Hợp đồng khai thác dầu khí Petrovietnam', 'Signed', '60000000.00', 'VND', 'Thùng', '2025-05-01 00:00:00', '2028-05-01 00:00:00', 'Phạm Văn I', '0956789012', 'Hoàng Thị K', '0919876543', 'https://petrovietnam.pvn.vn/contract5.pdf', '5', '5'),
('16', 'Hợp đồng cung cấp ô tô VinFast', 'Signed', '600000000.00', 'VND', 'Chiếc', '2025-08-01 00:00:00', '2026-08-01 00:00:00', 'Nguyễn Văn AG', '0982345678', 'Trần Thị AH', '0915678901', 'https://vinfastauto.com/contract26.pdf', '26', '26'),
('17', 'Hợp đồng thương mại Shopee', 'Signed', '250000.00', 'VND', 'Đơn hàng', '2025-08-05 00:00:00', '2026-08-05 00:00:00', 'Lê Văn AI', '0973456789', 'Phạm Thị AJ', '0906789012', 'https://shopee.vn/contract27.pdf', '27', '27'),
('18', 'Hợp đồng đầu tư Sovico', 'Pending', '120000000.00', 'VND', 'Dự án', '2025-08-10 00:00:00', '2027-08-10 00:00:00', 'Hoàng Văn AK', '0964567890', 'Nguyễn Thị AL', '0937890123', 'https://sovico.com/contract28.pdf', '28', '28'),
('19', 'Hợp đồng cà phê Highlands', 'Signed', '45000.00', 'VND', 'Ly', '2025-08-15 00:00:00', '2026-08-15 00:00:00', 'Trần Văn AM', '0945678901', 'Lê Thị AN', '0928901234', 'https://highlandscoffee.com.vn/contract29.pdf', '29', '29'),
('20', 'Hợp đồng game VNG', 'Signed', '6000000.00', 'VND', 'Game', '2025-08-20 00:00:00', '2026-08-20 00:00:00', 'Phạm Văn AO', '0956789012', 'Hoàng Thị AP', '0919012345', 'https://vng.com.vn/contract30.pdf', '30', '30'),
('21', 'Hợp đồng du lịch Sun Group', 'Signed', '25000000.00', 'VND', 'Gói dịch vụ', '2025-08-25 00:00:00', '2027-08-25 00:00:00', 'Nguyễn Văn AQ', '0987890123', 'Trần Thị AR', '0910123456', 'https://sungroup.com.vn/contract31.pdf', '31', '31'),
('22', 'Hợp đồng bán lẻ Lazada', 'Pending', '200000.00', 'VND', 'Đơn hàng', '2025-08-30 00:00:00', '2026-08-30 00:00:00', 'Lê Văn AS', '0978901234', 'Phạm Thị AT', '0901234567', 'https://lazada.vn/contract32.pdf', '32', '32'),
('23', 'Hợp đồng cà phê Trung Nguyên', 'Signed', '350000.00', 'VND', 'Gói sản phẩm', '2025-09-01 00:00:00', '2026-09-01 00:00:00', 'Hoàng Văn AU', '0969012345', 'Nguyễn Thị AV', '0932345678', 'https://trungnguyen.vn/contract33.pdf', '33', '33'),
('24', 'Hợp đồng trang sức PNJ', 'Signed', '1200000.00', 'VND', 'Sản phẩm', '2025-09-05 00:00:00', '2026-09-05 00:00:00', 'Trần Văn AW', '0940123456', 'Lê Thị AX', '0923456789', 'https://pnj.com.vn/contract34.pdf', '34', '34'),
('25', 'Hợp đồng đô thị Ecopark', 'Signed', '60000000.00', 'VND', 'Căn hộ', '2025-09-10 00:00:00', '2027-09-10 00:00:00', 'Phạm Văn AY', '0951234567', 'Hoàng Thị AZ', '0914567890', 'https://ecopark.com.vn/contract35.pdf', '35', '35'),
('26', 'Đơn giao dịch số 1', 'Pending', '10000000.00', 'VND', 'year', '2025-05-30 00:00:00', '2025-06-25 00:00:00', 'dấdasd', 'dasdasd', 'dasdasd', 'dsadsad', NULL, '14', '14'),
('27', 'Hợp đồng mua bỉm', 'Pending', '15000.00', 'USD', 'year', '2025-05-30 00:00:00', '2025-06-03 00:00:00', 'Trần Văn Tân', 'Lê Đức Tiến', 'Ngô Văn Hưng', 'Đặng Thái Sơn', NULL, '7', '7'),
('28', 'Đơn giao dịch số 1', 'Active', '100000.00', 'USD', 'year', '2025-05-30 00:00:00', '2025-06-11 00:00:00', 'Trần Văn Tân', 'Lê Đức Tiến', 'Ngô Văn Hưng', 'Đặng Thái Sơn', NULL, '2', '2'),
('29', 'Đơn giao dịch số 1', 'Active', '10000000.00', 'VND', 'year', '2025-05-29 00:00:00', '2025-06-12 00:00:00', 'Trần Văn Tân', 'Lê Đức Tiến', 'Ngô Văn Hưng', 'Đặng Thái Sơn', NULL, '37', '36');

-- 4. bills
CREATE TABLE `bills` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `des` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `paidDate` date DEFAULT NULL,
  `vat` float DEFAULT NULL,
  `refContractId` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refContractId` (`refContractId`),
  CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`refContractId`) REFERENCES `contracts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table bills
INSERT INTO `bills` (`id`, `name`, `des`, `status`, `quantity`, `createdDate`, `paidDate`, `vat`, `refContractId`) VALUES
('1', 'hoá đơn phát triển game', 'đasad', 'Pending', '1000', '2025-05-27 22:37:49', NULL, '10', '22'),
('2', 'Lô quần áo số 10', 'đâsdsad', 'Paid', '100', '2025-05-27 22:38:02', NULL, '10', '22'),
('3', 'Hoá đơn cung cấp mẫu vinfast3', 'Cung cấp 10000 chiếc VF3 có thời hạn blalala', 'Pending', '10000', '2025-05-27 22:39:17', NULL, '10', '16');

-- 5. ratingcriteria
CREATE TABLE `ratingcriteria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `weight` decimal(3,2) DEFAULT '1.00',
  `status` varchar(20) DEFAULT 'Active',
  `createDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table ratingcriteria
INSERT INTO `ratingcriteria` (`id`, `name`, `description`, `weight`, `status`, `createDate`) VALUES
('1', 'Chất lượng dịch vụ', 'Đánh giá chất lượng dịch vụ được cung cấp', '1.00', 'Active', '2025-05-29 09:47:37'),
('2', 'Thời gian giao hàng', 'Đánh giá về việc giao hàng đúng hạn', '0.90', 'Active', '2025-05-29 09:47:37'),
('3', 'Thái độ phục vụ', 'Đánh giá thái độ phục vụ của nhà cung cấp', '0.85', 'Active', '2025-05-29 09:47:37'),
('4', 'Tính chuyên nghiệp', 'Đánh giá tính chuyên nghiệp trong quá trình làm việc', '0.80', 'Active', '2025-05-29 09:47:37'),
('5', 'Giá cả hợp lý', 'Đánh giá về tính hợp lý của giá so với chất lượng', '0.70', 'Active', '2025-05-29 09:47:37');

-- 6. providerratings
CREATE TABLE `providerratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `providerId` int NOT NULL,
  `userId` int DEFAULT NULL,
  `contractId` int DEFAULT NULL,
  `comment` text,
  `overall` decimal(3,1) DEFAULT '0.0',
  `createDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `providerId` (`providerId`),
  KEY `contractId` (`contractId`),
  CONSTRAINT `providerratings_ibfk_1` FOREIGN KEY (`providerId`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `providerratings_ibfk_2` FOREIGN KEY (`contractId`) REFERENCES `contracts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table providerratings
INSERT INTO `providerratings` (`id`, `providerId`, `userId`, `contractId`, `comment`, `overall`, `createDate`) VALUES
('1', '1', NULL, '1', 'ĐBRR', '3.2', '2025-05-29 09:48:14'),
('2', '5', NULL, '5', 'dsadasd', '4.6', '2025-05-29 09:58:26'),
('3', '36', NULL, NULL, 'đâsd', '4.3', '2025-05-30 23:39:58');

-- 7. provideservice
CREATE TABLE `provideservice` (
  `serviceId` int NOT NULL,
  `providerId` int NOT NULL,
  `providePrice` decimal(18,2) DEFAULT NULL,
  `currency` varchar(15) DEFAULT NULL,
  `unit` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`serviceId`,`providerId`),
  KEY `providerId` (`providerId`),
  CONSTRAINT `provideservice_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `services` (`id`),
  CONSTRAINT `provideservice_ibfk_2` FOREIGN KEY (`providerId`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table provideservice
INSERT INTO `provideservice` (`serviceId`, `providerId`, `providePrice`, `currency`, `unit`) VALUES
('1', '1', '5000000.00', 'VND', 'Gói/tháng'),
('2', '1', '10000000.00', 'VND', 'Dự án'),
('2', '2', '10000000.00', 'VND', 'Dự án'),
('3', '1', '20000000.00', 'VND', 'Dự án'),
('3', '3', '20000000.00', 'VND', 'Dự án'),
('4', '4', '500000.00', 'VND', 'Sản phẩm'),
('5', '5', '50000000.00', 'VND', 'Thùng'),
('6', '6', '1000000.00', 'VND', 'Gói/tháng'),
('7', '7', '200000.00', 'VND', 'Sản phẩm'),
('8', '8', '5000000.00', 'VND', 'Chuyến bay'),
('9', '9', '400000.00', 'VND', 'Sản phẩm'),
('10', '10', '3000000.00', 'VND', 'Tấn'),
('11', '11', '100000.00', 'VND', 'Đơn hàng'),
('12', '12', '30000.00', 'VND', 'Ly'),
('13', '13', '4000000.00', 'VND', 'Gói/tháng'),
('14', '14', '2000000.00', 'VND', 'Gói/tháng'),
('15', '8', '10000000.00', 'VND', 'Chuyến hàng'),
('26', '26', '500000000.00', 'VND', 'Chiếc'),
('27', '27', '200000.00', 'VND', 'Đơn hàng'),
('28', '28', '100000000.00', 'VND', 'Dự án'),
('29', '29', '40000.00', 'VND', 'Ly'),
('30', '30', '5000000.00', 'VND', 'Game'),
('31', '31', '20000000.00', 'VND', 'Gói dịch vụ'),
('32', '32', '150000.00', 'VND', 'Đơn hàng'),
('33', '33', '300000.00', 'VND', 'Gói sản phẩm'),
('34', '34', '1000000.00', 'VND', 'Sản phẩm'),
('35', '35', '50000000.00', 'VND', 'Căn hộ'),
('37', '36', '15000000.00', 'VND', 'Tấn'),
('38', '36', '12000.00', 'VND', 'Kg'),
('39', '36', '3500.00', 'VND', 'Quả'),
('40', '37', '30000.00', 'VND', 'Đơn hàng nội thành'),
('41', '37', '2000000.00', 'VND', 'Pallet/tháng'),
('42', '38', '5000000.00', 'VND', 'Gói tư vấn cơ bản'),
('45', '3', '500000000.00', 'VND', 'Dự án');

-- 8. ratingscores
CREATE TABLE `ratingscores` (
  `ratingId` int NOT NULL,
  `criteriaId` int NOT NULL,
  `score` int NOT NULL,
  PRIMARY KEY (`ratingId`,`criteriaId`),
  KEY `criteriaId` (`criteriaId`),
  CONSTRAINT `ratingscores_ibfk_1` FOREIGN KEY (`ratingId`) REFERENCES `providerratings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratingscores_ibfk_2` FOREIGN KEY (`criteriaId`) REFERENCES `ratingcriteria` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratingscores_chk_1` CHECK (((`score` >= 1) and (`score` <= 5)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table ratingscores
INSERT INTO `ratingscores` (`ratingId`, `criteriaId`, `score`) VALUES
('1', '1', '3'),
('1', '2', '5'),
('1', '3', '2'),
('1', '4', '4'),
('1', '5', '2'),
('2', '1', '5'),
('2', '2', '4'),
('2', '3', '4'),
('2', '4', '5'),
('2', '5', '5'),
('3', '1', '5'),
('3', '2', '5'),
('3', '3', '3'),
('3', '4', '5'),
('3', '5', '3');

-- 9. notifications
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `severity` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `relatedId` int DEFAULT NULL,
  `relatedTable` varchar(50) DEFAULT NULL,
  `isRead` tinyint(1) DEFAULT '0',
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `readDate` datetime DEFAULT NULL,
  `userId` int DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_isread` (`isRead`),
  KEY `idx_notifications_type` (`type`),
  KEY `idx_notifications_user` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data for table notifications
INSERT INTO `notifications` (`id`, `type`, `severity`, `title`, `message`, `relatedId`, `relatedTable`, `isRead`, `createdDate`, `readDate`, `userId`, `expiryDate`) VALUES
('1', 'bill_due', 'urgent', 'Hóa đơn sắp đến hạn: Internet T7/2023', 'Hóa đơn "Internet T7/2023" sẽ đến hạn vào ngày 25/07/2023.', '1', 'Bills', '0', '2025-05-29 10:20:19', NULL, NULL, '2025-06-05 10:20:19'),
('3', 'system', 'info', 'Bảo trì hệ thống', 'Hệ thống sẽ được bảo trì vào ngày 30/07/2023 từ 22:00 - 24:00.', NULL, NULL, '0', '2025-05-29 10:20:19', NULL, NULL, '2025-06-08 10:20:19'),
('4', 'bill_due', 'info', 'Hóa đơn sắp đến hạn: Điện thoại Q2/2023', 'Hóa đơn "Điện thoại Q2/2023" sẽ đến hạn vào ngày 05/08/2023.', '3', 'Bills', '1', '2025-05-27 10:20:19', '2025-05-28 10:20:19', NULL, '2025-06-03 10:20:19'),
('5', 'service_issue', 'urgent', 'Sự cố dịch vụ: Email Server', 'Dịch vụ Email Server đang gặp sự cố. Đội kỹ thuật đang khắc phục.', '4', 'Services', '0', '2025-05-29 10:20:19', NULL, NULL, '2025-05-30 10:20:19'),
('6', 'provider_update', 'info', 'Cập nhật thông tin nhà cung cấp: FPT Telecom', 'Nhà cung cấp FPT Telecom đã cập nhật thông tin liên hệ mới.', '5', 'Providers', '0', '2025-05-29 10:20:19', NULL, NULL, '2025-06-28 10:20:19'),
('7', 'contract_expiring', 'warning', 'Hợp đồng sắp hết hạn: Đơn giao dịch số 1', 'Hợp đồng "Đơn giao dịch số 1" với nhà cung cấp "Vingroup JSC" sẽ hết hạn vào ngày 11/06/2025.', '28', 'Contracts', '0', '2025-05-31 10:15:57', NULL, NULL, '2025-06-18 00:00:00'),
('8', 'contract_expiring', 'warning', 'Hợp đồng sắp hết hạn: Đơn giao dịch số 1', 'Hợp đồng "Đơn giao dịch số 1" với nhà cung cấp "Tập đoàn Hòa Phát" sẽ hết hạn vào ngày 12/06/2025.', '29', 'Contracts', '0', '2025-05-31 10:15:57', NULL, NULL, '2025-06-19 00:00:00');

