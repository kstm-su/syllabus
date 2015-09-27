CREATE TABLE IF NOT EXISTS `config` (
	`config_id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(127) NOT NULL,
	`value` varchar(127) NOT NULL,
	PRIMARY KEY (`config_id`),
	UNIQUE `uniq` (`name`, `value`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '設定情報一覧';

CREATE TABLE IF NOT EXISTS `list` (
	`id` int(11) NOT NULL AUTO_INCREMENT COMMENT '授業ID',
	`year` int(11) NOT NULL COMMENT '開講年度',
	`department_code` varchar(8) NOT NULL COMMENT '開講部局コード',
	`internal_code` varchar(127) NOT NULL COMMENT 'URL用の内部授業コード',
	`place` varchar(127) COMMENT '開講場所',
	PRIMARY KEY (`id`),
	UNIQUE `uniq` (`year`, `department_code`, `internal_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '/Search から取れるクローリング用の情報等';

CREATE TABLE IF NOT EXISTS `department` (
	`department_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '部局ID',
	`department_code` varchar(8) NOT NULL COMMENT '部局コード',
	`name` varchar(127) NOT NULL COMMENT '部局名',
	PRIMARY KEY (`department_id`),
	UNIQUE `code` (`department_code`),
	UNIQUE `name` (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '部局リスト';

CREATE TABLE IF NOT EXISTS `raw` (
	`id` int(11) NOT NULL COMMENT '授業ID',
	`html` longtext NOT NULL COMMENT '/Display から取れるデータ',
	`text` longtext NOT NULL COMMENT '/Text から取れるデータ',
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'クローリングしたシラバス情報を入れるテーブル';

CREATE TABLE IF NOT EXISTS `htmldata` (
	`id` int(11) NOT NULL COMMENT '授業ID',
	`code` varchar(16) NOT NULL COMMENT '授業コード(履修登録用)',
	`title` text NOT NULL COMMENT '授業名',
	`title_english` text COMMENT '授業名(英語)',
	`teacher` text NOT NULL COMMENT '担当教員',
	`sub_teacher` text COMMENT '副担当教員',
	`semester` varchar(127) NOT NULL COMMENT '開講期間',
	`schedule` text NOT NULL COMMENT '曜日時限',
	`classroom` text NOT NULL COMMENT '講義室',
	`credit` varchar(8) NOT NULL COMMENT '単位数',
	`target` text NOT NULL COMMENT '対象学生',
	`style` varchar(255) COMMENT '授業形態',
	`note` text COMMENT '備考',
	`public` tinyint(1) NOT NULL COMMENT '市民開放授業',
	`ches` tinyint(1) NOT NULL COMMENT '県内大学履修科目',
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'HTMLから抜き出した情報を入れるテーブル';

CREATE TABLE IF NOT EXISTS `textdata` (
	`textdata_id` int(11) NOT NULL AUTO_INCREMENT,
	`id` int(11) NOT NULL COMMENT '授業ID',
	`key` varchar(127) NOT NULL COMMENT '項目名',
	`value` text COMMENT '値',
	PRIMARY KEY (`textdata_id`),
	UNIQUE `uniq` (`id`, `key`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = 'テキストデータから抜き出した情報を入れるテーブル';

CREATE TABLE IF NOT EXISTS `summary` (
	`id` int(11) NOT NULL COMMENT '授業ID',
	`year` int(11) NOT NULL COMMENT '開講年度',
	`code` varchar(16) COMMENT '授業コード(履修登録用)',
	`department_id` int(11) NOT NULL COMMENT '開講部局ID',
	`title` varchar(127) NOT NULL COMMENT '授業名',
	`title_english` varchar(127) COMMENT '授業名(英語)',
	`semester_id` int(11) COMMENT '開講時期ID',
	`credit` float NOT NULL COMMENT '単位数',
	`target` varchar(255) COMMENT '対象学生',
	`style` varchar(255) COMMENT '授業形態',
	`note` text COMMENT '備考',
	`public` tinyint(1) NOT NULL COMMENT '市民開放授業',
	`ches` tinyint(1) NOT NULL COMMENT '県内大学履修科目',
	PRIMARY KEY (`id`),
	UNIQUE `uniq` (`year`, `code`),
	INDEX `title` (`title`),
	INDEX `title_english` (`title_english`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '主にhtmldataを元に整理したテーブル';

CREATE TABLE IF NOT EXISTS `staff` (
	`staff_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '教員ID',
	`name` varchar(127) NOT NULL COMMENT '教員名',
	PRIMARY KEY (`staff_id`),
	UNIQUE `name` (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '教員一覧';

CREATE TABLE IF NOT EXISTS `teacher` (
	`teacher_id` int(11) NOT NULL AUTO_INCREMENT,
	`id` int(11) NOT NULL COMMENT '授業ID',
	`staff_id` int(11) NOT NULL COMMENT '教員ID',
	`main` tinyint(1) NOT NULL COMMENT '主担当教員',
	PRIMARY KEY (`teacher_id`),
	UNIQUE (`id`, `staff_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '授業の担当教員';

CREATE TABLE IF NOT EXISTS `semester` (
	`semester_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '開講時期ID',
	`first` tinyint(1) NOT NULL COMMENT '前期',
	`second` tinyint(1) NOT NULL COMMENT '後期',
	`intensive` tinyint(1) NOT NULL COMMENT '集中',
	`description` varchar(127) NOT NULL,
	PRIMARY KEY (`semester_id`),
	UNIQUE `description` (`description`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '開講時期リスト';

CREATE TABLE IF NOT EXISTS `schedule` (
	`schedule_id` int(11) NOT NULL AUTO_INCREMENT,
	`id` int(11) NOT NULL COMMENT '授業ID',
	`day` int(11) COMMENT '曜日 (0:日 - 6:土)',
	`period` int(11) COMMENT '時限',
	`early` tinyint(1) NOT NULL COMMENT '前半',
	`late` tinyint(1) NOT NULL COMMENT '後半',
	`intensive` tinyint(1) NOT NULL COMMENT '集中',
	`irregular` tinyint(1) NOT NULL COMMENT '不定',
	`description` text,
	PRIMARY KEY (`schedule_id`),
	UNIQUE `uniq` (`id`, `day`, `period`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '授業の曜日時限';

CREATE TABLE IF NOT EXISTS `room` (
	`room_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '部屋ID',
	`department_id` int(11) COMMENT '部局ID',
	`name` varchar(127) NOT NULL COMMENT '部屋名',
	PRIMARY KEY (`room_id`),
	UNIQUE `uniq` (`department_id`, `name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '部屋リスト';

CREATE TABLE IF NOT EXISTS `classroom` (
	`classroom_id` int(11) NOT NULL AUTO_INCREMENT,
	`id` int(11) NOT NULL COMMENT '授業ID',
	`room_id` int(11) NOT NULL COMMENT '部屋ID',
	PRIMARY KEY (`classroom_id`),
	UNIQUE `uniq` (`id`, `room_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '講義室';

CREATE TABLE IF NOT EXISTS `json` (
	`id` int(11) NOT NULL COMMENT '授業ID',
	`json` longtext NOT NULL COMMENT '表示用JSON',
	PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COMMENT = '結果表示用テーブル';
