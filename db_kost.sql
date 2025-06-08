-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 08 Jun 2025 pada 17.16
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kost`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `email_verification`
--

CREATE TABLE `email_verification` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `verification_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `email_verification`
--

INSERT INTO `email_verification` (`id`, `user_id`, `verification_code`, `created_at`) VALUES
(13, 17, '7d7209', '2025-01-16 14:20:00'),
(19, 27, '969687', '2025-01-28 07:32:38'),
(20, 28, '54024a', '2025-01-28 07:46:23'),
(21, 29, '1c2a1d', '2025-05-16 11:38:46'),
(22, 30, '9fb6ea', '2025-05-17 06:46:47'),
(23, 31, '5f81a3', '2025-05-17 06:50:49'),
(24, 32, 'd1a29d', '2025-05-17 14:15:01'),
(25, 33, 'd5fa94', '2025-06-05 18:42:44'),
(26, 34, 'dec3df', '2025-06-05 18:43:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kost`
--

CREATE TABLE `kost` (
  `id` int NOT NULL,
  `pemilik_id` int NOT NULL,
  `nama_kost` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `list_gambar` text COLLATE utf8mb4_general_ci,
  `harga_per_bulan` decimal(10,2) NOT NULL,
  `kamar_tersisa` int NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `lokasi` text COLLATE utf8mb4_general_ci,
  `fasilitas_kamar` text COLLATE utf8mb4_general_ci,
  `rating_review` decimal(3,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_verified` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kost`
--

INSERT INTO `kost` (`id`, `pemilik_id`, `nama_kost`, `list_gambar`, `harga_per_bulan`, `kamar_tersisa`, `deskripsi`, `lokasi`, `fasilitas_kamar`, `rating_review`, `created_at`, `is_verified`) VALUES
(1, 17, 'kost A', '[\"buckshot-roulette-banner.jpg\",\"53A9563F-EAD6-4F42-A388-840268F00759.png\"]', 100.00, 3, '0', 'slawi', 'wifi', 0.00, '2025-01-19 11:51:40', 1),
(4, 28, 'kost barokah', '[\"\\u2500\\ua725\\ua724 \\ud835\\ude0f\\ud835\\ude2a\\ud835\\ude2e\\ud835\\ude2e\\ud835\\ude26\\ud835\\ude2d \\u02d6.jpg\",\"\\u2500\\ua725\\ua724 \\ud835\\ude0f\\ud835\\ude2a\\ud835\\ude2e\\ud835\\ude2e\\ud835\\ude26\\ud835\\ude2d \\u02d6.jpg\"]', 100000.00, 2, '0', 'kraton', 'kamar mandi dalam, wifi', 0.00, '2025-01-28 07:47:44', 1),
(6, 31, 'kost lihin', '[\"ganjar-pranowo_169.jpeg\",\"GG.jpg\",\"portgas d ace.jpg\"]', 20000.00, 7, 'kost murah dan bersih', 'brebes', 'AC', 0.00, '2025-05-17 11:09:54', 1),
(10, 34, 'test kost', '[\"0ad24284-efb7-4133-9296-8274995c9978_0_1024x768.jpg\"]', 200.00, 2, 'test desc', 'tegal', 'wifi', 0.00, '2025-06-08 16:12:09', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `transaction_id` int NOT NULL,
  `reason` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `refund_requests`
--

INSERT INTO `refund_requests` (`id`, `user_id`, `transaction_id`, `reason`, `created_at`) VALUES
(1, 33, 101, 'Gagal refund otomatis: Midtrans API is returning API error. HTTP status code: 418 API response: {\"status_code\":\"418\",\"status_message\":\"Payment Provider doesn\'t allow refund within this time\",\"id\":\"6c5bd3d7-909d-41ed-a722-8b8872c70095\"}', '2025-06-08 23:43:43'),
(2, 33, 99, 'Gagal refund otomatis: Midtrans API is returning API error. HTTP status code: 418 API response: {\"status_code\":\"418\",\"status_message\":\"Payment Provider doesn\'t allow refund within this time\",\"id\":\"7fbaf184-90fb-48eb-8877-b01f20d690d6\"}', '2025-06-08 23:43:49'),
(3, 33, 103, 'Gagal refund otomatis: Midtrans API is returning API error. HTTP status code: 418 API response: {\"status_code\":\"418\",\"status_message\":\"Payment Provider doesn\'t allow refund within this time\",\"id\":\"b20ea62c-1239-4be3-8e0b-775cd340b6b3\"}', '2025-06-08 23:45:36'),
(4, 33, 105, 'Gagal refund otomatis: Midtrans API is returning API error. HTTP status code: 418 API response: {\"status_code\":\"418\",\"status_message\":\"Payment Provider doesn\'t allow refund within this time\",\"id\":\"de4de753-d532-4027-b7d8-60a1d8510ed1\"}', '2025-06-08 23:55:32'),
(5, 33, 107, 'Gagal refund otomatis: Midtrans API is returning API error. HTTP status code: 418 API response: {\"status_code\":\"418\",\"status_message\":\"Payment Provider doesn\'t allow refund within this time\",\"id\":\"79122517-2f28-4c77-9b81-668b8cd22f24\"}', '2025-06-08 23:59:31'),
(6, 33, 108, 'Gagal refund otomatis: Midtrans API is returning API error. HTTP status code: 418 API response: {\"status_code\":\"418\",\"status_message\":\"Payment Provider doesn\'t allow refund within this time\",\"id\":\"459d24cb-3094-4151-96b2-7371eb89c724\"}', '2025-06-09 00:06:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `kost_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` decimal(3,2) NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `kost_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(2, 4, 29, 5.00, 'testing', '2025-05-17 06:34:52'),
(3, 4, 30, 5.00, 'saya wali', '2025-05-17 06:47:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sewa_requests`
--

CREATE TABLE `sewa_requests` (
  `id` int NOT NULL,
  `kost_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `tanggal_request` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `alasan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `kost_id` int DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `status` enum('pending','settlement','cancelled','refund','refund_manual') DEFAULT NULL,
  `payment_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `snap_token` varchar(255) DEFAULT NULL,
  `is_accepted` enum('pending','accepted','rejected') DEFAULT 'pending',
  `settlement_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `order_id`, `user_id`, `kost_id`, `amount`, `status`, `payment_type`, `created_at`, `updated_at`, `snap_token`, `is_accepted`, `settlement_time`) VALUES
(105, 'KOST-1749401513-33-10', 33, 10, 200, 'refund_manual', 'bank_transfer', '2025-06-08 23:51:56', '2025-06-08 23:55:32', NULL, 'pending', '2025-06-08 23:55:02'),
(106, 'KOST-1749401890-33-10', 33, 10, 200, 'cancelled', 'bank_transfer', '2025-06-08 23:58:15', '2025-06-08 23:58:28', NULL, 'pending', NULL),
(107, 'KOST-1749401921-33-10', 33, 10, 200, 'refund_manual', 'bank_transfer', '2025-06-08 23:58:47', '2025-06-08 23:59:31', NULL, 'pending', '2025-06-08 23:59:06'),
(108, 'KOST-1749402083-33-10', 33, 10, 200, 'refund_manual', 'bank_transfer', '2025-06-09 00:01:27', '2025-06-09 00:06:02', NULL, 'pending', '2025-06-09 00:01:46'),
(109, 'KOST-1749402408-33-10', 33, 10, 200, 'settlement', 'bank_transfer', '2025-06-09 00:06:52', '2025-06-09 00:07:30', '2d32e68d-e63b-4fd0-83e2-1177fb3b0532', 'pending', '2025-06-09 00:07:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','pemilik','admin') COLLATE utf8mb4_general_ci NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `email_verification` varchar(5) COLLATE utf8mb4_general_ci DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_verified`, `created_at`, `reset_token`, `pekerjaan`, `jenis_kelamin`, `alamat`, `email_verification`) VALUES
(17, 'kepin', 'ryankevinnurhakim@gmail.com', '$2y$10$pIVilkKzEpsr8I68wciACuS3JJJcuD.no.xIrR2QZwsFMMV2URf46', 'pemilik', 1, '2025-01-16 14:20:00', 'bf0d75', NULL, NULL, NULL, '0'),
(27, 'admin', 'sadakoooxd@gmail.com', '$2y$10$pSGmxYMXrnGWfSMX08Oh7OEvUVNHMK9qc6.eF0xDvzsjzWwbfH0a.', 'admin', 1, '2025-01-28 07:32:38', NULL, NULL, NULL, NULL, '0'),
(28, 'aden', 'sodakohxd@gmail.com', '$2y$10$o2LE2lvcrnFiIYNJYOyaOue3obrHO77eVFqCxkmMrYbwJdmotEK.2', 'pemilik', 1, '2025-01-28 07:46:23', NULL, 'nganggur', 'Laki-laki', 'jl brebes', '0'),
(29, 'Sahrul', 'sahrulpugas141@gmail.com', '$2y$10$4W3nGDs8s/N9q/UVful48e5c1v/LVlNQaIumXc4AvWUlIhucTKLb.', 'user', 1, '2025-05-16 11:38:46', NULL, '', 'Laki-laki', '', '0'),
(30, 'Gus Javar', 'sahrulsmp4@gmail.com', '$2y$10$GwaZNx5UqfGA2Lxs3dfxLOPTR7pUtVXJLJLz1.qCwpYM2.DA85P8S', 'user', 1, '2025-05-17 06:46:47', NULL, NULL, NULL, NULL, '0'),
(31, 'wali', 'wacaton297@jazipo.com', '$2y$10$aOCR7DVvGhlPDYC81gqHjeLc1kGFsUUrzPsgdrIKVhGugVyPgzK5m', 'pemilik', 1, '2025-05-17 06:50:49', NULL, 'wali tuhan', 'Laki-laki', 'jl alas roban2', '0'),
(32, 'Rudis', 'rudysugiarto12345@gmail.com', '$2y$10$Fbyr.TL6aLWCg3GonauTGOcKHEtAxzRgxmeQgPEfXA8tqEehpF6kC', 'user', 1, '2025-05-17 14:15:01', NULL, '', 'Laki-laki', '', '0'),
(33, 's', 's@gmail.com', '$2y$10$88d85jPrrMCNl8YNntM53OGxL9up3z.LDgvIkzQ3a4mkaoJ/vwOW.', 'user', 1, '2025-06-05 18:42:44', NULL, NULL, NULL, NULL, '0'),
(34, 'a', 'a@gmail.com', '$2y$10$utFXPWLnGLlT/DQdOMS08ON/os0bYhz1X2UprTlehATGptslO6lxW', 'pemilik', 1, '2025-06-05 18:43:00', NULL, NULL, NULL, NULL, '0');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `email_verification`
--
ALTER TABLE `email_verification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `kost`
--
ALTER TABLE `kost`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pemilik_id` (`pemilik_id`);

--
-- Indeks untuk tabel `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kost_id` (`kost_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `sewa_requests`
--
ALTER TABLE `sewa_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `email_verification`
--
ALTER TABLE `email_verification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `kost`
--
ALTER TABLE `kost`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `sewa_requests`
--
ALTER TABLE `sewa_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `email_verification`
--
ALTER TABLE `email_verification`
  ADD CONSTRAINT `email_verification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kost`
--
ALTER TABLE `kost`
  ADD CONSTRAINT `kost_ibfk_1` FOREIGN KEY (`pemilik_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`kost_id`) REFERENCES `kost` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
