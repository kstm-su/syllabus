<?php

include_once('../lib/util.php');

$db = new DBAdmin();

echo 'Updating `semester` table ... ';
$q = $db->query('SELECT DISTINCT semester FROM htmldata');
$db->begin();
while ($row = $q->fetch_assoc()) {
	$desc = trim(kana($row['semester']));
	if (!$desc) {
		continue;
	}
	$full = strpos($desc, '通年') !== FALSE;
	$first = $full || strpos($desc, '前期') !== FALSE;
	$second = $full || strpos($desc, '後期') !== FALSE;
	$intensive = strpos($desc, '集中') !== FALSE;
	$db->insert('semester', array(
		'first' => $first,
		'second' => $second,
		'intensive' => $intensive,
		'description' => $desc
	));
	$id = $db->query('SELECT semester_id FROM semester WHERE description = ?', $desc)->fetch_assoc()['semester_id'];
	$ids = array_map(function($t) {
		return $t[0];
	}, $db->query('SELECT id FROM htmldata WHERE semester = ?', $row['semester'])->fetch_all());
	$db->query('UPDATE `summary` SET `semester_id` = ? WHERE `id` IN (?)', $id, $ids);
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
