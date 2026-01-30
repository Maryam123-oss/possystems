-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 13 ديسمبر 2025 الساعة 18:40
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_site`
--

-- --------------------------------------------------------

--
-- بنية الجدول `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `price_current` varchar(60) DEFAULT NULL,
  `price_old` varchar(60) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `banners`
--

INSERT INTO `banners` (`id`, `title`, `badge`, `price_current`, `price_old`, `description`, `image`, `link`, `sort_order`, `active`, `created_at`) VALUES
(1, 'صيانة انظمة', '30%', '150$', '300%', 'خدمة صيانة جميع الانظمة الخاصة بنا', 'uploads/file_693d988aca9069.18388262.png', 'http://localhost/POS%20Systems/services.php?category=693c4a7aa1c9e&item=3', 0, 1, '2025-12-13 16:47:06'),
(2, 'نظام pos', '20%', '1000$', '1200$', 'نظام نقاط البيع المتكامل', 'uploads/file_693d99b5bd14c5.00796461.jpg', 'http://localhost/POS%20Systems/products.php?category=693afdc046122&item=4', 2, 1, '2025-12-13 16:52:05'),
(3, 'مبرمجين متخصصين', '50%', '', '', 'خدمات برمجية متكاملة', 'uploads/file_693d9a0f198288.83638070.jpg', 'http://localhost/POS%20Systems/services.php?category=693b066ceb691&item=1', 3, 1, '2025-12-13 16:53:35'),
(4, 'برنامج مكافح', '30%', '100$', '130$', 'بنامج المكافح الاول', 'uploads/file_693d9af43eea93.86187891.webp', 'http://localhost/POS%20Systems/products.php?category=693b06c3dc15e&item=2', 0, 1, '2025-12-13 16:57:24'),
(5, 'برنامج الخرائط', '50%', '100$', '50$', 'خرائطك وموقعك مع برنامج الخرائط', 'uploads/file_693d9b3d7516f3.40062181.png', 'http://localhost/POS%20Systems/products.php?category=693b06c3dc15e&item=3', 0, 1, '2025-12-13 16:58:37');

-- --------------------------------------------------------

--
-- بنية الجدول `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `perm_key` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `permissions`
--

INSERT INTO `permissions` (`id`, `perm_key`, `label`) VALUES
(1, 'posts.create', 'إنشاء مقالات'),
(2, 'posts.edit', 'تعديل مقالات'),
(3, 'services.manage', 'إدارة الخدمات'),
(4, 'products.manage', 'إدارة المنتجات'),
(29, 'posts.delete', 'حذف مقالات'),
(32, 'services.category.create', 'إضافة تصنيف خدمات'),
(33, 'services.category.edit', 'تعديل تصنيف خدمات'),
(34, 'services.category.delete', 'حذف تصنيف خدمات'),
(35, 'services.item.create', 'إضافة خدمة'),
(36, 'services.item.edit', 'تعديل خدمة'),
(37, 'services.item.delete', 'حذف خدمة'),
(38, 'products.category.create', 'إضافة تصنيف منتج'),
(39, 'products.category.edit', 'تعديل تصنيف منتج'),
(40, 'products.category.delete', 'حذف تصنيف منتج'),
(41, 'products.create', 'إضافة منتج'),
(42, 'products.edit', 'تعديل منتج'),
(43, 'products.delete', 'حذف منتج'),
(44, 'banners.create', 'إنشاء بنر'),
(45, 'banners.edit', 'تعديل بنر'),
(46, 'banners.delete', 'حذف بنر');

-- --------------------------------------------------------

--
-- بنية الجدول `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `published_at` date DEFAULT NULL,
  `status` enum('draft','pending','published','rejected') DEFAULT 'published',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `posts`
--

INSERT INTO `posts` (`id`, `title`, `excerpt`, `body`, `image`, `published_at`, `status`, `created_by`, `created_at`) VALUES
(1, 'تحديث نظام نقاط البيع 1', 'تم التحديث تم', 'خمهع\r\nنتالبيسبلات\r\nمنعتابيلاتعلنغبفغتقفبسيئبءلؤراهتهعنغتفيقءي', 'uploads/file_693afe6347fe30.43830551.jpg', '2025-12-11', 'published', NULL, '2025-12-11 17:24:51'),
(2, 'kjhgf', 'lkjhgf', 'liuytdf', 'uploads/file_693c4bd8e1afa6.16232471.jpg', '2025-12-11', 'published', NULL, '2025-12-11 19:30:49');

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` varchar(60) DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `specs`, `details`, `image`, `created_at`) VALUES
(1, 1, 'نظام محاسبي كامل', '15000', '[\"متكامل متكامل\",\"ذو كفائه عالية\"]', 'جيد لوزة\r\nممتاز \r\nعالى الجودة', 'uploads/file_693d82238c71b6.59112808.jpeg', '2025-12-11 17:23:30'),
(2, 2, 'برنامج مكافح', '1200$', '[\"مكافح فيروسات\",\"جدار حماية\"]', 'يحمي بياناتك\r\nخصوصياتك\r\nبرامجك \r\nجميع معلوماتك في امان', 'uploads/file_693d81ee6d1085.24742351.jpeg', '2025-12-12 17:07:08'),
(3, 2, 'برنامج خرائط', '1500$', '[\"دقة عالية\",\"تحديد المواقع\"]', 'خرائط متكاملة \r\nسريع\r\nلايحتاج انترنت', 'uploads/file_693d81e209bc44.29372498.jpeg', '2025-12-12 17:13:27'),
(4, 1, 'نظام pos', '170$', '[\"نظام نقاط البيع المتكامل\"]', 'يلبي جميع المطالب \r\nيوفر بيئة سهله \r\nواجهات حديثة', 'uploads/file_693d8214357171.32656240.jpeg', '2025-12-12 17:15:44');

-- --------------------------------------------------------

--
-- بنية الجدول `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'انظمة', '693afdc046122', '2025-12-11 17:22:08'),
(2, 'برامج متنوعة', '693b06c3dc15e', '2025-12-11 17:22:17');

