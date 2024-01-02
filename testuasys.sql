-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Loomise aeg: Jaan 02, 2024 kell 03:30 PL
-- Serveri versioon: 10.4.28-MariaDB
-- PHP versioon: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Andmebaas: `test`
--

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `uasys_crossref`
--

CREATE TABLE `uasys_crossref` (
  `id` int(11) NOT NULL,
  `table_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`table_value`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci;

--
-- Andmete tõmmistamine tabelile `uasys_crossref`
--

INSERT INTO `uasys_crossref` (`id`, `table_value`) VALUES
(1, '{\"events\":1,\"beers\":1}'),
(2, '{\"events\":1,\"beers\":2}'),
(3, '{\"events\":2,\"beers\":1}'),
(4, '{\"events\":2,\"beers\":2}'),
(8, '{\"events\":2,\"beers\":3}'),
(9, '{\"products\":[4,5]}'),
(10, '{\"products\":[6,4]}'),
(11, '{\"products\":[4,7]}'),
(12, '{\"products\":[4,8]}'),
(13, '{\"products\":[4,9]}'),
(14, '{\"products\":[9,3]}'),
(15, '{\"products\":[6,3]}');

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `uasys_models`
--

CREATE TABLE `uasys_models` (
  `id` int(11) NOT NULL,
  `table_name` varchar(32) NOT NULL,
  `pk` varchar(32) NOT NULL DEFAULT '''id''',
  `can_belong_to` tinyint(4) NOT NULL DEFAULT 0,
  `field_data` enum('default','custom') NOT NULL DEFAULT 'default',
  `can_hmabt` tinyint(4) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_when` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_by` int(11) DEFAULT NULL,
  `modified_when` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci;

--
-- Andmete tõmmistamine tabelile `uasys_models`
--

INSERT INTO `uasys_models` (`id`, `table_name`, `pk`, `can_belong_to`, `field_data`, `can_hmabt`, `created_by`, `created_when`, `modified_by`, `modified_when`) VALUES
(1, 'beers', 'id', 1, 'default', 1, 0, '2023-12-25 20:36:31', NULL, NULL),
(2, 'conductors', 'id', 1, 'default', 0, 0, '2023-12-25 20:36:31', 72, '2023-12-28 17:56:38'),
(3, 'events', 'id', 0, 'default', 1, 0, '2023-12-25 20:36:31', NULL, NULL),
(4, 'instruments', 'id', 1, 'default', 0, 0, '2023-12-25 20:36:31', NULL, NULL),
(5, 'orchestras', 'id', 0, 'default', 0, 0, '2023-12-25 20:36:31', NULL, NULL),
(6, 'players', 'id', 1, 'default', 0, 0, '2023-12-25 20:36:31', NULL, NULL),
(7, 'producers', 'id', 0, 'default', 0, 0, '2023-12-25 20:36:31', NULL, NULL),
(8, 'products', 'id', 0, 'default', 1, 0, '2023-12-25 20:36:31', NULL, NULL),
(9, 'manufacturers', 'id', 0, 'default', 0, 72, '2023-12-26 20:31:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `uasys_relations`
--

CREATE TABLE `uasys_relations` (
  `id` int(11) NOT NULL,
  `type` enum('belongsTo - no hasMany','hasManyAndBelongsTo','belongsTo','inner hasManyAndBelongsTo','inner belongsTo - no has Many','inner belongsTo') NOT NULL,
  `allow_has_many` tinyint(1) DEFAULT NULL,
  `is_inner` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci;

--
-- Andmete tõmmistamine tabelile `uasys_relations`
--

INSERT INTO `uasys_relations` (`id`, `type`, `allow_has_many`, `is_inner`) VALUES
(1, 'belongsTo', 1, 0),
(2, 'hasManyAndBelongsTo', 0, 0),
(3, 'inner belongsTo', 1, 1),
(4, 'inner hasManyAndBelongsTo', 0, 1),
(5, 'belongsTo - no hasMany', 0, 0),
(6, 'inner belongsTo - no has Many', 0, 1);

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `uasys_relation_settings`
--

CREATE TABLE `uasys_relation_settings` (
  `id` int(11) NOT NULL,
  `relations_id` int(11) NOT NULL,
  `role` enum('belongsTo','hasMany','hasManyAndBelongsTo','hasAny') NOT NULL,
  `key_field` varchar(32) NOT NULL,
  `hasMany` tinyint(1) NOT NULL DEFAULT 0,
  `models_id` int(11) NOT NULL,
  `other_table` varchar(50) DEFAULT NULL,
  `mode` enum('hasMany__one_many__belongsTo','hasManyAndBelongsTo','hasMany','belongsTo','hasAny') DEFAULT NULL,
  `many_id` int(11) DEFAULT NULL,
  `many_table` varchar(64) DEFAULT NULL,
  `many_fk` varchar(32) DEFAULT NULL,
  `many_many` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `many_many_ids` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `any_id` int(11) DEFAULT NULL,
  `any_any` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `any_table` varchar(32) DEFAULT NULL,
  `any_pk` varchar(32) DEFAULT NULL,
  `one_pk` varchar(32) DEFAULT NULL,
  `one_table` varchar(64) DEFAULT NULL,
  `one_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 72,
  `created_when` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_by` int(11) DEFAULT NULL,
  `modified_when` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci;

--
-- Andmete tõmmistamine tabelile `uasys_relation_settings`
--

INSERT INTO `uasys_relation_settings` (`id`, `relations_id`, `role`, `key_field`, `hasMany`, `models_id`, `other_table`, `mode`, `many_id`, `many_table`, `many_fk`, `many_many`, `many_many_ids`, `any_id`, `any_any`, `any_table`, `any_pk`, `one_pk`, `one_table`, `one_id`, `created_by`, `created_when`, `modified_by`, `modified_when`) VALUES
(1, 1, 'hasMany', 'id', 1, 7, 'beers', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2023-12-30 13:28:10'),
(2, 1, 'belongsTo', 'producers_id', 0, 1, 'producers', 'hasMany__one_many__belongsTo', 1, 'beers', 'producers_id', NULL, NULL, NULL, NULL, NULL, NULL, 'id', 'producers', 7, 72, '2023-12-29 15:07:53', NULL, '2023-12-31 17:36:07'),
(3, 2, 'hasManyAndBelongsTo', 'id', 0, 1, 'events', 'hasManyAndBelongsTo', NULL, NULL, NULL, '[{\"id\":1, \"table\":\"beers\",\"pk\":\"id\",\"value\":0},{\"id\":3,\"table\":\"events\",\"pk\":\"id\",\"value\":0}]', '[1,3]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2024-01-01 22:27:19'),
(4, 2, 'hasManyAndBelongsTo', 'id', 0, 3, 'beers', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2023-12-30 13:28:10'),
(5, 3, 'hasManyAndBelongsTo', 'id', 0, 8, 'products', 'hasManyAndBelongsTo', NULL, NULL, NULL, '{\"id\":8, \"table\":\"products\",\"pk\":\"id\",\"value\":[0,0]}', '[8]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2024-01-02 03:24:43'),
(6, 4, 'belongsTo', 'orchestraId', 0, 2, 'orchestras', 'hasMany__one_many__belongsTo', 2, 'conductors', 'orchestraId', NULL, NULL, NULL, NULL, NULL, NULL, 'id', 'orchestras', 5, 72, '2023-12-29 15:07:53', NULL, '2023-12-31 17:36:14'),
(7, 4, 'hasMany', 'id', 1, 5, 'conductors', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2023-12-30 13:28:10'),
(8, 6, 'belongsTo', 'orchestraId', 0, 4, 'orchestras', 'hasMany__one_many__belongsTo', 4, 'instruments', 'orchestraId', NULL, NULL, NULL, NULL, NULL, NULL, 'id', 'orchestras', 5, 72, '2023-12-29 15:07:53', NULL, '2023-12-31 17:36:22'),
(9, 6, 'hasMany', 'id', 1, 5, 'instruments', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2023-12-30 13:28:10'),
(10, 7, 'belongsTo', 'instruments_id', 0, 6, 'instruments', 'hasMany__one_many__belongsTo', 6, 'players', 'instruments_id', NULL, NULL, NULL, NULL, NULL, NULL, 'id', 'instruments', 4, 72, '2023-12-29 15:07:53', NULL, '2023-12-31 17:36:26'),
(11, 7, 'hasMany', 'id', 1, 4, 'players', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2023-12-30 13:28:10'),
(12, 2, 'hasAny', 'id', 1, 9, 'manufacturers', 'hasAny', NULL, NULL, NULL, NULL, NULL, 9, '{\"otherTable\":{\"name\":\"table\",\"pk\":\"id\",\"value\":0}}', 'manufacturers', 'id', NULL, NULL, NULL, 72, '2023-12-29 15:07:53', NULL, '2023-12-31 12:19:28');

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `uasys_users`
--

CREATE TABLE `uasys_users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `social` varchar(255) DEFAULT NULL,
  `user_token` varchar(255) DEFAULT NULL,
  `identity_token` varchar(255) DEFAULT NULL,
  `role` enum('ADMIN','EDITOR','PERSON','USER') NOT NULL,
  `persons_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_estonian_ci;

--
-- Andmete tõmmistamine tabelile `uasys_users`
--

INSERT INTO `uasys_users` (`id`, `first_name`, `last_name`, `username`, `email`, `password`, `social`, `user_token`, `identity_token`, `role`, `persons_id`) VALUES
(1, 'Jaanus', 'Nurmoja', 'Jaanus in dev mode', 'jaanus@nurmoja.net.ee', 'mgrmgaenfw', 'arendus', 'dlkfnefgnweobfnmjfe', 'dndfdfnsbfwebf', 'ADMIN', NULL),
(2, NULL, NULL, 'Jaanus in another mode', 'yld@nurmoja.net.ee', '', 'testimine', 'dlkfnefgnweobfnmjfeabvgd', 'dndfdfnsbfwebfahaha', 'USER', NULL),
(3, NULL, NULL, 'Jaanus in night mode', 'silvia@nurmoja.net.ee', '', 'öötöö', '', '', 'USER', NULL),
(5, NULL, NULL, 'Jaanus Nurmoja', 'jaanus.nurmoja@gmail.com', '', 'google', 'd63f7344-ce41-4580-b8f4-5a835d00b405', 'ac3e208d-0e55-4ede-b1bc-44176403b9e3', 'ADMIN', NULL),
(6, NULL, NULL, 'Jaanus Nurmoja', 'jaanus.nurmoja@gmail.com', '', 'github', 'fa8cad68-1dbe-4f66-b3b2-b52ba6872bfd', '655529c8-b9f3-4018-b718-2bc168114743', 'USER', NULL),
(7, NULL, NULL, 'Jaanus Nurmoja', '', '', 'twitter', '783d9d96-01be-446c-93ba-f12ea762f4ad', '7e9e1c2d-ed00-40d3-b103-8d142e294058', 'USER', NULL),
(8, NULL, NULL, 'Jaanus Nurmoja', 'jaanus.nurmoja@outlook.com', '', 'windowslive', 'af6409a8-958b-47cd-bea8-2c9ec488d740', '112c680c-fefa-4645-ad8b-bbb1df93c749', 'USER', NULL),
(28, NULL, NULL, 'NURMOJA,SILVIA,43210270233', '43210270233@eesti.ee', NULL, 'eID', NULL, NULL, 'USER', 1),
(29, NULL, NULL, 'Silvia Nurmoja', 'silvia.nurmoja@gmail.com', NULL, 'google', NULL, NULL, 'USER', NULL),
(30, NULL, NULL, 'Silvia Nurmoja', '', NULL, 'openid', NULL, NULL, 'USER', NULL),
(31, NULL, NULL, '', '', NULL, 'openid', NULL, NULL, 'USER', NULL),
(33, NULL, NULL, 'Jaanus Nurmoja', 'jaanus.nurmoja@gmail.com', NULL, 'facebook', NULL, NULL, 'USER', NULL),
(35, NULL, NULL, 'TAMBI,KAIA,47410265225', '47410265225@eesti.ee', NULL, 'eID', NULL, NULL, 'USER', 3),
(71, NULL, NULL, 'NURMOJA,JAANUS,36706230305', '36706230305@eesti.ee', NULL, 'eID', NULL, NULL, 'USER', 40),
(72, NULL, NULL, 'PETERSON,KRISTJAN JAAK,10103140001', '10103140001@eesti.ee', NULL, 'eID', NULL, NULL, 'USER', 41);

--
-- Indeksid tõmmistatud tabelitele
--

--
-- Indeksid tabelile `uasys_crossref`
--
ALTER TABLE `uasys_crossref`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_value` (`table_value`(768));

--
-- Indeksid tabelile `uasys_models`
--
ALTER TABLE `uasys_models`
  ADD PRIMARY KEY (`id`);

--
-- Indeksid tabelile `uasys_relations`
--
ALTER TABLE `uasys_relations`
  ADD PRIMARY KEY (`id`);

--
-- Indeksid tabelile `uasys_relation_settings`
--
ALTER TABLE `uasys_relation_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `many_many_ids` (`many_many_ids`) USING HASH,
  ADD KEY `models` (`models_id`),
  ADD KEY `relations` (`relations_id`),
  ADD KEY `many` (`many_id`),
  ADD KEY `one` (`one_id`),
  ADD KEY `any_id` (`any_id`);
ALTER TABLE `uasys_relation_settings` ADD FULLTEXT KEY `xref` (`many_many`);
ALTER TABLE `uasys_relation_settings` ADD FULLTEXT KEY `any_any` (`any_any`);

--
-- Indeksid tabelile `uasys_users`
--
ALTER TABLE `uasys_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `oneuser` (`username`,`email`,`social`),
  ADD KEY `persons` (`persons_id`);

--
-- AUTO_INCREMENT tõmmistatud tabelitele
--

--
-- AUTO_INCREMENT tabelile `uasys_crossref`
--
ALTER TABLE `uasys_crossref`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT tabelile `uasys_models`
--
ALTER TABLE `uasys_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT tabelile `uasys_relations`
--
ALTER TABLE `uasys_relations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT tabelile `uasys_relation_settings`
--
ALTER TABLE `uasys_relation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `uasys_users`
--
ALTER TABLE `uasys_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Tõmmistatud tabelite piirangud
--

--
-- Piirangud tabelile `uasys_relation_settings`
--
ALTER TABLE `uasys_relation_settings`
  ADD CONSTRAINT `uasys_relation_settings_ibfk_1` FOREIGN KEY (`many_id`) REFERENCES `uasys_models` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
