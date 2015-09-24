<?php

include_once('./util.php');

$db = new DBAdmin();

echo 'Updating `summary` table ... ';
$table = $db->query('SELECT `list`.`id`, `list`.`year`,
	`department`.`department_id`, `htmldata`.`code`, `htmldata`.`title`,
	`htmldata`.`title_english`, `htmldata`.`credit`, `htmldata`.`target`,
	`htmldata`.`style`, `htmldata`.`note`, `htmldata`.`public`,
	`htmldata`.`ches` FROM `list` LEFT JOIN `htmldata`
	ON `list`.`id` = `htmldata`.`id` LEFT JOIN `department`
	ON `list`.`department_code` = `department`.`department_code`
	ORDER BY `list`.`id`');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$row = array_map(function($s){
		return trim(kana($s));
	}, $row);
	if ($row['code'] === '') {
		$row['code'] = NULL;
	}
	$db->replace('summary', $row);
	echo "\033[30G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
