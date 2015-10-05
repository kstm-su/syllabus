<?php

include_once('../lib/util.php');

$db = new DBGuest();
$res = $db->fetchAll('SELECT * from `department`');

header('Content-Type: application/json');
echo json_encode($res);
