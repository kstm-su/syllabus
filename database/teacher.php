<?php

include_once('../lib/util.php');

$db = new DBAdmin();

function insertTeacher($db, $id, $name, $main) {
	$q = $db->query('SELECT `staff_id` FROM `staff` WHERE `name` = ?', $name);
	if ($q) {
		$res = $q->fetch_assoc();
		if ($res) {
			$db->insert('teacher', array(
				'id' => $id,
				'staff_id' => $res['staff_id'],
				'main' => $main
			));
		}
	}
}

echo 'Updating `teacher` table ... ';
$q = $db->selectAll('htmldata');
$db->begin();
while ($row = $q->fetch_assoc()) {
	$teacher = trim($row['teacher']);
	if ($teacher) {
		$teachers = preg_split('/　　|，| (?![A-Z])/', $teacher);
		foreach ($teachers as $i => $teacher) {
			$teacher = trim(kana($teacher));
			if ($teacher && $teacher !== '他') {
				$db->insert('staff', array('name' => $teacher));
				insertTeacher($db, $row['id'], $teacher, !$i);
			}
		}
	}
	$sub = $row['sub_teacher'];
	$sub = kana($sub);
	foreach(explode('・', $sub) as $teacher) {
		$teacher = trim($teacher);
		if ($teacher) {
			$db->insert('staff', array('name' => $teacher));
			insertTeacher($db, $row['id'], $teacher, FALSE);
		}
	}
	echo "\033[30G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
