<?php

/* データベースに接続 */
include_once('./db.php');
$db = new DBAdmin();

$table = $db->selectAll('htmldata');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$subject = trim(mb_convert_kana($row['subject'], 'asKV'));
	if ($subject) {
		$db->insert('subject', array(NULL, $subject, 0));
	}
	$subject = trim(mb_convert_kana($row['subject_english'], 'asKV'));
	if ($subject) {
		$db->insert('subject', array(NULL, $subject, 1));
	}
}
$db->commit();
$db->close();
