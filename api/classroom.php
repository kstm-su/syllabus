<?php

include_once('../lib/util.php');

	$db = new DBGuest();
if (isset($_REQUEST['department_id']) && $_REQUEST['department_id']) {
	$res = $db->fetchAll('SELECT `room_id`, `name` from `room` WHERE `department_id` = ?',
		$_REQUEST['department_id']);
} else {
	$res = $db->fetchAll('SELECT `room_id`, `name` from `room`');
}

header('Content-Type: application/json');
echo json_encode($res);
