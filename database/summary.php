<?php

function kana($str) {
	return mb_convert_kana($str, 'asKV');
}

/* データベースに接続 */
include_once('./db.php');
$db = new DBAdmin();

$db->truncate('summary');

$table = $db->selectAll('htmldata');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$subject = $db->query("SELECT `subject_id` FROM `subject` WHERE `name` = '{$db->escape(kana($row['subject']))}' and `english` = '0'")->fetch_assoc();
	$subject_eng = $db->query("SELECT `subject_id` FROM `subject` WHERE `name` = '{$db->escape(kana($row['subject_english']))}' and `english` = '1'")->fetch_assoc();
	$teacher = $db->query("SELECT `teacher_id` FROM `teacher` WHERE `name` = '{$db->escape(kana($row['teacher']))}'")->fetch_assoc();
	$season = $db->query("SELECT `season_id` FROM `season` WHERE `description` = '{$db->escape(kana($row['season']))}'")->fetch_assoc();
	$style = $db->query("SELECT `style_id` FROM `style` WHERE `description` = '{$db->escape(kana($row['style']))}'")->fetch_assoc();
	$data = array(
		$row['id'],
		$row['code'],
		$subject['subject_id'],
		$subject_eng['subject_id'],
		$teacher['teacher_id'],
		$season['season_id'],
		$row['unit'],
		$style['style_id']
	);
	$db->insert('summary', $data);
}
$db->commit();
$db->close();
