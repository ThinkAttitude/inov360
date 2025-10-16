-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Tempo de geração: 16-Out-2025 às 10:34
-- Versão do servidor: 5.7.44
-- versão do PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `inov360`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `colaborador_dados`
--

CREATE TABLE `colaborador_dados` (
  `user_id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `freguesia` varchar(100) DEFAULT NULL,
  `concelho` varchar(100) DEFAULT NULL,
  `distrito` varchar(100) DEFAULT NULL,
  `naturalidade` varchar(100) DEFAULT NULL,
  `habilitacoes` varchar(100) DEFAULT NULL,
  `pai` varchar(100) DEFAULT NULL,
  `mae` varchar(100) DEFAULT NULL,
  `estado_civil` enum('Solteiro','Casado','Viuvo','Divorciado','Uniao de Facto','Separado Judicialmente') DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `tipo_documento` enum('CC','Titulo Residencia','Passaporte') DEFAULT NULL,
  `numero_documento` varchar(50) DEFAULT NULL,
  `emitido_em` varchar(100) DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `validade_documento` date DEFAULT NULL,
  `nif` varchar(20) DEFAULT NULL,
  `numero_seg_social` varchar(20) DEFAULT NULL,
  `descontos_fiscais` varchar(100) DEFAULT NULL,
  `reparticao_financas` varchar(100) DEFAULT NULL,
  `regiao` varchar(100) DEFAULT NULL,
  `estado_fiscal` enum('Nao Casado','Casado 1 Titular','Casado 2 Titulares') DEFAULT NULL,
  `deficiencia` enum('Nao Deficiente','Deficiente','Defic. F.Armadas') DEFAULT NULL,
  `conjugue_deficiente` tinyint(1) DEFAULT NULL,
  `num_dependentes` int(11) DEFAULT NULL,
  `num_dependentes_deficientes` int(11) DEFAULT NULL,
  `pensionista` tinyint(1) DEFAULT NULL,
  `data_admissao` date DEFAULT NULL,
  `tipo_contrato` varchar(50) DEFAULT NULL,
  `profissao` varchar(100) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `regime` enum('Tempo Inteiro','Tempo Parcial') DEFAULT NULL,
  `horas_semana` int(11) DEFAULT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `subsidio_alimentacao` decimal(10,2) DEFAULT NULL,
  `nib` varchar(50) DEFAULT NULL,
  `ordenado_liquido` decimal(10,2) DEFAULT NULL,
  `validacao_empresa` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `colaborador_dados`
--

INSERT INTO `colaborador_dados` (`user_id`, `nome`, `email`, `telefone`, `morada`, `codigo_postal`, `freguesia`, `concelho`, `distrito`, `naturalidade`, `habilitacoes`, `pai`, `mae`, `estado_civil`, `data_nascimento`, `pais`, `tipo_documento`, `numero_documento`, `emitido_em`, `arquivo`, `validade_documento`, `nif`, `numero_seg_social`, `descontos_fiscais`, `reparticao_financas`, `regiao`, `estado_fiscal`, `deficiencia`, `conjugue_deficiente`, `num_dependentes`, `num_dependentes_deficientes`, `pensionista`, `data_admissao`, `tipo_contrato`, `profissao`, `categoria`, `regime`, `horas_semana`, `salario_base`, `subsidio_alimentacao`, `nib`, `ordenado_liquido`, `validacao_empresa`) VALUES
(12, 'Miguel Administrador RH', 'adminrh@gmail.com', '961234567', 'Rua Do Moinho Casais De Sao Lourenco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Solteiro', NULL, NULL, 'CC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Nao Casado', 'Nao Deficiente', 1, NULL, NULL, 1, NULL, 'Sem termo', NULL, NULL, 'Tempo Inteiro', NULL, 8000.00, NULL, '000201231234567890154', NULL, NULL),
(21, 'Miguel Operador', 'oper@gmail.com', '963206692', 'Rua Do Moinho Casais De Sao Lourenco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Solteiro', NULL, NULL, 'CC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Nao Casado', 'Nao Deficiente', 1, NULL, NULL, 1, NULL, 'Sem termo', NULL, 'Categoria A', 'Tempo Inteiro', NULL, NULL, NULL, '000201231234567890154', NULL, NULL),
(22, 'Miguel Intermedio2', 'inter2@gmail.com', '960009827', 'Rua Do Moinho Casais De Sao Lourenco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '000201231234567890154', NULL, NULL),
(23, 'Miguel Intermedio', 'inter@gmail.com', '960009827', 'Rua Do Moinho Casais De Sao Lourenco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Solteiro', NULL, NULL, 'CC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Nao Casado', 'Nao Deficiente', 1, NULL, NULL, 1, NULL, 'Sem termo', NULL, NULL, 'Tempo Inteiro', NULL, 800000.00, NULL, '000200123456789012345', NULL, NULL),
(24, 'Miguel Admin', 'admin@gmail.com', '960009827', 'Rua do Carmo Lopes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '000201231234567890154', NULL, NULL),
(25, 'Miguel Estrela', 'estrela@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'João Operador', 'joao@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'Luís Financeiro', 'finan@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'Sandra Ferreira', 'sandraferreira@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `colaborador_edicoes`
--

CREATE TABLE `colaborador_edicoes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `estado` enum('pendente','aprovado','recusado') DEFAULT 'pendente',
  `avaliado_por` int(11) DEFAULT NULL,
  `avaliado_em` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `morada` varchar(255) DEFAULT NULL,
  `nib` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `colaborador_edicoes`
--

INSERT INTO `colaborador_edicoes` (`id`, `user_id`, `email`, `telefone`, `estado`, `avaliado_por`, `avaliado_em`, `criado_em`, `morada`, `nib`) VALUES
(1, 21, 'oper@gmail.com', '963206692', 'aprovado', 12, '2025-09-10 19:00:51', '2025-09-10 18:50:28', 'Rua Do Moinho Casais De Sao Lourenco', '000201231234567890154'),
(2, 21, 'oper@gmail.com', '963206692', 'aprovado', 12, '2025-09-11 11:20:06', '2025-09-11 11:18:48', 'Rua Do Moinho Casais De Sao Lourenco', '000201231234567890154');

-- --------------------------------------------------------

--
-- Estrutura da tabela `colaborador_responsaveis`
--

CREATE TABLE `colaborador_responsaveis` (
  `id` int(11) NOT NULL,
  `colaborador_id` int(11) NOT NULL,
  `responsavel_id` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `valido_desde` datetime DEFAULT NULL,
  `valido_ate` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `company`
--

CREATE TABLE `company` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `company`
--

INSERT INTO `company` (`id`, `name`, `slug`, `logo_path`, `active`, `created_at`) VALUES
(1, 'Inovbuild', 'inovbuild', '/assets/logos/inov.png', 1, '2025-08-18 10:39:58'),
(2, 'Ferrobuild', 'ferrobuild', '/assets/logos/ferro.png', 1, '2025-08-18 10:39:58'),
(3, 'WoodBuild', 'woodbuild', '/assets/logos/wood.png', 1, '2025-08-18 10:39:58'),
(4, 'AutoBuild', 'autobuild', '/assets/logos/auto.png', 1, '2025-08-18 10:39:58'),
(5, 'FitOut', 'fitout', '/assets/logos/fit.png', 1, '2025-08-18 10:39:58'),
(6, 'StratGate', 'stratgate', '/assets/logos/strat.png', 1, '2025-08-18 10:39:58'),
(7, 'Almalusa', 'almalusa', '/assets/logos/alma.png', 1, '2025-08-18 10:39:58'),
(8, 'Drawline I', 'drawline-i', '/assets/logos/draw1.png', 1, '2025-08-18 10:39:58'),
(9, 'Drawline II', 'drawline-ii', '/assets/logos/draw2.png', 1, '2025-08-18 10:39:58'),
(10, 'Think Attitude', 'think-attitude', '/assets/logos/think.png', 1, '2025-08-18 10:39:58'),
(11, 'GrupoInov', 'grupoinov', '/assets/logos/grupoinov.png', 1, '2025-08-25 10:46:50');

-- --------------------------------------------------------

--
-- Estrutura da tabela `contactos_emergencia`
--

CREATE TABLE `contactos_emergencia` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parentesco` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `contactos_emergencia`
--

INSERT INTO `contactos_emergencia` (`id`, `user_id`, `nome`, `parentesco`, `telefone`) VALUES
(1, 40, '', '', ''),
(3, 41, '', '', ''),
(4, 21, 'Joao Lopes', 'Pai', '987773663');

-- --------------------------------------------------------

--
-- Estrutura da tabela `contactos_emergencia_edicoes`
--

CREATE TABLE `contactos_emergencia_edicoes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `estado` enum('pendente','aprovado','recusado') DEFAULT 'pendente',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `avaliado_por` int(11) DEFAULT NULL,
  `avaliado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `contactos_emergencia_edicoes`
--

INSERT INTO `contactos_emergencia_edicoes` (`id`, `user_id`, `nome`, `parentesco`, `telefone`, `estado`, `criado_em`, `avaliado_por`, `avaliado_em`) VALUES
(1, 21, 'Miguel Cordeiro', 'Pai', '987773663', 'aprovado', '2025-09-10 18:50:28', 12, '2025-09-10 19:00:51'),
(2, 21, 'Joao Lopes', 'Pai', '987773663', 'aprovado', '2025-09-11 11:18:48', 12, '2025-09-11 11:20:06');

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime NOT NULL,
  `minutos` int(11) DEFAULT NULL,
  `km` decimal(8,2) DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected','locked') NOT NULL DEFAULT 'draft',
  `source` enum('manual','approval','import','system') NOT NULL DEFAULT 'manual',
  `leave_request_id` int(11) DEFAULT NULL,
  `period_id` int(11) DEFAULT NULL,
  `tipo` enum('WORK','OVERTIME','ONCALL','KM','LEAVE','SUBSTITUTION') NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dia` date GENERATED ALWAYS AS (cast(`inicio` as date)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `inicio`, `fim`, `minutos`, `km`, `status`, `source`, `leave_request_id`, `period_id`, `tipo`, `user_id`, `created_at`, `updated_at`) VALUES
(1156, 'WORK', '2025-09-01 00:00:00', '2025-09-01 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1157, 'WORK', '2025-09-02 00:00:00', '2025-09-02 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1158, 'WORK', '2025-09-03 00:00:00', '2025-09-03 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1159, 'WORK', '2025-09-04 00:00:00', '2025-09-04 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1160, 'WORK', '2025-09-05 00:00:00', '2025-09-05 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1161, 'WORK', '2025-09-06 00:00:00', '2025-09-06 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1162, 'WORK', '2025-09-07 00:00:00', '2025-09-07 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1163, 'WORK', '2025-09-08 00:00:00', '2025-09-08 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1164, 'WORK', '2025-09-09 00:00:00', '2025-09-09 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1165, 'WORK', '2025-09-10 00:00:00', '2025-09-10 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1166, 'WORK', '2025-09-11 00:00:00', '2025-09-11 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1167, 'WORK', '2025-09-12 00:00:00', '2025-09-12 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1168, 'WORK', '2025-09-13 00:00:00', '2025-09-13 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1169, 'WORK', '2025-09-14 00:00:00', '2025-09-14 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1170, 'WORK', '2025-09-15 00:00:00', '2025-09-15 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1171, 'WORK', '2025-09-16 00:00:00', '2025-09-16 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1172, 'WORK', '2025-09-17 00:00:00', '2025-09-17 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1173, 'WORK', '2025-09-18 00:00:00', '2025-09-18 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1174, 'WORK', '2025-09-19 00:00:00', '2025-09-19 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1175, 'WORK', '2025-09-20 00:00:00', '2025-09-20 23:59:59', 0, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1176, 'WORK', '2025-09-21 00:00:00', '2025-09-21 23:59:59', 0, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1177, 'WORK', '2025-09-22 00:00:00', '2025-09-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1178, 'WORK', '2025-09-23 00:00:00', '2025-09-23 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1179, 'WORK', '2025-09-24 00:00:00', '2025-09-24 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1180, 'WORK', '2025-09-25 00:00:00', '2025-09-25 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1181, 'WORK', '2025-09-26 00:00:00', '2025-09-26 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1182, 'WORK', '2025-09-27 00:00:00', '2025-09-27 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1183, 'WORK', '2025-09-28 00:00:00', '2025-09-28 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1184, 'WORK', '2025-09-29 00:00:00', '2025-09-29 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1185, 'WORK', '2025-09-30 00:00:00', '2025-09-30 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'WORK', 21, '2025-09-10 18:37:23', '2025-09-10 19:47:48'),
(1217, 'OVERTIME', '2025-09-18 00:00:00', '2025-09-18 23:59:59', 240, NULL, 'approved', 'approval', NULL, 9, 'OVERTIME', 21, '2025-09-10 18:46:37', '2025-09-10 19:47:48'),
(1218, 'ONCALL', '2025-09-18 00:00:00', '2025-09-18 23:59:59', 120, NULL, 'approved', 'approval', NULL, 9, 'ONCALL', 21, '2025-09-10 18:46:37', '2025-09-10 19:47:48'),
(1220, 'OVERTIME', '2025-09-24 00:00:00', '2025-09-24 23:59:59', 0, NULL, 'approved', 'approval', NULL, 9, 'OVERTIME', 21, '2025-09-10 18:46:47', '2025-09-10 19:47:48'),
(1221, 'ONCALL', '2025-09-24 00:00:00', '2025-09-24 23:59:59', 120, NULL, 'approved', 'approval', NULL, 9, 'ONCALL', 21, '2025-09-10 18:46:47', '2025-09-10 19:47:48'),
(1222, 'KM', '2025-09-01 00:00:00', '2025-09-01 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1223, 'KM', '2025-09-02 00:00:00', '2025-09-02 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1224, 'KM', '2025-09-03 00:00:00', '2025-09-03 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1225, 'KM', '2025-09-04 00:00:00', '2025-09-04 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1226, 'KM', '2025-09-05 00:00:00', '2025-09-05 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1227, 'KM', '2025-09-06 00:00:00', '2025-09-06 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1228, 'KM', '2025-09-07 00:00:00', '2025-09-07 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1229, 'KM', '2025-09-08 00:00:00', '2025-09-08 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1230, 'KM', '2025-09-09 00:00:00', '2025-09-09 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1231, 'KM', '2025-09-10 00:00:00', '2025-09-10 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1232, 'KM', '2025-09-11 00:00:00', '2025-09-11 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1233, 'KM', '2025-09-12 00:00:00', '2025-09-12 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1234, 'KM', '2025-09-13 00:00:00', '2025-09-13 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1235, 'KM', '2025-09-14 00:00:00', '2025-09-14 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1236, 'KM', '2025-09-15 00:00:00', '2025-09-15 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1237, 'KM', '2025-09-16 00:00:00', '2025-09-16 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1238, 'KM', '2025-09-17 00:00:00', '2025-09-17 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1239, 'KM', '2025-09-18 00:00:00', '2025-09-18 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1240, 'KM', '2025-09-19 00:00:00', '2025-09-19 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1241, 'KM', '2025-09-20 00:00:00', '2025-09-20 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1242, 'KM', '2025-09-21 00:00:00', '2025-09-21 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1243, 'KM', '2025-09-22 00:00:00', '2025-09-22 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1244, 'KM', '2025-09-23 00:00:00', '2025-09-23 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1245, 'KM', '2025-09-24 00:00:00', '2025-09-24 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1246, 'KM', '2025-09-25 00:00:00', '2025-09-25 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1247, 'KM', '2025-09-26 00:00:00', '2025-09-26 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1248, 'KM', '2025-09-27 00:00:00', '2025-09-27 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1249, 'KM', '2025-09-28 00:00:00', '2025-09-28 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1250, 'KM', '2025-09-29 00:00:00', '2025-09-29 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1251, 'KM', '2025-09-30 00:00:00', '2025-09-30 23:59:59', NULL, 40.00, 'approved', 'approval', NULL, 9, 'KM', 21, '2025-09-10 18:47:19', '2025-09-10 19:47:48'),
(1282, 'baixa_medica', '2025-09-20 00:00:00', '2025-09-22 23:59:59', NULL, NULL, 'approved', 'approval', 11, NULL, 'LEAVE', 21, '2025-09-10 18:50:55', '2025-09-10 18:50:55'),
(1283, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1284, 'OVERTIME', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1285, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1286, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1287, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1288, 'OVERTIME', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1289, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1290, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1291, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1292, 'OVERTIME', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1293, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1294, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1295, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1296, 'OVERTIME', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1297, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1298, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1299, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1300, 'OVERTIME', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1301, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1302, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1303, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1304, 'OVERTIME', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1305, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1306, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1307, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1308, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1309, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1310, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1311, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1312, 'OVERTIME', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1313, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1314, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1315, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1316, 'OVERTIME', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1317, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1318, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1319, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1320, 'OVERTIME', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1321, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1322, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1323, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1324, 'OVERTIME', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1325, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1326, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1327, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1328, 'OVERTIME', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1329, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1330, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1331, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1332, 'OVERTIME', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1333, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1334, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1335, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1336, 'OVERTIME', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1337, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1338, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1339, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1340, 'OVERTIME', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1341, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1342, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1343, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1344, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1345, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1346, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1347, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1348, 'OVERTIME', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1349, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1350, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1351, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1352, 'OVERTIME', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1353, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1354, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1355, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1356, 'OVERTIME', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1357, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1358, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1359, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1360, 'OVERTIME', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1361, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1362, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1363, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1364, 'OVERTIME', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1365, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1366, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1367, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1368, 'OVERTIME', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1369, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1370, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1371, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 240, NULL, 'approved', 'approval', NULL, 10, 'WORK', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1372, 'OVERTIME', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 100, NULL, 'approved', 'approval', NULL, 10, 'OVERTIME', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1373, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 60, NULL, 'approved', 'approval', NULL, 10, 'ONCALL', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1374, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 10, 'KM', 41, '2025-09-10 19:35:51', '2025-09-10 19:47:54'),
(1375, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1376, 'OVERTIME', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1377, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1378, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1379, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1380, 'OVERTIME', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1381, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1382, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1383, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1384, 'OVERTIME', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1385, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1386, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1387, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1388, 'OVERTIME', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1389, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1390, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1391, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1392, 'OVERTIME', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1393, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1394, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1395, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1396, 'OVERTIME', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1397, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1398, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1399, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1400, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1401, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1402, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1403, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1404, 'OVERTIME', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1405, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1406, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1407, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1408, 'OVERTIME', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1409, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1410, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1411, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1412, 'OVERTIME', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1413, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1414, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1415, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1416, 'OVERTIME', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1417, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1418, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1419, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1420, 'OVERTIME', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1421, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1422, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1423, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1424, 'OVERTIME', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1425, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1426, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1427, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1428, 'OVERTIME', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1429, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1430, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1431, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1432, 'OVERTIME', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1433, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1434, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1435, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1436, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1437, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1438, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1439, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1440, 'OVERTIME', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1441, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1442, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1443, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1444, 'OVERTIME', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1445, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1446, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1447, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1448, 'OVERTIME', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1449, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1450, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1451, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1452, 'OVERTIME', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1453, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1454, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1455, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1456, 'OVERTIME', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1457, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1458, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1459, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1460, 'OVERTIME', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1461, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1462, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1463, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'approved', 'approval', NULL, 11, 'WORK', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1464, 'OVERTIME', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'OVERTIME', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1465, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 60, NULL, 'approved', 'approval', NULL, 11, 'ONCALL', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1466, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 11, 'KM', 52, '2025-09-10 19:41:56', '2025-09-10 19:47:57'),
(1467, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1468, 'OVERTIME', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1469, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1470, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1471, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1472, 'OVERTIME', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1473, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1474, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1475, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1476, 'OVERTIME', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1477, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1478, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1479, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1480, 'OVERTIME', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1481, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1482, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1483, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1484, 'OVERTIME', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1485, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1486, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1487, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1488, 'OVERTIME', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1489, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1490, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1491, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1492, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1493, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1494, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1495, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1496, 'OVERTIME', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1497, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1498, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1499, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1500, 'OVERTIME', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1501, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1502, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1503, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1504, 'OVERTIME', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1505, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1506, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1507, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1508, 'OVERTIME', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1509, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1510, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1511, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1512, 'OVERTIME', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1513, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1514, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1515, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1516, 'OVERTIME', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1517, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1518, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1519, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1520, 'OVERTIME', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01');
INSERT INTO `eventos` (`id`, `titulo`, `inicio`, `fim`, `minutos`, `km`, `status`, `source`, `leave_request_id`, `period_id`, `tipo`, `user_id`, `created_at`, `updated_at`) VALUES
(1521, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1522, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1523, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1524, 'OVERTIME', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1525, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1526, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1527, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1528, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1529, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1530, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1531, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1532, 'OVERTIME', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1533, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1534, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1535, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1536, 'OVERTIME', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1537, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1538, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1539, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1540, 'OVERTIME', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1541, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1542, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1543, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1544, 'OVERTIME', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1545, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1546, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1547, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1548, 'OVERTIME', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1549, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1550, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1551, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1552, 'OVERTIME', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1553, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1554, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1555, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'approved', 'approval', NULL, 12, 'WORK', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1556, 'OVERTIME', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 0, NULL, 'approved', 'approval', NULL, 12, 'OVERTIME', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1557, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 60, NULL, 'approved', 'approval', NULL, 12, 'ONCALL', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1558, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 30.00, 'approved', 'approval', NULL, 12, 'KM', 53, '2025-09-10 19:42:38', '2025-09-10 19:48:01'),
(1559, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1560, 'OVERTIME', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1561, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1562, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1563, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1564, 'OVERTIME', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1565, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1566, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1567, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1568, 'OVERTIME', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1569, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1570, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1571, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1572, 'OVERTIME', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1573, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1574, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1575, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1576, 'OVERTIME', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1577, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1578, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1579, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1580, 'OVERTIME', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1581, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1582, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1583, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1584, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1585, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1586, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1587, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1588, 'OVERTIME', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1589, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1590, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1591, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1592, 'OVERTIME', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1593, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1594, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1595, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1596, 'OVERTIME', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1597, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1598, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1599, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1600, 'OVERTIME', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1601, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1602, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1603, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1604, 'OVERTIME', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1605, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1606, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1607, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1608, 'OVERTIME', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1609, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1610, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1611, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1612, 'OVERTIME', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1613, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1614, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1615, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1616, 'OVERTIME', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1617, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1618, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1619, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1620, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1621, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1622, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1623, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1624, 'OVERTIME', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1625, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1626, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1627, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1628, 'OVERTIME', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1629, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1630, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1631, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1632, 'OVERTIME', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1633, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1634, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1635, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1636, 'OVERTIME', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1637, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1638, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1639, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1640, 'OVERTIME', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1641, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1642, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1643, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1644, 'OVERTIME', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1645, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1646, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1647, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'approved', 'approval', NULL, 13, 'WORK', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1648, 'OVERTIME', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 0, NULL, 'approved', 'approval', NULL, 13, 'OVERTIME', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1649, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 60, NULL, 'approved', 'approval', NULL, 13, 'ONCALL', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1650, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 13, 'KM', 54, '2025-09-10 19:43:30', '2025-09-10 19:50:02'),
(1651, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1652, 'OVERTIME', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1653, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1654, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1655, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1656, 'OVERTIME', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1657, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1658, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1659, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1660, 'OVERTIME', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1661, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1662, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1663, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1664, 'OVERTIME', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1665, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1666, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1667, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1668, 'OVERTIME', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1669, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1670, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1671, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1672, 'OVERTIME', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1673, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1674, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1675, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1676, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1677, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1678, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1679, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1680, 'OVERTIME', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1681, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1682, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1683, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1684, 'OVERTIME', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1685, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1686, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1687, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1688, 'OVERTIME', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1689, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1690, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1691, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1692, 'OVERTIME', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1693, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1694, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1695, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1696, 'OVERTIME', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1697, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1698, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1699, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1700, 'OVERTIME', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1701, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1702, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1703, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1704, 'OVERTIME', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1705, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1706, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1707, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1708, 'OVERTIME', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1709, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1710, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1711, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1712, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1713, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1714, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1715, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1716, 'OVERTIME', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1717, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1718, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1719, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1720, 'OVERTIME', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1721, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1722, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1723, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1724, 'OVERTIME', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1725, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1726, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1727, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1728, 'OVERTIME', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1729, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1730, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1731, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1732, 'OVERTIME', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1733, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1734, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1735, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1736, 'OVERTIME', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1737, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1738, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1739, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 240, NULL, 'approved', 'approval', NULL, 14, 'WORK', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1740, 'OVERTIME', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 0, NULL, 'approved', 'approval', NULL, 14, 'OVERTIME', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1741, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 60, NULL, 'approved', 'approval', NULL, 14, 'ONCALL', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1742, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 14, 'KM', 55, '2025-09-10 19:44:40', '2025-09-10 19:50:31'),
(1743, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1744, 'OVERTIME', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1745, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1746, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1747, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1748, 'OVERTIME', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1749, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1750, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1751, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1752, 'OVERTIME', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1753, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1754, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1755, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1756, 'OVERTIME', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1757, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1758, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1759, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1760, 'OVERTIME', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1761, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1762, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1763, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1764, 'OVERTIME', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1765, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1766, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1767, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1768, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1769, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1770, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1771, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1772, 'OVERTIME', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1773, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1774, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1775, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1776, 'OVERTIME', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1777, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1778, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1779, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1780, 'OVERTIME', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1781, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1782, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1783, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1784, 'OVERTIME', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1785, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1786, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1787, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1788, 'OVERTIME', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1789, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1790, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1791, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1792, 'OVERTIME', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1793, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1794, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1795, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1796, 'OVERTIME', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1797, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1798, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1799, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1800, 'OVERTIME', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1801, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1802, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1803, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1804, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1805, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1806, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1807, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1808, 'OVERTIME', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1809, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1810, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1811, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1812, 'OVERTIME', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1813, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1814, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1815, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1816, 'OVERTIME', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1817, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1818, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1819, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1820, 'OVERTIME', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1821, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1822, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04');
INSERT INTO `eventos` (`id`, `titulo`, `inicio`, `fim`, `minutos`, `km`, `status`, `source`, `leave_request_id`, `period_id`, `tipo`, `user_id`, `created_at`, `updated_at`) VALUES
(1823, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1824, 'OVERTIME', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1825, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1826, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1827, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1828, 'OVERTIME', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1829, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1830, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1831, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'approved', 'approval', NULL, 15, 'WORK', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1832, 'OVERTIME', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 120, NULL, 'approved', 'approval', NULL, 15, 'OVERTIME', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1833, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 180, NULL, 'approved', 'approval', NULL, 15, 'ONCALL', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1834, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 15, 'KM', 56, '2025-09-10 19:45:59', '2025-09-10 19:48:04'),
(1835, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1836, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1837, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1838, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1839, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1840, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1843, 'WORK', '2025-10-05 00:00:00', '2025-10-05 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1844, 'KM', '2025-10-05 00:00:00', '2025-10-05 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1845, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1846, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1847, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1848, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1849, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1850, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1851, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1852, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1853, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1854, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1855, 'WORK', '2025-10-11 00:00:00', '2025-10-11 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1856, 'KM', '2025-10-11 00:00:00', '2025-10-11 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1857, 'WORK', '2025-10-12 00:00:00', '2025-10-12 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1858, 'KM', '2025-10-12 00:00:00', '2025-10-12 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1859, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1860, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1861, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1862, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1863, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1864, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1865, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1866, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1867, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1868, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1869, 'WORK', '2025-10-18 00:00:00', '2025-10-18 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1870, 'KM', '2025-10-18 00:00:00', '2025-10-18 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1871, 'WORK', '2025-10-19 00:00:00', '2025-10-19 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1872, 'KM', '2025-10-19 00:00:00', '2025-10-19 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1873, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1874, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1875, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1876, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1877, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1878, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1879, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1880, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1881, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1882, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1883, 'WORK', '2025-10-25 00:00:00', '2025-10-25 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1884, 'KM', '2025-10-25 00:00:00', '2025-10-25 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1885, 'WORK', '2025-10-26 00:00:00', '2025-10-26 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1886, 'KM', '2025-10-26 00:00:00', '2025-10-26 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1887, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1888, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1889, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1890, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1891, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1892, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1893, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1894, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1895, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'approved', 'approval', NULL, 16, 'WORK', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1896, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 35.00, 'approved', 'approval', NULL, 16, 'KM', 21, '2025-09-11 11:01:12', '2025-09-11 11:05:31'),
(1960, 'OVERTIME', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 240, NULL, 'approved', 'approval', NULL, 16, 'OVERTIME', 21, '2025-09-11 11:01:33', '2025-09-11 11:05:31'),
(1961, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'ONCALL', 21, '2025-09-11 11:01:33', '2025-09-11 11:05:31'),
(1964, 'OVERTIME', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'OVERTIME', 21, '2025-09-11 11:01:40', '2025-09-11 11:05:31'),
(1965, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 0, NULL, 'approved', 'approval', NULL, 16, 'ONCALL', 21, '2025-09-11 11:01:40', '2025-09-11 11:05:31'),
(1967, 'baixa_medica', '2025-10-21 00:00:00', '2025-10-25 23:59:59', NULL, NULL, 'approved', 'approval', 13, NULL, 'LEAVE', 21, '2025-09-11 11:04:03', '2025-09-11 11:04:03'),
(1968, 'WORK', '2025-11-12 00:00:00', '2025-11-12 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-11 11:55:59', '2025-09-15 18:57:25'),
(1969, 'OVERTIME', '2025-11-12 00:00:00', '2025-11-12 23:59:59', 120, NULL, 'draft', 'manual', NULL, NULL, 'OVERTIME', 21, '2025-09-11 11:55:59', '2025-09-11 11:55:59'),
(1970, 'ferias', '2025-11-26 00:00:00', '2025-11-29 23:59:59', NULL, NULL, 'approved', 'approval', 14, NULL, 'LEAVE', 21, '2025-09-12 09:32:53', '2025-09-12 09:32:53'),
(1976, 'ferias', '2025-12-01 00:00:00', '2025-12-20 23:59:59', NULL, NULL, 'approved', 'approval', 15, NULL, 'LEAVE', 39, '2025-09-12 09:55:23', '2025-09-12 09:55:23'),
(1977, 'Substituição João Operador', '2025-12-01 00:00:00', '2025-12-20 23:59:59', NULL, NULL, 'approved', 'system', 15, NULL, 'SUBSTITUTION', 21, '2025-09-12 09:55:23', '2025-09-12 09:55:23'),
(1978, 'ferias', '2025-08-01 00:00:00', '2025-08-10 23:59:59', NULL, NULL, 'approved', 'approval', 16, NULL, 'LEAVE', 21, '2025-09-12 10:50:28', '2025-09-12 10:50:28'),
(1979, 'WORK', '2025-11-01 00:00:00', '2025-11-01 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1980, 'KM', '2025-11-01 00:00:00', '2025-11-01 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1981, 'WORK', '2025-11-02 00:00:00', '2025-11-02 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1982, 'KM', '2025-11-02 00:00:00', '2025-11-02 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1983, 'WORK', '2025-11-03 00:00:00', '2025-11-03 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1984, 'KM', '2025-11-03 00:00:00', '2025-11-03 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1985, 'WORK', '2025-11-04 00:00:00', '2025-11-04 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1986, 'KM', '2025-11-04 00:00:00', '2025-11-04 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1987, 'WORK', '2025-11-05 00:00:00', '2025-11-05 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1988, 'KM', '2025-11-05 00:00:00', '2025-11-05 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1989, 'WORK', '2025-11-06 00:00:00', '2025-11-06 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1990, 'KM', '2025-11-06 00:00:00', '2025-11-06 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1991, 'WORK', '2025-11-07 00:00:00', '2025-11-07 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1992, 'KM', '2025-11-07 00:00:00', '2025-11-07 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1993, 'WORK', '2025-11-08 00:00:00', '2025-11-08 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1994, 'KM', '2025-11-08 00:00:00', '2025-11-08 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1995, 'WORK', '2025-11-09 00:00:00', '2025-11-09 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1996, 'KM', '2025-11-09 00:00:00', '2025-11-09 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1997, 'WORK', '2025-11-10 00:00:00', '2025-11-10 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1998, 'KM', '2025-11-10 00:00:00', '2025-11-10 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(1999, 'WORK', '2025-11-11 00:00:00', '2025-11-11 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2000, 'KM', '2025-11-11 00:00:00', '2025-11-11 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2002, 'KM', '2025-11-12 00:00:00', '2025-11-12 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2003, 'WORK', '2025-11-13 00:00:00', '2025-11-13 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:42'),
(2004, 'KM', '2025-11-13 00:00:00', '2025-11-13 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:42'),
(2005, 'WORK', '2025-11-14 00:00:00', '2025-11-14 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2006, 'KM', '2025-11-14 00:00:00', '2025-11-14 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2007, 'WORK', '2025-11-15 00:00:00', '2025-11-15 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2008, 'KM', '2025-11-15 00:00:00', '2025-11-15 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2009, 'WORK', '2025-11-16 00:00:00', '2025-11-16 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2010, 'KM', '2025-11-16 00:00:00', '2025-11-16 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2011, 'WORK', '2025-11-17 00:00:00', '2025-11-17 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2012, 'KM', '2025-11-17 00:00:00', '2025-11-17 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2013, 'WORK', '2025-11-18 00:00:00', '2025-11-18 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2014, 'KM', '2025-11-18 00:00:00', '2025-11-18 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2015, 'WORK', '2025-11-19 00:00:00', '2025-11-19 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2016, 'KM', '2025-11-19 00:00:00', '2025-11-19 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2017, 'WORK', '2025-11-20 00:00:00', '2025-11-20 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2018, 'KM', '2025-11-20 00:00:00', '2025-11-20 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2019, 'WORK', '2025-11-21 00:00:00', '2025-11-21 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2020, 'KM', '2025-11-21 00:00:00', '2025-11-21 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2021, 'WORK', '2025-11-22 00:00:00', '2025-11-22 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2022, 'KM', '2025-11-22 00:00:00', '2025-11-22 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2023, 'WORK', '2025-11-23 00:00:00', '2025-11-23 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2024, 'KM', '2025-11-23 00:00:00', '2025-11-23 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2025, 'WORK', '2025-11-24 00:00:00', '2025-11-24 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2026, 'KM', '2025-11-24 00:00:00', '2025-11-24 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2027, 'WORK', '2025-11-25 00:00:00', '2025-11-25 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2028, 'KM', '2025-11-25 00:00:00', '2025-11-25 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2029, 'WORK', '2025-11-26 00:00:00', '2025-11-26 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2030, 'KM', '2025-11-26 00:00:00', '2025-11-26 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2031, 'WORK', '2025-11-27 00:00:00', '2025-11-27 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2032, 'KM', '2025-11-27 00:00:00', '2025-11-27 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2033, 'WORK', '2025-11-28 00:00:00', '2025-11-28 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2034, 'KM', '2025-11-28 00:00:00', '2025-11-28 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2035, 'WORK', '2025-11-29 00:00:00', '2025-11-29 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2036, 'KM', '2025-11-29 00:00:00', '2025-11-29 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2037, 'WORK', '2025-11-30 00:00:00', '2025-11-30 23:59:59', 540, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2038, 'KM', '2025-11-30 00:00:00', '2025-11-30 23:59:59', NULL, 30.00, 'draft', 'manual', NULL, NULL, 'KM', 21, '2025-09-15 18:57:25', '2025-09-15 18:57:25'),
(2100, 'OVERTIME', '2025-11-13 00:00:00', '2025-11-13 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'OVERTIME', 21, '2025-09-15 18:57:42', '2025-09-15 18:57:42'),
(2101, 'ONCALL', '2025-11-13 00:00:00', '2025-11-13 23:59:59', 0, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 21, '2025-09-15 18:57:42', '2025-09-15 18:57:42');

-- --------------------------------------------------------

--
-- Estrutura da tabela `finance_profiles`
--

CREATE TABLE `finance_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `numero` varchar(50) DEFAULT NULL,
  `nome_completo` varchar(255) DEFAULT NULL,
  `vencimento_estimado` decimal(10,2) DEFAULT NULL,
  `vencimento_base` decimal(10,2) DEFAULT NULL,
  `valor_sub_alimentacao` decimal(10,2) DEFAULT NULL,
  `dias_sub_alimentacao` int(11) DEFAULT NULL,
  `kms_estimados` decimal(10,2) DEFAULT NULL,
  `valor_por_km` decimal(10,2) DEFAULT NULL,
  `valor_prevencoes` decimal(10,2) DEFAULT NULL,
  `valor_passe_transporte` decimal(10,2) DEFAULT NULL,
  `iht` decimal(10,2) DEFAULT NULL,
  `ajuda_custo_estimado` decimal(10,2) DEFAULT NULL,
  `subsidio_noturno` decimal(10,2) DEFAULT NULL,
  `subsidio_turno` decimal(10,2) DEFAULT NULL,
  `ajudas_custos_deduc` decimal(10,2) DEFAULT NULL,
  `adiantamentos_deduzir` decimal(10,2) DEFAULT NULL,
  `bonus_bonificacoes` decimal(10,2) DEFAULT NULL,
  `duodecimos` tinyint(1) DEFAULT NULL,
  `prevencoes_sn` tinyint(1) DEFAULT NULL,
  `penhoras_sn` tinyint(1) DEFAULT NULL,
  `ferias_sn` tinyint(1) DEFAULT NULL,
  `faltas_nao_rem` int(11) DEFAULT NULL,
  `faltas_nao_rem_just` int(11) DEFAULT NULL,
  `faltas_rem_just` int(11) DEFAULT NULL,
  `baixa_medica_start` date DEFAULT NULL,
  `baixa_medica_end` date DEFAULT NULL,
  `ferias_start` date DEFAULT NULL,
  `ferias_end` date DEFAULT NULL,
  `observacoes` text,
  `ajustes_vencimento` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `finance_profiles`
--

INSERT INTO `finance_profiles` (`id`, `user_id`, `created_at`, `updated_at`, `numero`, `nome_completo`, `vencimento_estimado`, `vencimento_base`, `valor_sub_alimentacao`, `dias_sub_alimentacao`, `kms_estimados`, `valor_por_km`, `valor_prevencoes`, `valor_passe_transporte`, `iht`, `ajuda_custo_estimado`, `subsidio_noturno`, `subsidio_turno`, `ajudas_custos_deduc`, `adiantamentos_deduzir`, `bonus_bonificacoes`, `duodecimos`, `prevencoes_sn`, `penhoras_sn`, `ferias_sn`, `faltas_nao_rem`, `faltas_nao_rem_just`, `faltas_rem_just`, `baixa_medica_start`, `baixa_medica_end`, `ferias_start`, `ferias_end`, `observacoes`, `ajustes_vencimento`) VALUES
(1, 23, '2025-09-05 15:32:15', '2025-09-05 16:48:13', '1001', 'Miguel Intermedio', 1250.00, 1200.00, 120.50, 20, 150.00, 0.36, 80.00, 45.00, 150.00, 50.00, 100.00, 200.00, 30.00, 75.00, 250.00, 1, 0, 0, 1, 2, 1, 0, '2025-09-01', '2025-09-10', '2025-08-01', '2025-08-14', 'Contrato atualizado com prémio anual.', 'Ajuste retroativo +40€'),
(12, 52, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP052', 'João Silva', 1250.00, 1200.00, 6.00, 22, 50.00, 0.36, 35.00, 30.00, 0.00, 15.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-08-01', '2025-08-15', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(13, 53, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP053', 'Maria Fernandes', 1350.00, 1300.00, 6.00, 21, 40.00, 0.36, 25.00, 30.00, 0.00, 10.00, 0.00, 0.00, 0.00, 0.00, 50.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-08-05', '2025-08-20', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(14, 54, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP054', 'Pedro Santos', 1300.00, 1250.00, 6.00, 22, 30.00, 0.36, 20.00, 20.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 0, 0, 1, 0, 0, 0, NULL, NULL, '2025-07-15', '2025-07-31', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(15, 55, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP055', 'Ana Rodrigues', 1450.00, 1400.00, 6.00, 20, 60.00, 0.36, 40.00, 35.00, 0.00, 20.00, 0.00, 0.00, 0.00, 0.00, 100.00, 1, 1, 0, 1, 1, 0, 0, NULL, NULL, '2025-09-02', '2025-09-16', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(16, 56, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP056', 'Miguel Costa', 1400.00, 1350.00, 6.00, 22, 45.00, 0.36, 25.00, 30.00, 0.00, 10.00, 0.00, 0.00, 0.00, 0.00, 75.00, 1, 0, 0, 1, 0, 1, 0, NULL, NULL, '2025-06-10', '2025-06-24', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(17, 57, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP057', 'Sofia Almeida', 1550.00, 1500.00, 6.00, 22, 70.00, 0.36, 50.00, 40.00, 0.00, 25.00, 0.00, 50.00, 0.00, 0.00, 150.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-07-01', '2025-07-14', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(18, 58, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP058', 'Ricardo Lopes', 1150.00, 1100.00, 6.00, 21, 20.00, 0.36, 10.00, 25.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 0, 0, 1, 0, 0, 0, NULL, NULL, '2025-10-01', '2025-10-15', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(19, 59, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP059', 'Carla Martins', 1300.00, 1250.00, 6.00, 22, 35.00, 0.36, 20.00, 30.00, 0.00, 15.00, 0.00, 0.00, 0.00, 0.00, 60.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-08-19', '2025-09-02', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(20, 60, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP060', 'Tiago Ferreira', 1330.00, 1280.00, 6.00, 21, 40.00, 0.36, 30.00, 32.50, 0.00, 10.00, 0.00, 0.00, 0.00, 0.00, 80.00, 1, 0, 0, 1, 0, 0, 1, NULL, NULL, '2025-06-20', '2025-07-04', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(21, 61, '2025-09-10 19:28:50', '2025-09-10 19:28:50', 'EMP061', 'Inês Carvalho', 1370.00, 1320.00, 6.00, 22, 50.00, 0.36, 35.00, 35.00, 0.00, 12.50, 0.00, 0.00, 0.00, 0.00, 90.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-09-10', '2025-09-24', 'Ficha de teste para demonstração.', 'Sem ajustes.'),
(22, 12, '2025-09-10 19:32:11', '2025-09-10 19:32:11', 'EMP012', 'Miguel Administrador RH', 2000.00, 1900.00, 6.00, 22, 60.00, 0.36, 50.00, 40.00, 0.00, 25.00, 0.00, 50.00, 0.00, 0.00, 200.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-07-01', '2025-07-15', 'Administrador RH para testes.', 'Sem ajustes.'),
(23, 21, '2025-09-10 19:32:11', '2025-09-10 19:32:11', 'EMP021', 'Miguel Operador', 1100.00, 1050.00, 6.00, 21, 25.00, 0.36, 15.00, 20.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 0, 0, 1, 0, 0, 0, NULL, NULL, '2025-06-10', '2025-06-24', 'Operador de teste.', 'Sem ajustes.'),
(24, 24, '2025-09-10 19:32:11', '2025-09-10 19:32:11', 'EMP024', 'Miguel Admin', 1800.00, 1750.00, 6.00, 22, 40.00, 0.36, 30.00, 30.00, 0.00, 20.00, 0.00, 0.00, 0.00, 0.00, 100.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-08-05', '2025-08-20', 'Administrador para testes.', 'Sem ajustes.'),
(25, 25, '2025-09-10 19:32:11', '2025-09-10 19:32:11', 'EMP025', 'Miguel Estrela', 1600.00, 1550.00, 6.00, 22, 35.00, 0.36, 25.00, 25.00, 0.00, 15.00, 0.00, 0.00, 0.00, 0.00, 75.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-09-01', '2025-09-14', 'Colaborador estrela (teste).', 'Sem ajustes.'),
(26, 38, '2025-09-10 19:32:11', '2025-09-10 19:32:11', 'EMP038', 'Diretor RH', 2200.00, 2100.00, 6.00, 22, 80.00, 0.36, 60.00, 50.00, 0.00, 30.00, 0.00, 50.00, 0.00, 0.00, 250.00, 1, 1, 0, 1, 0, 0, 0, NULL, NULL, '2025-07-20', '2025-08-03', 'Diretor RH para testes.', 'Sem ajustes.'),
(27, 39, '2025-09-10 19:32:11', '2025-09-10 19:32:11', 'EMP039', 'João Operador', 1150.00, 1100.00, 6.00, 21, 30.00, 0.36, 20.00, 25.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1, 0, 0, 1, 0, 0, 0, NULL, NULL, '2025-06-15', '2025-06-29', 'Operador de teste.', 'Sem ajustes.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `overtime`
--

CREATE TABLE `overtime` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dia` date GENERATED ALWAYS AS (cast(`inicio` as date)) STORED,
  `inicio` datetime NOT NULL,
  `fim` datetime NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `origem` enum('approval','import','system') NOT NULL DEFAULT 'approval',
  `criado_por` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedidos_ferias`
--

CREATE TABLE `pedidos_ferias` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tipo` enum('licenca_paternidade','licenca_maternidade','baixa_medica','baixa_seguro','casamento','consulta_medica','ferias','pessoal') NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `justificacao` text NOT NULL,
  `ficheiro` varchar(255) DEFAULT NULL,
  `estado` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `decidido_por` int(11) DEFAULT NULL,
  `comentario` text,
  `responsavel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `pedidos_ferias`
--

INSERT INTO `pedidos_ferias` (`id`, `user_id`, `tipo`, `data_inicio`, `data_fim`, `justificacao`, `ficheiro`, `estado`, `criado_em`, `decidido_por`, `comentario`, `responsavel_id`) VALUES
(11, 21, 'baixa_medica', '2025-09-20', '2025-09-22', 'Miocardite', 'comprovativo_1757530211_4920.pdf', 'aprovado', '2025-09-10 18:50:11', 22, NULL, 39),
(12, 39, 'ferias', '2025-10-18', '2025-10-11', 'svsffs', 'comprovativo_1757530954_9290.pdf', 'aprovado', '2025-09-10 19:02:34', 12, NULL, NULL),
(13, 21, 'baixa_medica', '2025-10-21', '2025-10-25', 'Miocardite', 'comprovativo_1757588600_9729.pdf', 'aprovado', '2025-09-11 11:03:20', 22, NULL, 39),
(14, 21, 'ferias', '2025-11-26', '2025-11-29', 'Teste', NULL, 'aprovado', '2025-09-12 09:32:21', 22, NULL, 39),
(15, 39, 'ferias', '2025-12-01', '2025-12-20', 'Test', NULL, 'aprovado', '2025-09-12 09:41:13', 22, NULL, 21),
(16, 21, 'ferias', '2025-08-01', '2025-08-10', 'livre', '/uploads/leaves/2025/09/Pedido-Contacto-Obras-1--20250912105028-024295.pdf', 'aprovado', '2025-09-12 10:50:28', 12, 'Marcação direta RH', NULL),
(17, 21, 'ferias', '2025-11-15', '2025-11-15', 'Miocardite', NULL, 'pendente', '2025-09-15 18:59:20', NULL, NULL, 39);

-- --------------------------------------------------------

--
-- Estrutura da tabela `permission`
--

CREATE TABLE `permission` (
  `id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `label` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `permission`
--

INSERT INTO `permission` (`id`, `code`, `label`) VALUES
(1, 'colab_managment', 'Criar colabs, dar update de permissões e hierarquia'),
(2, 'direct_leave', 'Adicionar falta direta a colab'),
(3, 'periods_info', 'Ver e exportar todos os eventos de colabs'),
(4, 'request_overtime', 'Pedir horas extra'),
(5, 'approve_overtime', 'Aprovar horas extra');

-- --------------------------------------------------------

--
-- Estrutura da tabela `request_overtime`
--

CREATE TABLE `request_overtime` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `justificacao` text NOT NULL,
  `ficheiro` varchar(255) DEFAULT NULL,
  `estado` enum('requested','approved','rejected') NOT NULL DEFAULT 'requested',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `criado_por` int(11) NOT NULL,
  `decidido_por` int(11) DEFAULT NULL,
  `decidido_em` datetime DEFAULT NULL,
  `comentario` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `timesheet_periods`
--

CREATE TABLE `timesheet_periods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `estado` enum('open','submitted','approved','rejected','locked') NOT NULL DEFAULT 'open',
  `decidido_por` int(11) DEFAULT NULL,
  `decidido_em` timestamp NULL DEFAULT NULL,
  `comentario` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `timesheet_periods`
--

INSERT INTO `timesheet_periods` (`id`, `user_id`, `period_start`, `period_end`, `estado`, `decidido_por`, `decidido_em`, `comentario`, `created_at`, `updated_at`) VALUES
(8, 39, '2025-10-01', '2025-10-31', 'approved', 22, '2025-09-08 11:09:39', NULL, '2025-09-08 11:05:07', '2025-09-08 11:09:39'),
(9, 21, '2025-09-01', '2025-09-30', 'approved', 22, '2025-09-10 19:47:48', NULL, '2025-09-10 18:54:16', '2025-09-10 19:47:48'),
(10, 41, '2025-10-01', '2025-10-31', 'approved', 22, '2025-09-10 19:47:54', NULL, '2025-09-10 19:35:59', '2025-09-10 19:47:54'),
(11, 52, '2025-10-01', '2025-10-31', 'approved', 22, '2025-09-10 19:47:57', NULL, '2025-09-10 19:42:02', '2025-09-10 19:47:57'),
(12, 53, '2025-10-01', '2025-10-31', 'approved', 22, '2025-09-10 19:48:01', NULL, '2025-09-10 19:42:47', '2025-09-10 19:48:01'),
(13, 54, '2025-10-01', '2025-10-31', 'approved', 24, '2025-09-10 19:50:02', NULL, '2025-09-10 19:43:55', '2025-09-10 19:50:02'),
(14, 55, '2025-10-01', '2025-10-31', 'approved', 23, '2025-09-10 19:50:31', NULL, '2025-09-10 19:44:44', '2025-09-10 19:50:31'),
(15, 56, '2025-10-01', '2025-10-31', 'approved', 22, '2025-09-10 19:48:04', NULL, '2025-09-10 19:46:06', '2025-09-10 19:48:04'),
(16, 21, '2025-10-01', '2025-10-31', 'approved', 22, '2025-09-11 11:05:31', NULL, '2025-09-11 11:05:06', '2025-09-11 11:05:31'),
(17, 21, '2025-08-01', '2025-08-31', 'rejected', 22, '2025-09-12 11:01:40', 'faltam registos de 12/08 e 13/08', '2025-09-12 10:54:07', '2025-09-12 11:01:40');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin_rh','inter','opera','admin','inter2','*','finan') NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `role`, `company_id`) VALUES
(12, 'Miguel Administrador RH', 'adminrh@gmail.com', '$2y$10$B.bMkH59Q50F9En8HDkGkuWb7gg7q/JJx0HJdyeTA4PeknAKRLRSC', 'admin_rh', 11),
(21, 'Miguel Operador', 'oper@gmail.com', '$2y$10$EGMXoFEYCIe.ikxef1vBKeTbPKZHqYo4VlRwNDhrXsOHLxEdeQtam', 'opera', 11),
(22, 'Miguel Intermedio2', 'inter2@gmail.com', '$2y$10$M/EJOTR/oBC8dc2smGblW.wR0zniGA0/i.WS31E.xKARJY71jvUz.', 'inter2', 11),
(23, 'Miguel Intermedio', 'inter@gmail.com', '$2y$10$/P/JJfaYhiWHckhOxtS8y.XFXIOFs7lopmV9L3bWD8Ilea6B.MiIO', 'inter', 10),
(24, 'Miguel Admin', 'admin@gmail.com', '$2y$10$T/4B9aKCQZWrW46bUVnm2egAlen9LGnZte1AuWtFjPfS5oqBR0yme', 'admin', 10),
(25, 'Miguel Estrela', 'estrela@gmail.com', '$2y$10$ECmuiiWuw7cjFqcWTCUXJeXfApm4q.mus2KMOCuETwX0OfHBoA1EO', '*', 11),
(38, 'Diretor RH', 'diretorrh@gmail.com', '$2y$10$BRxnnpNXBIYEi6rI570uIeDB/R3cDNnSXQt4xpAa9YcFfoE/wYmf2', '*', 11),
(39, 'João Operador', 'joao@gmail.com', '$2y$10$ElvaN9UKBsDw6h4Y/gHF/OTBZubJKKmdbdf/NA1RdLcdp7lRbKbSq', 'opera', 3),
(40, 'Luís Financeiro', 'finan@gmail.com', '$2y$10$2XFNLGIiAz2jlNZjYl6CG.5kF.2Op6eAKfEv0me1nvUPQML2slRGO', 'finan', 10),
(41, 'Sandra Ferreira', 'sandraferreira@gmail.com', '$2y$10$Fp3b9rXYdGyYguIlhaNg5enche2AvMJD7UliMWxHqyRIDLMksqWrm', 'opera', 11),
(52, 'João Silva', 'joao.silva@teste.com', '$2y$10$ECmuiiWuw7cjFqcWTCUXJeXfApm4q.mus2KMOCuETwX0OfHBoA1EO', 'opera', 11),
(53, 'Maria Fernandes', 'maria.fernandes@teste.com', '$2y$10$ECmuiiWuw7cjFqcWTCUXJeXfApm4q.mus2KMOCuETwX0OfHBoA1EO', 'opera', 11),
(54, 'Pedro Santos', 'pedro.santos@teste.com', '$2y$10$ECmuiiWuw7cjFqcWTCUXJeXfApm4q.mus2KMOCuETwX0OfHBoA1EO', 'inter', 11),
(55, 'Ana Rodrigues', 'ana.rodrigues@teste.com', '$2y$10$ECmuiiWuw7cjFqcWTCUXJeXfApm4q.mus2KMOCuETwX0OfHBoA1EO', 'inter2', 11),
(56, 'Miguel Costa', 'miguel.costa@teste.com', '$2y$10$ECmuiiWuw7cjFqcWTCUXJeXfApm4q.mus2KMOCuETwX0OfHBoA1EO', 'opera', 11),
(57, 'Sofia Almeida', 'sofia.almeida@teste.com', '$2y$10$123456789012345678901uYtKzX1gT3fQw98hJsd1234567890abc', 'admin', 11),
(58, 'Ricardo Lopes', 'ricardo.lopes@teste.com', '$2y$10$123456789012345678901uYtKzX1gT3fQw98hJsd1234567890abc', 'opera', 11),
(59, 'Carla Martins', 'carla.martins@teste.com', '$2y$10$123456789012345678901uYtKzX1gT3fQw98hJsd1234567890abc', 'opera', 11),
(60, 'Tiago Ferreira', 'tiago.ferreira@teste.com', '$2y$10$123456789012345678901uYtKzX1gT3fQw98hJsd1234567890abc', 'opera', 11),
(61, 'Inês Carvalho', 'ines.carvalho@teste.com', '$2y$10$123456789012345678901uYtKzX1gT3fQw98hJsd1234567890abc', 'finan', 11),
(62, 'Nundo', 'nuno@gmail.com', '$2y$10$/Kx3sgc3z9vTzr/LLtZP1e0ovDfM3uM3wZbYz/S0NQq/C4qkO94YG', 'admin_rh', 1),
(63, 'Miguel', 'miguel@gmail.com', '$2y$10$jZT.5dbmiwlxGU1r5sOoE.33uSb4gaCStERmWKHHIGyB5aBQdWhDu', 'admin_rh', 1),
(64, 'Henrique', 'henrique@gmail.com', '$2y$10$K8VcxjW6Piid0.aZikxEXeROBThMFtd2K2T0FrHZKRJvQo/N0RYoi', 'admin_rh', 1),
(65, 'Luis', 'luis@gmail.com', '$2y$10$juBeE2rt4zNgwvRNasBBIO9hdd/kzPiFl4qAfE4yq8srrYWFtkrjG', 'admin_rh', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_permission`
--

CREATE TABLE `user_permission` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `user_permission`
--

INSERT INTO `user_permission` (`user_id`, `permission_id`, `granted_at`) VALUES
(12, 1, '2025-10-16 10:33:17');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `colaborador_dados`
--
ALTER TABLE `colaborador_dados`
  ADD PRIMARY KEY (`user_id`);

--
-- Índices para tabela `colaborador_edicoes`
--
ALTER TABLE `colaborador_edicoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_colaborador` (`user_id`);

--
-- Índices para tabela `colaborador_responsaveis`
--
ALTER TABLE `colaborador_responsaveis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cr_resp_now` (`responsavel_id`,`ativo`,`valido_desde`,`valido_ate`),
  ADD KEY `idx_cr_colab` (`colaborador_id`);

--
-- Índices para tabela `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices para tabela `contactos_emergencia`
--
ALTER TABLE `contactos_emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `contactos_emergencia_edicoes`
--
ALTER TABLE `contactos_emergencia_edicoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_evento_user_tipo_dia` (`user_id`,`tipo`,`dia`),
  ADD UNIQUE KEY `uniq_leave_request_tipo` (`leave_request_id`,`tipo`),
  ADD KEY `idx_eventos_user_dia` (`user_id`,`dia`),
  ADD KEY `idx_eventos_tipo_dia` (`tipo`,`dia`),
  ADD KEY `idx_eventos_period` (`period_id`);

--
-- Índices para tabela `finance_profiles`
--
ALTER TABLE `finance_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Índices para tabela `overtime`
--
ALTER TABLE `overtime`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ot_no_overlap` (`user_id`,`inicio`,`fim`),
  ADD KEY `fk_ot_req` (`request_id`),
  ADD KEY `fk_ot_criadopor` (`criado_por`),
  ADD KEY `idx_ot_user_dia` (`user_id`,`dia`);

--
-- Índices para tabela `pedidos_ferias`
--
ALTER TABLE `pedidos_ferias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `decidido_por` (`decidido_por`);

--
-- Índices para tabela `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Índices para tabela `request_overtime`
--
ALTER TABLE `request_overtime`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ro_criadopor` (`criado_por`),
  ADD KEY `fk_ro_decisor` (`decidido_por`),
  ADD KEY `idx_ro_user_inicio_fim` (`user_id`,`data_inicio`,`data_fim`),
  ADD KEY `idx_ro_estado` (`estado`);

--
-- Índices para tabela `timesheet_periods`
--
ALTER TABLE `timesheet_periods`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_company` (`company_id`);

--
-- Índices para tabela `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`user_id`,`permission_id`),
  ADD KEY `fk_up_perm` (`permission_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `colaborador_edicoes`
--
ALTER TABLE `colaborador_edicoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `colaborador_responsaveis`
--
ALTER TABLE `colaborador_responsaveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `company`
--
ALTER TABLE `company`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `contactos_emergencia`
--
ALTER TABLE `contactos_emergencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `contactos_emergencia_edicoes`
--
ALTER TABLE `contactos_emergencia_edicoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2102;

--
-- AUTO_INCREMENT de tabela `finance_profiles`
--
ALTER TABLE `finance_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `overtime`
--
ALTER TABLE `overtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos_ferias`
--
ALTER TABLE `pedidos_ferias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `request_overtime`
--
ALTER TABLE `request_overtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `timesheet_periods`
--
ALTER TABLE `timesheet_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `colaborador_dados`
--
ALTER TABLE `colaborador_dados`
  ADD CONSTRAINT `fk_colab_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_id_colab` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `colaborador_edicoes`
--
ALTER TABLE `colaborador_edicoes`
  ADD CONSTRAINT `fk_colaborador` FOREIGN KEY (`user_id`) REFERENCES `colaborador_dados` (`user_id`);

--
-- Limitadores para a tabela `colaborador_responsaveis`
--
ALTER TABLE `colaborador_responsaveis`
  ADD CONSTRAINT `fk_cr_colab` FOREIGN KEY (`colaborador_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cr_resp` FOREIGN KEY (`responsavel_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `contactos_emergencia`
--
ALTER TABLE `contactos_emergencia`
  ADD CONSTRAINT `contactos_emergencia_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `colaborador_dados` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `contactos_emergencia_edicoes`
--
ALTER TABLE `contactos_emergencia_edicoes`
  ADD CONSTRAINT `contactos_emergencia_edicoes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `finance_profiles`
--
ALTER TABLE `finance_profiles`
  ADD CONSTRAINT `fk_fin_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `overtime`
--
ALTER TABLE `overtime`
  ADD CONSTRAINT `fk_ot_criadopor` FOREIGN KEY (`criado_por`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_ot_req` FOREIGN KEY (`request_id`) REFERENCES `request_overtime` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ot_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `pedidos_ferias`
--
ALTER TABLE `pedidos_ferias`
  ADD CONSTRAINT `pedidos_ferias_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_ferias_ibfk_2` FOREIGN KEY (`decidido_por`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `request_overtime`
--
ALTER TABLE `request_overtime`
  ADD CONSTRAINT `fk_ro_criadopor` FOREIGN KEY (`criado_por`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_ro_decisor` FOREIGN KEY (`decidido_por`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ro_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `user_permission`
--
ALTER TABLE `user_permission`
  ADD CONSTRAINT `fk_up_perm` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
