CREATE TABLE IF NOT EXISTS `list` (
	`id` int(11) NOT NULL AUTO_INCREMENT COMMENT '授業ID',
	`year` int(11) NOT NULL COMMENT '年度',
	`department_code` varchar(8) NOT NULL COMMENT '開講部局コード',
	`internal_code` varchar(64) NOT NULL COMMENT 'URL用の内部授業コード',
	`place` varchar(64) COMMENT '開講場所',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq` (`year`, `department_code`, `internal_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '/Search から取れるクローリング用の情報等';

CREATE TABLE IF NOT EXISTS `raw` (
	`id` int(11) NOT NULL COMMENT '授業ID',
	`html` longtext NOT NULL COMMENT '/Display から取れるデータ',
	`text` longtext NOT NULL COMMENT '/Text から取れるデータ',
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'クローリングしたシラバス情報を入れるテーブル';

CREATE TABLE IF NOT EXISTS `htmldata` (
	`id` int(11) NOT NULL COMMENT '授業ID',
	`code` varchar(255) NOT NULL COMMENT '授業コード(履修登録用)',
	`title` text NOT NULL COMMENT '授業名',
	`title_english` text COMMENT '授業名(英語)',
	`teacher` text NOT NULL COMMENT '担当教員',
	`sub_teacher` text COMMENT '副担当教員',
	`semester` varchar(255) NOT NULL COMMENT '開講期間',
	`schedule` varchar(255) NOT NULL COMMENT '曜日時限',
	`classroom` text NOT NULL COMMENT '講義室',
	`credit` varchar(255) NOT NULL COMMENT '単位数',
	`target` text NOT NULL COMMENT '対象学生',
	`style` varchar(255) COMMENT '授業形態',
	`note` text COMMENT '備考',
	`public` tinyint(1) NOT NULL COMMENT '市民開放授業',
	`ches` tinyint(1) NOT NULL COMMENT '県内大学履修科目',
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'HTMLから抜き出した情報を入れるテーブル';

CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
CREATE TABLE IF NOT EXISTS `` () ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '';
