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
		`time` INT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;

CREATE TABLE `questions_answers`
	(
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`id_question` INT,
		`id_patient` INT,
		`answer` INT,
		`time` INT,
		`date` TINYTEXT
	)
Engine=Aria
DEFAULT CHARSET=utf8mb4;