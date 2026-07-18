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
-- Structure de la table `parcours`
--

CREATE TABLE `parcours` (
  `id` bigint UNSIGNED NOT NULL,
  `mention_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parcours`
--

INSERT INTO `parcours` (`id`, `mention_id`, `code`, `nom`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'DROI', 'Droit', 'Parcours Droit pour les niveaux L1 et L2.', '2026-06-03 07:31:35', '2026-06-03 07:31:35', NULL),
(2, 1, 'DPRI', 'Droit Privé', 'Parcours Droit Privé pour le niveau L3.', '2026-06-03 07:31:35', '2026-06-03 07:31:35', NULL),
(3, 1, 'DAFF', 'Droit des Affaires', 'Parcours Droit des Affaires pour les niveaux M1 et M2.', '2026-06-03 07:31:35', '2026-06-03 07:31:35', NULL),
(4, 2, 'SCPO', 'Science Politique', 'Parcours Science Politique pour les niveaux L1, L2 et L3.', '2026-06-03 07:31:35', '2026-06-03 07:31:35', NULL),
(5, 2, 'ETPO', 'Études Politiques', 'Parcours Études Politiques pour les niveaux M1 et M2.', '2026-06-03 07:31:35', '2026-06-03 07:31:35', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `parcours`
--
ALTER TABLE `parcours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parcours_code_unique` (`code`),
  ADD KEY `parcours_mention_id_foreign` (`mention_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `parcours`
--
ALTER TABLE `parcours`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `parcours`
--
ALTER TABLE `parcours`
  ADD CONSTRAINT `parcours_mention_id_foreign` FOREIGN KEY (`mention_id`) REFERENCES `mentions` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
