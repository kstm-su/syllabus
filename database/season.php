<?php

include_once('./util.php');
include_once('./db.php');

$db = new DBAdmin();

$q = $db->query('SELECT DISTINCT `season` FROM `htmldata`');
$db->begin();
while ($row = $q->fetch_assoc()) {
	$desc = trim(kana($row['season']));
	$full = strpos($desc, '通年') !== FALSE;
	$spring = $full || strpos($desc, '前期') !== FALSE;
	$autumn = $full || strpos($desc, '後期') !== FALSE;
	$intensive = strpos($desc, '集中') !== FALSE;
	$db->insert('season', array('description' => $desc,
		'spring' => (int)$spring, 'autumn' => (int)$autumn,
		'intensive' => (int)$intensive));
}
$db->commit();
$db->close();
