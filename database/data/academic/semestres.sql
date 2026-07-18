-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : sam. 18 juil. 2026 à 12:13
-- Version du serveur : 8.0.46-0ubuntu0.22.04.3
-- Version de PHP : 8.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `edsp_app`
--

-- --------------------------------------------------------

--
-- Structure de la table `semestres`
--

CREATE TABLE `semestres` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordre` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `semestres`
--

INSERT INTO `semestres` (`id`, `code`, `nom`, `ordre`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'S1', 'Semestre 1', 1, 1, '2026-06-03 07:31:37', '2026-06-03 07:31:37', NULL),
(2, 'S2', 'Semestre 2', 2, 1, '2026-06-03 07:31:37', '2026-06-15 03:25:50', NULL),
(3, 'S3', 'Semestre 3', 3, 1, '2026-06-03 07:31:37', '2026-06-03 07:31:37', NULL),
(4, 'S4', 'Semestre 4', 4, 1, '2026-06-03 07:31:37', '2026-06-15 03:25:50', NULL),
(5, 'S5', 'Semestre 5', 5, 1, '2026-06-03 07:31:37', '2026-06-03 07:31:37', NULL),
(6, 'S6', 'Semestre 6', 6, 1, '2026-06-03 07:31:37', '2026-06-15 03:25:51', NULL),
(7, 'S7', 'Semestre 7', 7, 1, '2026-06-03 07:31:37', '2026-06-03 07:31:37', NULL),
(8, 'S8', 'Semestre 8', 8, 1, '2026-06-03 07:31:37', '2026-06-15 03:25:52', NULL),
(9, 'S9', 'Semestre 9', 9, 1, '2026-06-03 07:31:38', '2026-06-03 07:31:38', NULL),
(10, 'S10', 'Semestre 10', 10, 1, '2026-06-03 07:31:38', '2026-06-15 03:25:52', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `semestres`
--
ALTER TABLE `semestres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `semestres_code_unique` (`code`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `semestres`
--
ALTER TABLE `semestres`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
