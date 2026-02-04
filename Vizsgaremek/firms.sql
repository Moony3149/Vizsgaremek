-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Feb 04. 15:26
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `firms`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `favorites`
--

INSERT INTO `favorites` (`user_id`, `product_id`) VALUES
(3, 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `firm`
--

CREATE TABLE `firm` (
  `ID` int(11) NOT NULL,
  `brand_name` varchar(50) NOT NULL,
  `worker_name` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `profile_pic` varchar(255) DEFAULT 'default_firm.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `firm`
--

INSERT INTO `firm` (`ID`, `brand_name`, `worker_name`, `email`, `password`, `approved`, `profile_pic`) VALUES
(1, 'HARIBO', '', 'haribo.og@gmail.com', '$2y$10$vNU0lh7ZY0AhpY9JBIISVerfn83r5jLd7c16mlmB6IKm51F2OtKEe', 1, 'default_firm.png');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `weight` int(8) NOT NULL,
  `amount` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `type` enum('Zöldség és gyümölcs','Tejtermék- tojás','Pékáru','Húsáru','Mélyhűtött','Alapvető élelmiszerek','Italok','Speciális','Háztartás','Drogéria','Kisállat','Otthon-hobbi') NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `firm_id` int(11) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`ID`, `name`, `description`, `price`, `weight`, `amount`, `active`, `type`, `picture`, `firm_id`, `approved`) VALUES
(1, 'Haribo Goldbären gyümölcsízű gumicukorka', 'A Goldbären-t mindenki ismeri – nem véletlenül. Hiszen ez az eredeti és összetéveszthetetlen gumicukorka 1922 óta okoz örömet kicsi és nagy, fiatalabb vagy idősebb rajongók számára. A hat színpompás medvécske – a legfinomabb gyümölcsízekben az ananásztól ', 499.00, 100, 3425, 1, 'Alapvető élelmiszerek', 'letoltes.jpg', 1, 1),
(2, 'Haribo Goldbären gyümölcsízű gumicukorka', 'A Goldbären-t mindenki ismeri – nem véletlenül. Hiszen ez az eredeti és összetéveszthetetlen gumicukorka 1922 óta okoz örömet kicsi és nagy, fiatalabb vagy idősebb rajongók számára. A hat színpompás medvécske – a legfinomabb gyümölcsízekben az ananásztól ', 379.00, 80, 4534, 1, 'Alapvető élelmiszerek', 'letoltes.jpg', 1, 1),
(3, 'HARIBO Starmix', 'hhhhhhh', 899.00, 250, 7, 1, 'Alapvető élelmiszerek', '1770198067_Haribo-Starmix-Minis-250g.jpg', 1, 1),
(4, 'HARIBO Rainbow Frogs', '..........', 499.00, 142, 5, 1, 'Alapvető élelmiszerek', '1770198732_Harbio_frogs.jpg', 1, 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `shopping_list`
--

CREATE TABLE `shopping_list` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admin` varchar(20) NOT NULL DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT 'default_user.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `name`, `userName`, `email`, `password`, `admin`, `profile_pic`) VALUES
(1, 'valami', 'valami', 'valami@hotmail.com', '$2y$10$pNVhxRayvnlgreujtjYi0.wBay07v27dr0gkyMMwCE0GtQyeZzKWq', 'user', 'default_user.png'),
(2, 'Kovács Antal', 'thinkjan', 'kovacsan@gmail.com', 'password123', 'admin', 'default_user.png'),
(3, 'Admin', 'admin', 'admin@bolt.hu', '$2y$10$w5C0UJMfwkrOUfuKnymCzumLIUJRuPIpYUQUTRAIbIJxJwvWA4Nv6', 'admin', 'default_user.png'),
(4, 'Admin', 'admin', 'admin@bolt.hu', '$2y$10$RAFXEyDNtXTb2mbi8cINj.j6PnZqVh1QJBm3Pt8abT/ZrvIuiZVVi', 'user', 'prof_1769776487.png'),
(5, 'vasarlo1', 'Béla', 'elsovas@hotmail.com', '$2y$10$c9ssHKwXXjWsxganFUwwAO3d96Ai2s4/h9RapOZUCWLn5/kE9e1Z6', 'user', 'default_user.png');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`product_id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`product_id`);

--
-- A tábla indexei `firm`
--
ALTER TABLE `firm`
  ADD PRIMARY KEY (`ID`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `firm_id` (`firm_id`),
  ADD KEY `price` (`price`),
  ADD KEY `price_2` (`price`);

--
-- A tábla indexei `shopping_list`
--
ALTER TABLE `shopping_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `firm`
--
ALTER TABLE `firm`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `shopping_list`
--
ALTER TABLE `shopping_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`firm_id`) REFERENCES `firm` (`ID`);

--
-- Megkötések a táblához `shopping_list`
--
ALTER TABLE `shopping_list`
  ADD CONSTRAINT `fk_list_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_list_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
