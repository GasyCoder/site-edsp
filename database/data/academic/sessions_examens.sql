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
-- Structure de la table `sessions_examens`
--

CREATE TABLE `sessions_examens` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_rattrapage` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions_examens`
--

INSERT INTO `sessions_examens` (`id`, `code`, `nom`, `is_rattrapage`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'NORMAL', 'Session normale', 0, 1, '2026-06-03 07:31:38', '2026-06-03 07:31:38', NULL),
(2, 'RATTRAPAGE', 'Session de rattrapage', 1, 0, '2026-06-03 07:31:38', '2026-06-03 07:31:38', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `sessions_examens`
--
ALTER TABLE `sessions_examens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sessions_examens_code_unique` (`code`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `sessions_examens`
--
ALTER TABLE `sessions_examens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