-- --------------------------------------------------------

--
-- بنية الجدول `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'مبرمجين متخصصين', '693b066ceb691', 'ذو كفائه عالية 1', '2025-12-11 17:18:08'),
(2, 'صيانة انظمة', '693c4a7aa1c9e', 'صيانة جميع انواع الانظمة', '2025-12-12 17:01:46');

-- --------------------------------------------------------

--
-- بنية الجدول `service_items`
--

CREATE TABLE `service_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image` varchar(200) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `service_items`
--

INSERT INTO `service_items` (`id`, `category_id`, `name`, `image`, `details`, `features`, `created_at`) VALUES
(1, 1, 'المبرمج / محمد شماخ', 'uploads/file_693afd8142c569.95183959.jpg', 'مبرمج من الطراز الاول 1', '[\"برمجة جميع اللغات\",\"جميع الانظمة\",\"جميع المواقع\",\"جميع برامج الدسك توب\",\"جميع تطبيقات الاندرويد\"]', '2025-12-11 17:21:05'),
(2, 2, 'صيانة انظمة محاسبية', 'uploads/file_693c4b28749d05.21695024.jpg', 'نقوم بصيانة الانظمة المحاسبية بكل كفائه\r\nنصلح جميع المشاكل\r\nحلول كاملة', '[\"سرعة\",\"دقة\",\"مرونة\",\"كفائه\"]', '2025-12-12 17:04:40'),
(3, 2, 'صيانة انظمة نقاط البيع', 'uploads/file_693d81640c4b77.76427239.jpeg', 'نقوم بصيانة الانظمة المحاسبية بكل كفائه نصلح جميع المشاكل حلول كاملة', '[\"سرعة\",\"دقة\",\"كفائه\"]', '2025-12-12 17:09:11'),
(4, 1, 'ياسر المنبهي', 'uploads/file_693d81a27c7603.97293675.jpeg', 'مبرمج ومطور انظمة\r\nبرامج سطح المكتب\r\nتطبيقات اندرويد', '[\"مبرمج من الطراز الاول\",\"ذو كفائه عالية\"]', '2025-12-12 17:11:18');

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'about_headline', 'حلول نقاط بيع موثوقة تدعم نمو عملك', '2025-12-11 17:43:34'),
(2, 'about_description', 'نقدم أنظمة نقاط بيع متكاملة تغطي الأجهزة والبرمجيات مع دعم فني مستمر ولوحات تحكم إدارية مرنة.', '2025-12-11 17:43:34'),
(3, 'contact_phone', '+967 712633106', '2025-12-11 17:56:08'),
(4, 'contact_email', 'mohmmed.shammake.000@gmail.com', '2025-12-11 17:56:08'),
(5, 'contact_address', 'اليمن - صنعاء - جوار وزارة الاعلام', '2025-12-11 17:56:08'),
(6, 'footer_description', 'حلول نقاط بيع متكاملة للأجهزة والبرمجيات والدعم الفني.', '2025-12-11 17:43:34');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `avatar`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$YILKSRa/FAvoLPwEPtVNw.IrYaov.bVWWvl0Wc2.B.fJ2mqnwiNm2', 'admin', '2025-12-11 17:16:39', NULL),
(2, 'Staff', 'staff@example.com', '$2y$10$GioJF705.d4TWvvum8ejMOK1YcUhkJ/VnYYTH9AAWwPF6fOBrLDPe', 'staff', '2025-12-11 17:16:39', NULL),
(3, 'محمد محمد حسين شماخ', 'Mohammed.labtobe.000@gmail.com', '$2y$10$PQ4OOFMbdpZ0PYm4f23XiO1WbH/S.ZgWl8t3E1EvDQhb2/XwgaJcq', 'admin', '2025-12-11 17:25:27', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission`) VALUES
(8, 2, 'posts.create'),
(9, 2, 'posts.delete'),
(10, 2, 'posts.edit'),
(11, 2, 'products.category.create'),
(12, 2, 'products.category.delete'),
(13, 2, 'products.category.edit'),
(14, 2, 'products.create'),
(15, 2, 'products.delete'),
(16, 2, 'products.edit'),
(17, 2, 'products.manage');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perm_key` (`perm_key`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_posts_created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `service_items`
--
ALTER TABLE `service_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_perm_unique` (`user_id`,`permission`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_items`
--
ALTER TABLE `service_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `service_items`
--
ALTER TABLE `service_items`
  ADD CONSTRAINT `service_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `fk_user_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
