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
-- Structure de la table `ecs`
--

CREATE TABLE `ecs` (
  `id` bigint UNSIGNED NOT NULL,
  `ue_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `coefficient` decimal(5,2) NOT NULL DEFAULT '1.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_historical_marker` tinyint(1) NOT NULL DEFAULT '0',
  `replaced_by_ec_id` bigint UNSIGNED DEFAULT NULL,
  `historical_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ecs`
--

INSERT INTO `ecs` (`id`, `ue_id`, `code`, `nom`, `coefficient`, `is_active`, `is_historical_marker`, `replaced_by_ec_id`, `historical_comment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'EC-1', 'Introduction au droit prive', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:38', '2026-06-03 07:31:38', NULL),
(2, 1, 'EC-2', 'Introduction au droit public', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:38', '2026-06-03 07:31:38', NULL),
(3, 1, 'EC-3', 'Droit des personnes', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:38', '2026-06-03 07:31:38', NULL),
(4, 2, 'EC-1', 'Droit constitutionnel', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:39', '2026-06-03 07:31:39', NULL),
(5, 2, 'EC-2', 'Organisations internationales', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:39', '2026-06-03 07:31:39', NULL),
(6, 3, 'EC-1', 'Introduction a la recherche documentaire', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:39', '2026-06-03 07:31:39', NULL),
(7, 3, 'EC-2', 'Technique de redaction juridique', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:39', '2026-06-03 07:31:39', NULL),
(8, 3, 'EC-3', 'Francais', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:39', '2026-06-03 07:31:39', NULL),
(9, 4, 'EC-1', 'Droit de la famille', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:39', '2026-06-03 07:31:39', NULL),
(10, 4, 'EC-2', 'Relations internationales', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(11, 4, 'EC-3', 'Regimes politiques contemporains', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(12, 5, 'EC-1', 'Francais', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(13, 5, 'EC-2', 'Microeconomie', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(14, 5, 'EC-3', 'Histoire des institutions', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(15, 6, 'EC-1', 'Sciences sociales', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(16, 6, 'EC-2', 'Approches genres', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:40', '2026-06-03 07:31:40', NULL),
(17, 7, 'EC-1', 'Introduction au droit public', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:41', '2026-06-03 07:31:41', NULL),
(18, 7, 'EC-2', 'Introduction a la science politique', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:41', '2026-06-03 07:31:41', NULL),
(19, 7, 'EC-3', 'Introduction a la vie politique Malagasy', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:41', '2026-06-03 07:31:41', NULL),
(20, 8, 'EC-1', 'Droit constitutionnel', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:41', '2026-06-03 07:31:41', NULL),
(21, 8, 'EC-2', 'Organisations internationales', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:41', '2026-06-03 07:31:41', NULL),
(22, 9, 'EC-1', 'Introduction a la recherche documentaire', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:42', '2026-06-03 07:31:42', NULL),
(23, 9, 'EC-2', 'Technique de redaction juridique', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:42', '2026-06-03 07:31:42', NULL),
(24, 9, 'EC-3', 'Francais', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:42', '2026-06-03 07:31:42', NULL),
(25, 10, 'EC-1', 'Introduction a l\'analyse politique', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:42', '2026-06-03 07:31:42', NULL),
(26, 10, 'EC-2', 'Relations internationales', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:42', '2026-06-03 07:31:42', NULL),
(27, 10, 'EC-3', 'Regimes politiques contemporains', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:42', '2026-06-03 07:31:42', NULL),
(28, 11, 'EC-1', 'Francais', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:43', '2026-06-03 07:31:43', NULL),
(29, 11, 'EC-2', 'Microeconomie', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:43', '2026-06-03 07:31:43', NULL),
(30, 11, 'EC-3', 'Histoire des institutions', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:43', '2026-06-03 07:31:43', NULL),
(31, 12, 'EC-1', 'Sciences sociales', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:43', '2026-06-03 07:31:43', NULL),
(32, 12, 'EC-2', 'Approches genres', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:43', '2026-06-03 07:31:43', NULL),
(33, 13, 'EC-1', 'Droit penal general', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:43', '2026-06-03 07:31:43', NULL),
(34, 13, 'EC-2', 'Finances publiques', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:44', '2026-06-03 07:31:44', NULL),
(35, 14, 'EC-1', 'Droit des responsabilites civiles', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:44', '2026-06-03 07:31:44', NULL),
(36, 14, 'EC-2', 'Institutions administratives', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:44', '2026-06-03 07:31:44', NULL),
(37, 15, 'EC-1', 'Histoire de droit', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:44', '2026-06-03 07:31:44', NULL),
(38, 15, 'EC-2', 'Comptabilite generale', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:44', '2026-06-03 07:31:44', NULL),
(39, 15, 'EC-3', 'Francais', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:44', '2026-06-03 07:31:44', NULL),
(40, 16, 'EC-1', 'Droit des Obligations', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:45', '2026-06-03 07:31:45', NULL),
(41, 16, 'EC-2', 'Droit administratif', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:45', '2026-06-03 07:31:45', NULL),
(42, 17, 'EC-1', 'Droit international public', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:45', '2026-06-03 07:31:45', NULL),
(43, 17, 'EC-2', 'Libertes publiques', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:45', '2026-06-03 07:31:45', NULL),
(44, 17, 'EC-3', 'Macroeconomie', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:45', '2026-06-03 07:31:45', NULL),
(45, 18, 'EC-1', 'Metiers de droit et science politique', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:45', '2026-06-03 07:31:45', NULL),
(46, 18, 'EC-2', 'GENRE', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:46', '2026-06-03 07:31:46', NULL),
(47, 19, 'EC-1', 'Analyse Politique', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:46', '2026-06-03 07:31:46', NULL),
(48, 19, 'EC-2', 'Finances publiques', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:46', '2026-06-03 07:31:46', NULL),
(49, 20, 'EC-1', 'Introduction a la politique mondiale', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:46', '2026-06-03 07:31:46', NULL),
(50, 20, 'EC-2', 'Institutions administratives', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:46', '2026-06-03 07:31:46', NULL),
(51, 21, 'EC-1', 'Sociologie historique de l\'Etat', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:47', '2026-06-03 07:31:47', NULL),
(52, 21, 'EC-2', 'Comptabilite generale', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:47', '2026-06-03 07:31:47', NULL),
(53, 21, 'EC-3', 'Francais', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:47', '2026-06-03 07:31:47', NULL),
(54, 22, 'EC-1', 'Sociologie politique', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:47', '2026-06-03 07:31:47', NULL),
(55, 22, 'EC-2', 'Droit administratif', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:47', '2026-06-03 07:31:47', NULL),
(56, 23, 'EC-1', 'Droit international public', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:47', '2026-06-03 07:31:47', NULL),
(57, 23, 'EC-2', 'Libertes publiques', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(58, 23, 'EC-3', 'Macroeconomie', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(59, 24, 'EC-1', 'Metiers de droit et science politique', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(60, 24, 'EC-2', 'GENRE', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(61, 25, 'EC-1', 'Methodologie de recherches', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(62, 25, 'EC-2', 'Droit administratif des biens', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(63, 25, 'EC-3', 'Technique de redaction administrative et juridique', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:48', '2026-06-03 07:31:48', NULL),
(64, 26, 'EC-1', 'Droit commercial I', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(65, 26, 'EC-2', 'Droit des societes', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(66, 26, 'EC-3', 'Droit maritime', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(67, 27, 'EC-1', 'Procedure civile', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(68, 27, 'EC-2', 'Procedure Penale', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(69, 28, 'EC-1', 'Droit civil des biens', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(70, 28, 'EC-2', 'Droit des suretes', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(71, 28, 'EC-3', 'GENRE : Integration du genre dans les politiques', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:49', '2026-06-03 07:31:49', NULL),
(72, 29, 'EC-1', 'Droit social', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:50', '2026-06-03 07:31:50', NULL),
(73, 29, 'EC-2', 'Droit de l\'environnement', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:50', '2026-06-03 07:31:50', NULL),
(74, 29, 'EC-3', 'Droit penal special', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:50', '2026-06-03 07:31:50', NULL),
(75, 30, 'EC-1', 'Histoire politique et sociale', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:50', '2026-06-03 07:31:50', NULL),
(76, 30, 'EC-2', 'Grand oral', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:50', '2026-06-03 07:31:50', NULL),
(77, 30, 'EC-3', 'Rapport de seminaire et conference', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:50', '2026-06-03 07:31:50', NULL),
(78, 31, 'EC-1', 'Methodologie de recherches', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:51', '2026-06-03 07:31:51', NULL),
(79, 31, 'EC-2', 'Droit administratif des biens', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:51', '2026-06-03 07:31:51', NULL),
(80, 31, 'EC-3', 'Technique de redaction administrative et juridique', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:51', '2026-06-03 07:31:51', NULL),
(81, 32, 'EC-1', 'Initiation a la communication politique', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:51', '2026-06-03 07:31:51', NULL),
(82, 32, 'EC-2', 'Diplomatie', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:51', '2026-06-03 07:31:51', NULL),
(83, 32, 'EC-3', 'Politique publique generale', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:51', '2026-06-03 07:31:51', NULL),
(84, 33, 'EC-1', 'Rhetorique', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:52', '2026-06-03 07:31:52', NULL),
(85, 33, 'EC-2', 'Economie de developpement', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:52', '2026-06-03 07:31:52', NULL),
(86, 34, 'EC-1', 'Doctrine politique', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:52', '2026-06-03 07:31:52', NULL),
(87, 34, 'EC-2', 'Droit communautaire et integration regionale', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:52', '2026-06-03 07:31:52', NULL),
(88, 34, 'EC-3', 'Integration du genre dans les politiques', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:52', '2026-06-03 07:31:52', NULL),
(89, 35, 'EC-1', 'Geopolitique', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(90, 35, 'EC-2', 'Droit constitutionnel approfondi', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(91, 35, 'EC-3', 'Histoire des idees politiques', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(92, 36, 'EC-1', 'Histoire politique et sociale', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(93, 36, 'EC-2', 'Grand oral', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(94, 36, 'EC-3', 'Rapport de seminaire et conference', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(95, 37, 'EC-1', 'Droit commercial 2', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:53', '2026-06-03 07:31:53', NULL),
(96, 37, 'EC-2', 'Droit des suretes 2', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:54', '2026-06-03 07:31:54', NULL),
(97, 37, 'EC-3', 'Droit social 2', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:54', '2026-06-03 07:31:54', NULL),
(98, 38, 'EC-1', 'Droit des Societes 2', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:54', '2026-06-03 07:31:54', NULL),
(99, 38, 'EC-2', 'Droit Civil 2', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:54', '2026-06-03 07:31:54', NULL),
(100, 38, 'EC-3', 'Droit civil des biens 2', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:54', '2026-06-03 07:31:54', NULL),
(101, 39, 'EC-1', 'Voies d\'execution', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(102, 39, 'EC-2', 'Droit International Prive', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(103, 39, 'EC-3', 'Comptabilite des societes', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(104, 40, 'EC-1', 'Droit bancaire', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(105, 40, 'EC-2', 'Droit des contrats speciaux', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(106, 41, 'EC-1', 'Droit economique international', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(107, 41, 'EC-2', 'Francais', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(108, 41, 'EC-3', 'Anglais des affaires', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:55', '2026-06-03 07:31:55', NULL),
(109, 41, 'EC-4', 'Histoire Politique et sociale', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:56', '2026-06-03 07:31:56', NULL),
(110, 42, 'EC-1', 'Droit penal des affaires', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:56', '2026-06-03 07:31:56', NULL),
(111, 42, 'EC-2', 'Techniques fiscales', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:56', '2026-06-03 07:31:56', NULL),
(112, 42, 'EC-3', 'Droit des transports maritimes', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:56', '2026-06-03 07:31:56', NULL),
(113, 42, 'EC-4', 'Rapport de Voyage d\'etude', '0.25', 1, 0, NULL, NULL, '2026-06-03 07:31:56', '2026-06-03 07:31:56', NULL),
(114, 43, 'EC-1', 'Histoire politique de Madagascar', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:56', '2026-06-03 07:31:56', NULL),
(115, 43, 'EC-2', 'Analyse politique approfondie', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(116, 43, 'EC-3', 'Politique publique de l\'environnement', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(117, 44, 'EC-1', 'Philosophie Politique', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(118, 44, 'EC-2', 'FOP et Contentieux Administratif', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(119, 44, 'EC-3', 'Francais', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(120, 45, 'EC-1', 'Politique Economique', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(121, 45, 'EC-2', 'Politique Publique Contemporaine', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:57', '2026-06-03 07:31:57', NULL),
(122, 45, 'EC-3', 'Politique internationale', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:58', '2026-06-03 07:31:58', NULL),
(123, 46, 'EC-1', 'Theorie et pratique du pouvoir', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:58', '2026-06-03 07:31:58', NULL),
(124, 46, 'EC-2', 'Sociologie politique', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:58', '2026-06-03 07:31:58', NULL),
(125, 46, 'EC-3', 'Grands Problemes politiques contemporains', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:58', '2026-06-03 07:31:58', NULL),
(126, 47, 'EC-1', 'Sciences politiques comparees', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:58', '2026-06-03 07:31:58', NULL),
(127, 47, 'EC-2', 'Histoire politique et sociale', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:58', '2026-06-03 07:31:58', NULL),
(128, 48, 'EC-1', 'Droit des collectivites publiques', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:59', '2026-06-03 07:31:59', NULL),
(129, 48, 'EC-2', 'Partis politiques', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:31:59', '2026-06-03 07:31:59', NULL),
(130, 48, 'EC-3', 'Voyage d\'etudes', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:31:59', '2026-06-03 07:31:59', NULL),
(131, 49, 'EC-1', 'Procedure et Contentieux fiscaux', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:59', '2026-06-03 07:31:59', NULL),
(132, 49, 'EC-2', 'Droit du commerce international', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:31:59', '2026-06-03 07:31:59', NULL),
(133, 50, 'EC-1', 'Droit des contrats d\'affaires', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(134, 50, 'EC-2', 'Procedures collectives d\'apurement du passif', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(135, 51, 'EC-1', 'Droit de la consommation', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(136, 51, 'EC-2', 'Circulation des obligations', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(137, 51, 'EC-3', 'Francais', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(138, 52, 'EC-1', 'Droit de la concurrence', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(139, 52, 'EC-2', 'Droit des groupes de societes', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:00', '2026-06-03 07:32:00', NULL),
(140, 52, 'EC-3', 'Comptabilite des societes', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:01', '2026-06-03 07:32:01', NULL),
(141, 53, 'EC-1', 'Droit des assurances', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:01', '2026-06-03 07:32:01', NULL),
(142, 53, 'EC-2', 'Droit de la propriete intellectuelle', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:01', '2026-06-03 07:32:01', NULL),
(143, 54, 'EC-1', 'Droit douanier', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:01', '2026-06-03 07:32:01', NULL),
(144, 54, 'EC-2', 'Pratique des procedures d\'urgence devant la juridiction civile', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:01', '2026-06-03 07:32:01', NULL),
(145, 54, 'EC-3', 'Memoire de recherche', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:32:01', '2026-06-03 07:32:01', NULL),
(146, 55, 'EC-1', 'Finances locales', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:02', '2026-06-03 07:32:02', NULL),
(147, 55, 'EC-2', 'L\'election', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:02', '2026-06-03 07:32:02', NULL),
(148, 55, 'EC-3', 'Developpement local', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:02', '2026-06-03 07:32:02', NULL),
(149, 56, 'EC-1', 'Sciences administratives', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:02', '2026-06-03 07:32:02', NULL),
(150, 56, 'EC-2', 'Action internationale et cooperation territoriale des collectivites locales', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:02', '2026-06-03 07:32:02', NULL),
(151, 57, 'EC-1', 'Lettre administrative', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:02', '2026-06-03 07:32:02', NULL),
(152, 57, 'EC-2', 'Atelier de professionnalisation', '0.50', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(153, 58, 'EC-1', 'Sociologie de l\'action publique', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(154, 58, 'EC-2', 'Gouvernance et ethique', '0.35', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(155, 58, 'EC-3', 'Management public', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(156, 59, 'EC-1', 'Communication politique', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(157, 59, 'EC-2', 'Enjeux politiques et juridiques de la gestion de crise', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(158, 59, 'EC-3', 'Anglais', '0.20', 1, 0, NULL, NULL, '2026-06-03 07:32:03', '2026-06-03 07:32:03', NULL),
(159, 60, 'EC-1', 'Projet des societes', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:04', '2026-06-03 07:32:04', NULL),
(160, 60, 'EC-2', 'Le metier de l\'elu', '0.30', 1, 0, NULL, NULL, '2026-06-03 07:32:04', '2026-06-03 07:32:04', NULL),
(161, 60, 'EC-3', 'Memoire de recherche', '0.40', 1, 0, NULL, NULL, '2026-06-03 07:32:04', '2026-06-03 07:32:04', NULL),
(162, 20, 'EC-3', 'Droit des obligations', '0.50', 1, 1, 49, NULL, '2026-06-15 03:56:14', '2026-06-15 03:59:09', '2026-06-15 03:59:09'),
(163, 19, 'EC-3', 'DPG', '0.50', 1, 1, 47, NULL, '2026-06-15 04:22:12', '2026-06-15 04:23:14', '2026-06-15 04:23:14');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ecs`
--
ALTER TABLE `ecs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecs_ue_id_code_unique` (`ue_id`,`code`),
  ADD KEY `ecs_is_active_index` (`is_active`),
  ADD KEY `ecs_replaced_by_ec_id_foreign` (`replaced_by_ec_id`),
  ADD KEY `ecs_is_historical_marker_replaced_by_ec_id_index` (`is_historical_marker`,`replaced_by_ec_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ecs`
--
ALTER TABLE `ecs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ecs`
--
ALTER TABLE `ecs`
  ADD CONSTRAINT `ecs_replaced_by_ec_id_foreign` FOREIGN KEY (`replaced_by_ec_id`) REFERENCES `ecs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ecs_ue_id_foreign` FOREIGN KEY (`ue_id`) REFERENCES `ues` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
