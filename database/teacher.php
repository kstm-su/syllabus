<?php

/* データベースに接続 */
include_once('./db.php');
$db = new DBAdmin();

$db->truncate('teacher');
$db->truncate('sub_teacher');

function insertSubTeacher($db, $id, $teacher) {
	$teacher = $db->escape($teacher);
	$q = $db->query("SELECT `teacher_id` FROM `teacher` WHERE `name` = '$teacher'");
	if ($q) {
		$res = $q->fetch_assoc();
		if ($res) {
			$db->insert('sub_teacher', array($id, $res['teacher_id']));
		}
	}
}

$table = $db->selectAll('htmldata');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$teacher = trim($row['teacher']);
	if ($teacher) {
		$teachers = preg_split('/　　|，| (?![A-Z])/', $teacher);
		foreach ($teachers as $i => $teacher) {
			$teacher = trim(mb_convert_kana($teacher, 'asKV'));
			if ($teacher && $teacher !== '他') {
				$db->insert('teacher', array(NULL, $teacher));
				if ($i) {
					insertSubTeacher($db, $row['id'], $teacher);
				}
			}
		}
	}
	$sub = $row['sub_teacher'];
	$sub = mb_convert_kana($sub, 'asKV');
	foreach(explode('・', $sub) as $teacher) {
		$teacher = trim($teacher);
		if ($teacher) {
			$db->insert('teacher', array(NULL, $teacher));
			insertSubTeacher($db, $row['id'], $teacher);
		}
	}
}
$db->commit();
$db->close();
