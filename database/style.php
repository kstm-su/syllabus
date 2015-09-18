<?php

/* データベースに接続 */
include_once('./db.php');
$db = new DBAdmin();

$db->truncate('style');

$table = $db->selectAll('htmldata');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$style = trim(mb_convert_kana($row['style'], 'asKV'));
	if ($style) {
		$db->insert('style', array(NULL, $style));
	}
}
$db->commit();
$db->close();
