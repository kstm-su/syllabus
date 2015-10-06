<?php

include_once('../lib/util.php');

$db = new DBGuest();

header('Content-Type: application/json');
$res = array();

$keys = array_keys($_REQUEST);
if (count($keys) > 0) {
	$key = $keys[0];
	$value = $_REQUEST[$key];
	if ($value) {
		switch ($key) {
		case 'title':
			$res = $db->fetchAll('SELECT DISTINCT `title` FROM `summary`
				WHERE `title` LIKE ? UNION SELECT `title_english` FROM `summary`
				WHERE `title_english` LIKE ? LIMIT 30',
				$value . '%', $value . '%');
			break;
		case 'teacher':
			$res = $db->fetchAll('SELECT `name` FROM `staff` WHERE `name` LIKE ? LIMIT 30', $value . '%');
			break;
		case 'code':
			$res = $db->fetchAll('SELECT `code` FROM `summary` WHERE `code` LIKE ? LIMIT 30', $value . '%');
			break;
		}
	}
}

echo json_encode($res);


