<?php

include_once('./db.php');

$db = new DBAdmin();

$begin = isset($argv[1]) ? (int)$argv[1] : 0;
$end = isset($argv[2]) ? $argv[2] : NULL;

$q = $db->selectAll('list');
while ($row = $q->fetch_assoc()) {
	if ($begin <= $row['id'] && (is_null($end) || $row['id'] <= $end)) {
		$raw = file_get_contents(HTML_URL . "?NENDO={$row['year']}&BUKYOKU={$row['department']}&CODE={$row['code']}");
		$html = mb_convert_encoding($raw, 'utf8', 'cp932');
		$db->replace('rawhtml', array($row['id'], $html));
		echo "\033[9D\033[2K{$row['id']}";
	}
}

$db->close();
echo PHP_EOL;
