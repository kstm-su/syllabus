<?php

include_once('../lib/util.php');

	$db = new DBGuest();
if (isset($_REQUEST['title']) && $_REQUEST['title']) {
	$res = $db->fetchAll('SELECT `title` FROM `summary`
		WHERE `title` LIKE ? UNION SELECT `title_english` FROM `summary`
		WHERE `title_english` LIKE ?',
		$_REQUEST['title'] . '%', $_REQUEST['title'] . '%');
} else {
	$res = NULL;
}

header('Content-Type: application/json');
echo json_encode($res);
