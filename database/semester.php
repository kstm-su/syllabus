<?php

include_once('../lib/util.php');

$db = new DBAdmin();

echo 'Updating `semester` table ... ';
$q = $db->selectAll('htmldata');
$db->begin();
while ($row = $q->fetch_assoc()) {
	$desc = trim(kana($row['semester']));
	if ($desc) {
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
	}
	$db->query('UPDATE `summary` SET `semester_id` =
		(SELECT `semester_id` FROM `semester` WHERE `description` = ?)
		WHERE `id` = ?', $desc, $row['id']);
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
