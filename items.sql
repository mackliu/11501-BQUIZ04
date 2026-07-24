-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2026-07-24 05:25:10
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `db06`
--

-- --------------------------------------------------------

--
-- 資料表結構 `items`
--

CREATE TABLE `items` (
  `id` int(10) UNSIGNED NOT NULL,
  `no` text NOT NULL,
  `name` text NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `spec` text NOT NULL,
  `qt` int(10) UNSIGNED NOT NULL,
  `img` text NOT NULL,
  `intro` text NOT NULL,
  `big` int(10) UNSIGNED NOT NULL,
  `mid` int(10) UNSIGNED NOT NULL,
  `sh` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `items`
--

INSERT INTO `items` (`id`, `no`, `name`, `price`, `spec`, `qt`, `img`, `intro`, `big`, `mid`, `sh`) VALUES
(1, '728172', '手工訂製長夾', 1200, '全牛皮', 2, '0403.jpg', '手工製作長夾卡片層6*2 鈔票層 *2 零錢拉鍊層 *1 \r\n採用愛馬仕相同的雙針縫法,皮件堅固耐用不脫線 \r\n材質:直革鞣(馬鞍皮)牛皮製作  \r\n手工染色 ', 1, 6, 0),
(2, '117421', '兩用式磁扣腰包', 685, '中型', 18, '0404.jpg', '材質:進口牛皮\r\n顏色:黑色荔枝紋+黑色珠光面皮(黑色縫線)\r\n尺寸:15cm*14cm(高)*6cm(前後)\r\n產地:臺灣', 1, 6, 1),
(3, '115186', '超薄設計男士長款真皮', 800, 'L號', 61, '0405.jpg', '基本:編織皮革對摺長款零錢包\r\n特色:最潮流最時尚的單品 \r\n顏色:黑色珠光面皮(黑色縫線)\r\n形狀:黑白格編織皮革對摺 \r\n', 1, 6, 1),
(4, '857272', '經典牛皮少女帆船鞋', 1000, 'S號', 6, '0406.jpg', '以傳統學院派風格聞名，創始近百年工藝製鞋精神\r\n共用獨家專利氣墊技術，兼具紐約工藝精神，與舒適跑格靈魂', 2, 8, 0),
(5, '465335', '經典優雅時尚流行涼鞋', 2650, 'LL', 8, '0407.jpg', '優雅流線方型楦頭設計，結合簡潔線條綴飾，\r\n獨特的弧度與曲線美，突顯年輕優雅品味，\r\n是年輕上班族不可或缺的鞋款！\r\n全新美國運回，現貨附鞋盒', 2, 12, 1),
(7, '145119', '反折式大容量手提肩背包', 888, 'L號', 15, '0409.jpg', '特色:反折式的包口設計,釘釦的裝飾,讓簡單的包型更增添趣味性\r\n材質:棉布\r\n顏色:藍色\r\n尺寸:長50cm寬20cm高41cm\r\n產地:日本', 4, 15, 1);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `items`
--
ALTER TABLE `items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
