-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 23, 2026 lúc 11:33 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `gtpt`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`ID`, `Name`) VALUES
(1, 'vip'),
(2, 'thường\r\n');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `districts`
--

CREATE TABLE `districts` (
  `ID` int(10) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `districts`
--

INSERT INTO `districts` (`ID`, `Name`) VALUES
(1, 'Hà Tĩnh'),
(2, 'Nghệ An');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `motel`
--

CREATE TABLE `motel` (
  `ID` int(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `count_view` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `latlng` varchar(255) NOT NULL,
  `images` varchar(255) NOT NULL,
  `user_id` int(10) NOT NULL,
  `category_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `utilities` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(255) NOT NULL,
  `approve` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `motel`
--

INSERT INTO `motel` (`ID`, `title`, `description`, `price`, `area`, `count_view`, `address`, `latlng`, `images`, `user_id`, `category_id`, `district_id`, `utilities`, `created_at`, `phone`, `approve`) VALUES
(1, 'phòng trọ', 'hehehee', 1500, 38, 107, 'Hà Tĩnh', '/googlemap.com', 'thiet-ke-phong-tro-co-gac-lung-va-cay-canh-651cda6cc9649b0ef5c6f699.webp', 1, 2, 1, 'hihihihih', '2026-05-23 09:08:06', '12345678', 1),
(3, 'Phòng trọ tiện ích', 'Phòng trọ với không gian rộng rãi, thoáng mát, đầy đủ tiện nghi', 2300000, 20, 0, 'Số 22 đường Nguyễn Văn Cừ, thành phố Vinh, tỉnh Nghệ An', '', 'trang-tri-bang-giay-dan-tuong-1.jpg', 4, 1, 2, 'Máy giặt, điều hòa', '2026-05-23 09:08:22', '1234567890', -1),
(4, 'Trọ mới xây', 'Trọ vừa xây xong tại thành phố Hà Tĩnh', 2800000, 25, 2, 'Số 10 đường Lê Duẩn, thành phố Hà Tĩnh', '', 'photo-7-16893435804191174071458.webp', 4, 1, 1, 'Máy giặt, điều hòa, wifi', '2026-05-23 09:07:47', '0987654321', 1),
(5, 'Trọ chill', 'Phòng trọ với thiết kế nhẹ nhàng, ấm áp :>>', 2000000, 18, 3, 'Số 9 ngõ 55 đường Hồ Tùng Mậu, thành phố Vinh, tỉnh Nghệ An', '', 'trang-tri-phong-tro.jpg', 4, 1, 2, 'Đèn sưởi, điều hòa, wifi', '2026-05-23 09:07:19', '1112223334', 1),
(12, 'Phòng trọ giá rẻ gần chợ Vinh', 'Phòng sạch sẽ, an ninh tốt, gần chợ và bến xe', 1500000, 18, 45, 'Đường Trần Phú, TP Vinh, Nghệ An', '18.6738,105.6882', 'cach-trang-tri-phong-tro-kieu-han-quoc.jpg', 4, 1, 2, 'Wifi,Chỗ để xe,WC riêng', '2026-05-23 09:19:50', '0911222333', 1),
(13, 'Căn hộ mini đầy đủ tiện nghi', 'Có điều hòa, máy giặt, khu vực yên tĩnh', 3000000, 28, 88, 'Đường Nguyễn Sỹ Sách, TP Vinh, Nghệ An', '18.6821,105.6915', 'thiet-ke-phong-tro-dep-27.jpg', 4, 2, 1, 'Wifi,Điều hòa,Máy giặt,Tủ lạnh', '2026-05-23 09:20:16', '0922333444', 1),
(14, 'Phòng trọ sinh viên gần Đại học Vinh', 'Phòng phù hợp sinh viên, giá rẻ, giờ giấc tự do', 1300000, 16, 110, 'Đường Lê Viết Thuật, TP Vinh, Nghệ An', '18.6804,105.6751', '3-8.jpg', 5, 1, 1, 'Wifi,Chỗ để xe', '2026-05-23 09:22:37', '0933444555', 1),
(15, 'Phòng trọ cao cấp trung tâm thành phố', 'Phòng rộng, có camera an ninh và bảo vệ', 4000000, 40, 160, 'Đường Hồ Tùng Mậu, TP Vinh, Nghệ An', '18.6759,105.6842', '1779009266_6a0986f2ce747.jpeg', 5, 2, 1, 'Wifi,Điều hòa,Camera,Bãi xe', '2026-05-23 09:22:50', '0944555666', 1),
(16, 'Nhà trọ mới xây gần trường học', 'Phòng mới xây, sạch đẹp, thoáng mát', 2100000, 22, 55, 'Đường Phạm Đình Toái, TP Vinh, Nghệ An', '18.6788,105.6907', '1779012721_6a0994711c912.jpeg', 4, 2, 1, 'Wifi,Điều hòa,WC riêng', '2026-05-23 09:23:02', '0955666777', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `ID` int(10) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` int(11) NOT NULL,
  `Phone` varchar(255) NOT NULL,
  `Avatar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`ID`, `Name`, `Username`, `Email`, `Password`, `Role`, `Phone`, `Avatar`) VALUES
(1, 'admin', 'admin', 'admin123@', '$2y$10$jc07cJm8in8SXaIe0ExzWeam/BWuU/KH2IsnOcp9XbLBAs49k3MlK', 1, '1111111111', ''),
(4, 'Võ Quốc Dương', 'test', 'voquocduongvqd06012005@gmail.com', '$2y$10$HuMvbtMtdWkXrTQbUYqP9u40X/RdNz6hFRA9IaZhgheZoTRGQlsiK', 0, '0386795170', 'uploads/avatar_4_1779008525.jpg'),
(5, 'Võ Quốc Dương', 'test2', 'voquocduongvqd060112005@gmail.com', '$2y$10$jc07cJm8in8SXaIe0ExzWeam/BWuU/KH2IsnOcp9XbLBAs49k3MlK', 0, '0386795170', '');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`ID`);

--
-- Chỉ mục cho bảng `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`ID`);

--
-- Chỉ mục cho bảng `motel`
--
ALTER TABLE `motel`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `a` (`district_id`),
  ADD KEY `b` (`user_id`),
  ADD KEY `c` (`category_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `category`
--
ALTER TABLE `category`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `motel`
--
ALTER TABLE `motel`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `motel`
--
ALTER TABLE `motel`
  ADD CONSTRAINT `a` FOREIGN KEY (`district_id`) REFERENCES `districts` (`ID`),
  ADD CONSTRAINT `b` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `c` FOREIGN KEY (`category_id`) REFERENCES `category` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
