<?php

include_once('./db.php');

$db = new DBAdmin();

$q = $db->selectAll('list');
while ($row = $q->fetch_assoc()) {
	$raw = file_get_contents(HTML_URL . "?NENDO={$row['year']}&BUKYOKU={$row['department']}&CODE={$row['code']}");
	$text = $db->escape(mb_convert_encoding($raw, 'utf8', 'cp932'));
	$db->replace('rawhtml', array($row['id'], $text));
	echo "\033[9D\033[2K{$row['id']}";
}

$db->close();
