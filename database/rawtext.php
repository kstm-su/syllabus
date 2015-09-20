<?php

include_once('./db.php');

$db = new DBAdmin();

$q = $db->selectAll('list');
while ($row = $q->fetch_assoc()) {
	$raw = file_get_contents(TEXT_URL . "?NENDO={$row['year']}&BUKYOKU={$row['department']}&CODE={$row['code']}");
	$html = $db->escape(mb_convert_encoding($raw, 'utf8', 'cp932'));
	$db->replace('rawtext', array('id' => $row['id'], 'raw' => $html));
	echo "\033[9D\033[2K{$row['id']}";
}

$db->close();
