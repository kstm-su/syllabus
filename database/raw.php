<?php

include_once('./util.php');

$db = new DBAdmin();

$begin = isset($argv[1]) ? (int)$argv[1] : 0;
$end = isset($argv[2]) ? $argv[2] : NULL;

echo 'Updating `raw` table ... ';
$q = $db->selectAll('list');
while ($row = $q->fetch_assoc()) {
	if ($begin <= $row['id'] && (is_null($end) || $row['id'] <= $end)) {
		$query = "?NENDO={$row['year']}&BUKYOKU={$row['department_code']}&CODE={$row['internal_code']}";
		$html = sjis2utf(file_get_contents(HTML_URL . $query));
		$text = sjis2utf(file_get_contents(TEXT_URL . $query));
		$db->replace('raw', array(
			'id' => $row['id'],
			'html' => $html,
			'text' => $text
		));
	}
	echo "\033[26G\033[K{$row['id']}";
}

$db->close();
echo " " . PRINT_OK . PHP_EOL;
