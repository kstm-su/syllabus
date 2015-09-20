<?php

include_once('./util.php');
include_once('./db.php');

$db = new DBAdmin();

$table = $db->selectAll('htmldata');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$subject = trim(kana($row['subject']));
	if ($subject) {
		$db->insert('subject', array('name' => $subject));
	}
	$subject = trim(kana($row['subject_english']));
	if ($subject) {
		$db->insert('subject', array('name' => $subject, 'english' => 1));
	}
}
$db->commit();
$db->close();
