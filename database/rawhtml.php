<?php

include_once('./db.php');

$db = new DBAdmin();
$db->truncate('rawhtml');

$q = $db->selectAll('list');

while ($row = $q->fetch_assoc()) {
	$raw = file_get_contents("https://campus-2.shinshu-u.ac.jp/syllabus/syllabus.dll/Display?{$row['query']}");
	$text = $db->escape(mb_convert_encoding($raw, 'utf8', 'cp932'));
	$db->insert('rawhtml', array($row['id'], $text));
}

$db->close();
