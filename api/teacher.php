<?php

include_once('../lib/util.php');

	$db = new DBGuest();
if (isset($_REQUEST['teacher']) && $_REQUEST['teacher']) {
	$res = $db->fetchAll('SELECT `name` from `staff` WHERE `name` LIKE ?',
		$_REQUEST['teacher'] . '%');
} else {
	$res = NULL;
}

header('Content-Type: application/json');
echo json_encode($res);

