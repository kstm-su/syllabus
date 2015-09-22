<?php

include_once('./util.php');

$db = new DBAdmin();
$db->truncate('classroom');

echo 'Updating `classroom` table ... ';
$q = $db->query('SELECT `list`.`id`, `list`.`place`, `htmldata`.`classroom` FROM `list` JOIN `htmldata` ON `list`.`id` = `htmldata`.`id`');
$db->begin();
while ($row = $q->fetch_assoc()) {
	$place = $db->escape(kana($row['place']));
	$rooms = trim(kana($row['classroom']));
	$r = $db->query("SELECT `department_id` FROM `department` WHERE `name` = '$place'");
	$did = $r->fetch_assoc()['department_id'];
	if (is_null($did)) {
		if ($place) {
			$res = $db->query('SELECT `department_id` FROM `department` WHERE '
				. implode(' AND ', array_map(function($s) use ($db) {
					return "`name` LIKE '%" . $db->escape($s) . "%'";
				}, preg_split('/\(|\)/', $place))));
			$did = $res->fetch_assoc()['department_id'];
		}
	}
	foreach (explode(' ', $rooms) as $room) {
		if (is_null($did) && !$room) {
			continue;
		}
		$db->insert('room', array('department_id' => $did, 'name' => $room));
		$did = $db->escape($did);
		$room = $db->escape($room);
		$r = $db->query("SELECT `room_id` FROM `room` WHERE `department_id` = '$did' AND `name` = '$room'");
		$rid = $r->fetch_assoc()['room_id'];
		$db->insert('classroom', array(
			'id' => $row['id'],
			'room_id' => $rid
		));
	}
	echo "\033[32G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
