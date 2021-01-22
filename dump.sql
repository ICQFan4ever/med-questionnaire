CREATE TABLE `areas`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`title` TINYTEXT,
		`tg_id` TINYTEXT,
		`deleted` TINYINT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `doctors`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`id_area` INT,
		`level` TINYINT,
		`login` TINYTEXT,
		`password` TINYTEXT,
		`session` TINYTEXT,
		`name` TINYTEXT,
		`phone` TINYTEXT,
		`tg_id` BIGINT,
		`last_seen` INT,
		`deleted` TINYINT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `patients`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`id_area` INT,
		`name` TINYTEXT,
		`sid` TINYTEXT,
		`phone` TINYTEXT,
		`birth_date` TINYTEXT,
		`deleted` TINYINT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `questions`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`text` MEDIUMTEXT,
		`positive` tinytext,
		`negative` tinytext,
		`alert` TINYINT,
		`priority` INT,
		`draft` TINYINT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reports`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`id_patient` INT,
		`date` TINYTEXT,
		`time` INT,
		`alert` TINYINT,
		`by_doctor` INT DEFAULT '0'
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `questions_answers`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`id_report` INT,
		`id_question` INT,
		`id_patient` INT,
		`id_report` INT,
		`answer` INT,
		`time` INT,
		`date` TINYTEXT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `log`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`text` TEXT,
		`time` INT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;