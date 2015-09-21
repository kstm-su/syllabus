-- phpMyAdmin SQL Dump
-- version 4.0.10.10
-- http://www.phpmyadmin.net
--
-- ホスト: localhost
-- 生成日時: 2015 年 9 月 18 日 09:48
-- サーバのバージョン: 5.6.26
-- PHP のバージョン: 5.6.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- データベース: `syllabus`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `department`
--

CREATE TABLE IF NOT EXISTS `department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `htmldata`
--

CREATE TABLE IF NOT EXISTS `htmldata` (
  `id` int(11) NOT NULL COMMENT '全体のID',
  `code` varchar(255) NOT NULL COMMENT '登録コード',
  `subject` text NOT NULL COMMENT '授業名',
  `subject_english` text COMMENT '授業名(英語)',
  `teacher` text NOT NULL COMMENT '担当教員',
  `sub_teacher` text COMMENT '副担当',
  `season` varchar(255) NOT NULL COMMENT '講義期間',
  `schedule` varchar(255) NOT NULL COMMENT '曜日・時限',
  `location` text NOT NULL COMMENT '講義室',
  `unit` float NOT NULL COMMENT '単位数',
  `target` text NOT NULL COMMENT '対象学生',
  `style` varchar(255) DEFAULT NULL COMMENT '授業形態',
  `note` text COMMENT '備考',
  `public` tinyint(1) NOT NULL COMMENT '市民開放授業',
  `ches` tinyint(1) NOT NULL COMMENT '県内大学履修科目',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `list`
--
CREATE TABLE IF NOT EXISTS `list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `department` varchar(8) NOT NULL,
  `code` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`,`department`,`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `rawhtml`
--

CREATE TABLE IF NOT EXISTS `rawhtml` (
  `id` int(11) NOT NULL,
  `raw` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `rawtext`
--

CREATE TABLE IF NOT EXISTS `rawtext` (
  `id` int(11) NOT NULL,
  `raw` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL COMMENT '授業ID',
  `day` int(3) DEFAULT NULL COMMENT '曜日(日:1 - 土:7)',
  `period` float DEFAULT NULL COMMENT '時限',
  `early` tinyint(1) NOT NULL DEFAULT '0' COMMENT '前半',
  `late` tinyint(1) NOT NULL DEFAULT '0' COMMENT '後半',
  `intensive` tinyint(1) NOT NULL DEFAULT '0' COMMENT '集中',
  `irregular` tinyint(1) NOT NULL DEFAULT '0' COMMENT '不定',
  `description` text NOT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `season`
--

CREATE TABLE IF NOT EXISTS `season` (
  `season_id` int(11) NOT NULL AUTO_INCREMENT,
  `spring` tinyint(1) NOT NULL COMMENT '前期',
  `autumn` tinyint(1) NOT NULL COMMENT '後期',
  `intensive` tinyint(1) NOT NULL COMMENT '集中',
  `description` varchar(127) NOT NULL,
  PRIMARY KEY (`season_id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `style`
--

CREATE TABLE IF NOT EXISTS `style` (
  `style_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(64) NOT NULL,
  PRIMARY KEY (`style_id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `subject`
--

CREATE TABLE IF NOT EXISTS `subject` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(127) NOT NULL,
  `english` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `sub_teacher`
--

CREATE TABLE IF NOT EXISTS `sub_teacher` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`, `teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `summary`
--

CREATE TABLE IF NOT EXISTS `summary` (
  `id` int(11) NOT NULL COMMENT 'マスタID',
  `code` varchar(64) NOT NULL COMMENT '履修コード',
  `subject_id` int(11) NOT NULL COMMENT '授業名ID',
  `subject_english_id` int(11) DEFAULT NULL COMMENT '授業名ID(英語)',
  `teacher_id` int(11) DEFAULT NULL COMMENT '主担当教員ID',
  `season_id` int(11) DEFAULT NULL COMMENT '期間ID',
  `unit` double NOT NULL COMMENT '単位数',
  `style_id` int(11) DEFAULT NULL COMMENT '授業形態ID',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `subject_id` (`subject_id`),
  KEY `subject_english_id` (`subject_english_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `season_id` (`season_id`),
  KEY `unit` (`unit`),
  KEY `style` (`style_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `teacher`
--

CREATE TABLE IF NOT EXISTS `teacher` (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(127) NOT NULL,
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- テーブルの構造 `textdata`
--

CREATE TABLE IF NOT EXISTS `textdata` (
  `textdata_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'このテーブル専用のID',
  `id` int(11) NOT NULL COMMENT 'rawtextのid',
  `key` varchar(127) NOT NULL,
  `value` text,
  PRIMARY KEY (`textdata_id`),
  UNIQUE KEY `id` (`id`,`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='rawtextとkeyとvalueのペアに整形したもの' ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
