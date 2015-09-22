<?php

include_once('./util.php');

$db = new DBAdmin();

echo 'Updating `semester` table ... ';
$q = $db->selectAll('semester');
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
	$s = $db->query("SELECT `semester_id` FROM `semester` WHERE `description` = '$desc'");
	$sid = $s->fetch_assoc()['semester_id'];
	$sid = is_null($sid) ? 'NULL' : "'" . $db->escape($sid) . "'";
	$db->query("UPDATE `summary` SET `semester_id` = $sid WHERE `id` = '{$row['id']}'");
	echo "\033[31G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
