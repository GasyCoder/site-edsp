-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : sam. 18 juil. 2026 à 12:14
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
-- Structure de la table `ues`
--

CREATE TABLE `ues` (
  `id` bigint UNSIGNED NOT NULL,
  `parcours_level_id` bigint UNSIGNED NOT NULL,
  `semestre_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credits` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ues`
--

INSERT INTO `ues` (`id`, `parcours_level_id`, `semestre_id`, `code`, `nom`, `credits`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'UE-1-d', 'Initiation au droit', NULL, 1, '2026-06-03 07:31:38', '2026-06-08 04:42:13', NULL),
(2, 1, 1, 'UE-2-d', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:39', '2026-06-08 04:42:13', NULL),
(3, 1, 1, 'UE-3-d', 'Methodologie de travail universitaire', NULL, 1, '2026-06-03 07:31:39', '2026-06-08 04:42:13', NULL),
(4, 1, 2, 'UE-4-d', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:39', '2026-06-08 04:42:13', NULL),
(5, 1, 2, 'UE-5-d', 'Enseignements d\'ouverture', NULL, 1, '2026-06-03 07:31:40', '2026-06-08 04:42:13', NULL),
(6, 1, 2, 'UE-6-d', 'Enseignements complementaires', NULL, 1, '2026-06-03 07:31:40', '2026-06-08 04:42:13', NULL),
(7, 6, 1, 'UE-1-sp', 'Enseignements introductifs a la science politique', NULL, 1, '2026-06-03 07:31:41', '2026-06-08 04:42:13', NULL),
(8, 6, 1, 'UE-2-sp', 'Enseignements fondamentaux I', NULL, 1, '2026-06-03 07:31:41', '2026-06-08 04:42:13', NULL),
(9, 6, 1, 'UE-3-sp', 'Methodologie de travail universitaire', NULL, 1, '2026-06-03 07:31:41', '2026-06-08 04:42:13', NULL),
(10, 6, 2, 'UE-4-sp', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:42', '2026-06-08 04:42:13', NULL),
(11, 6, 2, 'UE-5-sp', 'Enseignements d\'ouverture', NULL, 1, '2026-06-03 07:31:42', '2026-06-08 04:42:13', NULL),
(12, 6, 2, 'UE-6-sp', 'Enseignements complementaires', NULL, 1, '2026-06-03 07:31:43', '2026-06-08 04:42:13', NULL),
(13, 2, 3, 'UE-7-d', 'Enseignements complementaires II', NULL, 1, '2026-06-03 07:31:43', '2026-06-08 04:42:13', NULL),
(14, 2, 3, 'UE-8-d', 'Renforcement des savoirs fondamentaux I', NULL, 1, '2026-06-03 07:31:44', '2026-06-08 04:42:13', NULL),
(15, 2, 3, 'UE-9-d', 'Enseignement pluridisciplinaire', NULL, 1, '2026-06-03 07:31:44', '2026-06-08 04:42:13', NULL),
(16, 2, 4, 'UE-10-d', 'Renforcement des savoirs fondamentaux II', NULL, 1, '2026-06-03 07:31:45', '2026-06-08 04:42:13', NULL),
(17, 2, 4, 'UE-11-d', 'Enseignement d\'ouverture', NULL, 1, '2026-06-03 07:31:45', '2026-06-08 04:42:13', NULL),
(18, 2, 4, 'UE-12-d', 'Unite de decouverte professionnelle', NULL, 1, '2026-06-03 07:31:45', '2026-06-08 04:42:13', NULL),
(19, 7, 3, 'UE-7-sp', 'Enseignements complementaires', NULL, 1, '2026-06-03 07:31:46', '2026-06-08 04:42:13', NULL),
(20, 7, 3, 'UE-8-sp', 'Renforcement des savoirs fondamentaux I', NULL, 1, '2026-06-03 07:31:46', '2026-06-08 04:42:13', NULL),
(21, 7, 3, 'UE-9-sp', 'Enseignement pluridisciplinaire', NULL, 1, '2026-06-03 07:31:46', '2026-06-08 04:42:13', NULL),
(22, 7, 4, 'UE-10-sp', 'Renforcement des savoirs fondamentaux II', NULL, 1, '2026-06-03 07:31:47', '2026-06-08 04:42:13', NULL),
(23, 7, 4, 'UE-11-sp', 'Enseignement d\'ouverture', NULL, 1, '2026-06-03 07:31:47', '2026-06-08 04:42:13', NULL),
(24, 7, 4, 'UE-12-sp', 'Unite de decouverte professionnelle', NULL, 1, '2026-06-03 07:31:48', '2026-06-08 04:42:13', NULL),
(25, 3, 5, 'UE-13-d', 'Methodologie du travail universitaire', NULL, 1, '2026-06-03 07:31:48', '2026-06-08 04:42:13', NULL),
(26, 3, 5, 'UE-14-d', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:48', '2026-06-08 04:42:13', NULL),
(27, 3, 5, 'UE-15-d', 'Droit processuel', NULL, 1, '2026-06-03 07:31:49', '2026-06-08 04:42:13', NULL),
(28, 3, 6, 'UE-16-d', 'Droit prive de base', NULL, 1, '2026-06-03 07:31:49', '2026-06-08 04:42:13', NULL),
(29, 3, 6, 'UE-17-d', 'Enseignements complementaires', NULL, 1, '2026-06-03 07:31:50', '2026-06-08 04:42:13', NULL),
(30, 3, 6, 'UE-18-d', 'Enseignement de culture generale et de professionnalisation', NULL, 1, '2026-06-03 07:31:50', '2026-06-08 04:42:13', NULL),
(31, 8, 5, 'UE-13-sp', 'Methodologie de travail universitaire', NULL, 1, '2026-06-03 07:31:51', '2026-06-08 04:42:13', NULL),
(32, 8, 5, 'UE-14-sp', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:51', '2026-06-08 04:42:13', NULL),
(33, 8, 5, 'UE-15-sp', 'Communication', NULL, 1, '2026-06-03 07:31:52', '2026-06-08 04:42:13', NULL),
(34, 8, 6, 'UE-16-sp', 'Enseignements politiques complementaires', NULL, 1, '2026-06-03 07:31:52', '2026-06-08 04:42:13', NULL),
(35, 8, 6, 'UE-17-sp', 'Droit public de base', NULL, 1, '2026-06-03 07:31:52', '2026-06-08 04:42:13', NULL),
(36, 8, 6, 'UE-18-sp', 'Enseignement de culture generale et de professionnalisation', NULL, 1, '2026-06-03 07:31:53', '2026-06-08 04:42:13', NULL),
(37, 4, 7, 'UE-19-d', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:53', '2026-06-08 04:42:13', NULL),
(38, 4, 7, 'UE-20-d', 'Enseignements complementaires I', NULL, 1, '2026-06-03 07:31:54', '2026-06-08 04:42:13', NULL),
(39, 4, 7, 'UE-21-d', 'Enseignements d\'ouverture', NULL, 1, '2026-06-03 07:31:54', '2026-06-08 04:42:13', NULL),
(40, 4, 8, 'UE-22-d', 'Enseignements fondamentaux II', NULL, 1, '2026-06-03 07:31:55', '2026-06-08 04:42:13', NULL),
(41, 4, 8, 'UE-23-d', 'Enseignements d\'ouverture II', NULL, 1, '2026-06-03 07:31:55', '2026-06-08 04:42:13', NULL),
(42, 4, 8, 'UE-24-d', 'Enseignements complementaires II', NULL, 1, '2026-06-03 07:31:56', '2026-06-08 04:42:13', NULL),
(43, 9, 7, 'UE-19-sp', 'Enseignements fondamentaux', NULL, 1, '2026-06-03 07:31:56', '2026-06-08 04:42:13', NULL),
(44, 9, 7, 'UE-20-sp', 'Enseignements d\'approfondissement', NULL, 1, '2026-06-03 07:31:57', '2026-06-08 04:42:13', NULL),
(45, 9, 7, 'UE-21-sp', 'Enseignements d\'outils', NULL, 1, '2026-06-03 07:31:57', '2026-06-08 04:42:13', NULL),
(46, 9, 8, 'UE-22-sp', 'Enseignements fondamentaux II', NULL, 1, '2026-06-03 07:31:58', '2026-06-08 04:42:13', NULL),
(47, 9, 8, 'UE-23-sp', 'Enseignements d\'approfondissements II', NULL, 1, '2026-06-03 07:31:58', '2026-06-08 04:42:13', NULL),
(48, 9, 8, 'UE-24-sp', 'Enseignements d\'outils II', NULL, 1, '2026-06-03 07:31:59', '2026-06-08 04:42:13', NULL),
(49, 5, 9, 'UE-25-d', 'Renforcement des savoirs I', NULL, 1, '2026-06-03 07:31:59', '2026-06-08 04:42:13', NULL),
(50, 5, 9, 'UE-26-d', 'Enseignements complementaires', NULL, 1, '2026-06-03 07:31:59', '2026-06-08 04:42:13', NULL),
(51, 5, 9, 'UE-27-d', 'Enseignements d\'ouverture', NULL, 1, '2026-06-03 07:32:00', '2026-06-08 04:42:13', NULL),
(52, 5, 10, 'UE-28-d', 'Renforcement de savoir II', NULL, 1, '2026-06-03 07:32:00', '2026-06-08 04:42:13', NULL),
(53, 5, 10, 'UE-29-d', 'Enseignement complementaire II', NULL, 1, '2026-06-03 07:32:01', '2026-06-08 04:42:13', NULL),
(54, 5, 10, 'UE-30-d', 'Enseignement professionnel et de recherche', NULL, 1, '2026-06-03 07:32:01', '2026-06-08 04:42:13', NULL),
(55, 10, 9, 'UE-25-sp', 'Renforcement des savoirs I', NULL, 1, '2026-06-03 07:32:01', '2026-06-08 04:42:13', NULL),
(56, 10, 9, 'UE-26-sp', 'Professionnalisation', NULL, 1, '2026-06-03 07:32:02', '2026-06-08 04:42:13', NULL),
(57, 10, 9, 'UE-27-sp', 'Enseignements d\'ouverture', NULL, 1, '2026-06-03 07:32:02', '2026-06-08 04:42:13', NULL),
(58, 10, 10, 'UE-28-sp', 'Renforcement des savoirs II', NULL, 1, '2026-06-03 07:32:03', '2026-06-08 04:42:13', NULL),
(59, 10, 10, 'UE-29-sp', 'Professionnalisation II', NULL, 1, '2026-06-03 07:32:03', '2026-06-08 04:42:13', NULL),
(60, 10, 10, 'UE-30-sp', 'Enseignement d\'ouverture II', NULL, 1, '2026-06-03 07:32:04', '2026-06-08 04:42:13', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ues`
--
ALTER TABLE `ues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ues_parcours_semestre_code_unique` (`parcours_level_id`,`semestre_id`,`code`),
  ADD KEY `ues_semestre_id_foreign` (`semestre_id`),
  ADD KEY `ues_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ues`
--
ALTER TABLE `ues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ues`
--
ALTER TABLE `ues`
  ADD CONSTRAINT `ues_parcours_level_id_foreign` FOREIGN KEY (`parcours_level_id`) REFERENCES `parcours_levels` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `ues_semestre_id_foreign` FOREIGN KEY (`semestre_id`) REFERENCES `semestres` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
