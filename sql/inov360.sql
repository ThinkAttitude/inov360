-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Tempo de geração: 20-Out-2025 às 11:16
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
  `created_by` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `valido_desde` datetime DEFAULT NULL,
  `valido_ate` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `colaborador_responsaveis`
--

INSERT INTO `colaborador_responsaveis` (`id`, `colaborador_id`, `responsavel_id`, `created_by`, `ativo`, `valido_desde`, `valido_ate`, `created_at`) VALUES
(3, 62, 64, 12, 1, NULL, NULL, '2025-10-16 12:06:09'),
(4, 63, 62, 12, 1, NULL, NULL, '2025-10-16 12:06:09'),
(5, 63, 65, 12, 1, NULL, NULL, '2025-10-16 12:07:38');

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
(2176, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2177, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2178, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2179, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 120, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2180, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 60, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2181, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 6.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2182, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2183, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2184, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2185, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2186, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2187, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2188, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2189, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2190, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2191, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2192, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2193, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2194, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2195, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2196, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2197, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2198, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2199, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2200, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2201, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2202, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2203, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 0, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2204, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 0, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2205, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2206, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2207, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2208, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2209, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2210, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2211, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2212, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2213, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2214, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2215, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2216, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2217, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2218, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2219, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2220, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2221, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2222, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2223, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2224, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2225, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2226, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2227, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 480, NULL, 'approved', 'approval', NULL, 18, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2228, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 180, NULL, 'approved', 'approval', NULL, 18, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2229, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 18, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:50:46'),
(2230, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2231, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2232, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2233, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2234, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2235, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2236, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2237, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2238, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2239, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2240, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2241, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2242, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2243, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2244, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 63, '2025-10-17 09:30:26', '2025-10-17 09:30:26'),
(2248, 'ferias', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, NULL, 'approved', 'approval', 20, NULL, 'LEAVE', 63, '2025-10-17 09:33:47', '2025-10-17 09:33:47'),
(2249, 'WORK', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2250, 'ONCALL', '2025-10-01 00:00:00', '2025-10-01 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2251, 'KM', '2025-10-01 00:00:00', '2025-10-01 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2252, 'WORK', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2253, 'ONCALL', '2025-10-02 00:00:00', '2025-10-02 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2254, 'KM', '2025-10-02 00:00:00', '2025-10-02 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2255, 'WORK', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2256, 'ONCALL', '2025-10-03 00:00:00', '2025-10-03 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2257, 'KM', '2025-10-03 00:00:00', '2025-10-03 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2258, 'WORK', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2259, 'ONCALL', '2025-10-06 00:00:00', '2025-10-06 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2260, 'KM', '2025-10-06 00:00:00', '2025-10-06 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2261, 'WORK', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2262, 'ONCALL', '2025-10-07 00:00:00', '2025-10-07 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2263, 'KM', '2025-10-07 00:00:00', '2025-10-07 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2264, 'WORK', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2265, 'ONCALL', '2025-10-08 00:00:00', '2025-10-08 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2266, 'KM', '2025-10-08 00:00:00', '2025-10-08 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2267, 'WORK', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2268, 'ONCALL', '2025-10-09 00:00:00', '2025-10-09 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2269, 'KM', '2025-10-09 00:00:00', '2025-10-09 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2270, 'WORK', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2271, 'ONCALL', '2025-10-10 00:00:00', '2025-10-10 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2272, 'KM', '2025-10-10 00:00:00', '2025-10-10 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2273, 'WORK', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2274, 'ONCALL', '2025-10-13 00:00:00', '2025-10-13 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2275, 'KM', '2025-10-13 00:00:00', '2025-10-13 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2276, 'WORK', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2277, 'ONCALL', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2278, 'KM', '2025-10-14 00:00:00', '2025-10-14 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2279, 'WORK', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2280, 'ONCALL', '2025-10-15 00:00:00', '2025-10-15 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2281, 'KM', '2025-10-15 00:00:00', '2025-10-15 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2282, 'WORK', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2283, 'ONCALL', '2025-10-16 00:00:00', '2025-10-16 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2284, 'KM', '2025-10-16 00:00:00', '2025-10-16 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2285, 'WORK', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2286, 'ONCALL', '2025-10-17 00:00:00', '2025-10-17 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2287, 'KM', '2025-10-17 00:00:00', '2025-10-17 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2288, 'WORK', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2289, 'ONCALL', '2025-10-20 00:00:00', '2025-10-20 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2290, 'KM', '2025-10-20 00:00:00', '2025-10-20 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2291, 'WORK', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2292, 'ONCALL', '2025-10-21 00:00:00', '2025-10-21 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2293, 'KM', '2025-10-21 00:00:00', '2025-10-21 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2294, 'WORK', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2295, 'ONCALL', '2025-10-22 00:00:00', '2025-10-22 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2296, 'KM', '2025-10-22 00:00:00', '2025-10-22 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2297, 'WORK', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2298, 'ONCALL', '2025-10-23 00:00:00', '2025-10-23 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2299, 'KM', '2025-10-23 00:00:00', '2025-10-23 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2300, 'WORK', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 480, NULL, 'approved', 'approval', NULL, 19, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2301, 'ONCALL', '2025-10-24 00:00:00', '2025-10-24 23:59:59', 180, NULL, 'approved', 'approval', NULL, 19, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2302, 'KM', '2025-10-24 00:00:00', '2025-10-24 23:59:59', NULL, 80.00, 'approved', 'approval', NULL, 19, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 10:00:49'),
(2303, 'WORK', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2304, 'ONCALL', '2025-10-27 00:00:00', '2025-10-27 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2305, 'KM', '2025-10-27 00:00:00', '2025-10-27 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2306, 'WORK', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2307, 'ONCALL', '2025-10-28 00:00:00', '2025-10-28 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2308, 'KM', '2025-10-28 00:00:00', '2025-10-28 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2309, 'WORK', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2310, 'ONCALL', '2025-10-29 00:00:00', '2025-10-29 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2311, 'KM', '2025-10-29 00:00:00', '2025-10-29 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2312, 'WORK', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2313, 'ONCALL', '2025-10-30 00:00:00', '2025-10-30 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2314, 'KM', '2025-10-30 00:00:00', '2025-10-30 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2315, 'WORK', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 480, NULL, 'draft', 'manual', NULL, NULL, 'WORK', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2316, 'ONCALL', '2025-10-31 00:00:00', '2025-10-31 23:59:59', 180, NULL, 'draft', 'manual', NULL, NULL, 'ONCALL', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43'),
(2317, 'KM', '2025-10-31 00:00:00', '2025-10-31 23:59:59', NULL, 80.00, 'draft', 'manual', NULL, NULL, 'KM', 62, '2025-10-17 09:59:43', '2025-10-17 09:59:43');

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

--
-- Extraindo dados da tabela `overtime`
--

INSERT INTO `overtime` (`id`, `user_id`, `inicio`, `fim`, `request_id`, `origem`, `criado_por`, `created_at`) VALUES
(1, 59, '2025-10-14 21:00:00', '2025-10-14 23:00:00', 2, 'approval', 65, '2025-10-20 11:14:12'),
(2, 63, '2025-10-14 21:00:00', '2025-10-14 23:00:00', 1, 'approval', 65, '2025-10-20 11:14:19');

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
(17, 21, 'ferias', '2025-11-15', '2025-11-15', 'Miocardite', NULL, 'pendente', '2025-09-15 18:59:20', NULL, NULL, 39),
(18, 63, 'ferias', '2025-10-14', '2025-10-14', 'Ferias Miguel', NULL, 'aprovado', '2025-10-16 12:10:33', 62, NULL, NULL),
(19, 63, 'ferias', '2025-08-01', '2025-08-10', 'livre', '/uploads/leaves/2025/10/Pagamentos-Simples-EasyPay-20251016125033-f591f0.pdf', 'aprovado', '2025-10-16 12:50:33', 62, 'Marcação direta RH', NULL),
(20, 63, 'ferias', '2025-10-14', '2025-10-14', 'Ferias Miguel', NULL, 'aprovado', '2025-10-17 09:33:14', 62, NULL, NULL);

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

--
-- Extraindo dados da tabela `request_overtime`
--

INSERT INTO `request_overtime` (`id`, `user_id`, `data_inicio`, `data_fim`, `justificacao`, `ficheiro`, `estado`, `criado_em`, `criado_por`, `decidido_por`, `decidido_em`, `comentario`) VALUES
(1, 63, '2025-10-14 21:00:00', '2025-10-14 23:00:00', 'Entrega urgente após horário', NULL, 'approved', '2025-10-17 13:00:14', 65, 65, '2025-10-20 11:14:19', 'opcional'),
(2, 59, '2025-10-14 21:00:00', '2025-10-14 23:00:00', 'Entrega urgente após horário', NULL, 'approved', '2025-10-20 11:13:14', 62, 65, '2025-10-20 11:14:12', 'opcional');

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
(17, 21, '2025-08-01', '2025-08-31', 'rejected', 22, '2025-09-12 11:01:40', 'faltam registos de 12/08 e 13/08', '2025-09-12 10:54:07', '2025-09-12 11:01:40'),
(18, 63, '2025-09-25', '2025-10-24', 'approved', 62, '2025-10-17 09:50:46', NULL, '2025-10-17 09:36:36', '2025-10-17 09:50:46'),
(19, 62, '2025-09-25', '2025-10-24', 'approved', 64, '2025-10-17 10:00:49', NULL, '2025-10-17 09:59:55', '2025-10-17 10:00:49');

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
(62, 'Nuno', 'nuno@gmail.com', '$2y$10$/Kx3sgc3z9vTzr/LLtZP1e0ovDfM3uM3wZbYz/S0NQq/C4qkO94YG', 'admin_rh', 1),
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
(12, 1, '2025-10-16 12:46:25'),
(62, 2, '2025-10-20 10:41:36'),
(62, 4, '2025-10-20 10:41:36'),
(64, 5, '2025-10-17 12:56:03'),
(65, 2, '2025-10-20 10:42:54'),
(65, 3, '2025-10-20 10:42:54'),
(65, 5, '2025-10-20 10:42:54');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2318;

--
-- AUTO_INCREMENT de tabela `finance_profiles`
--
ALTER TABLE `finance_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `overtime`
--
ALTER TABLE `overtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `pedidos_ferias`
--
ALTER TABLE `pedidos_ferias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `request_overtime`
--
ALTER TABLE `request_overtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `timesheet_periods`
--
ALTER TABLE `timesheet_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
