<?php

include_once('./util.php');

$db = new DBAdmin();

echo 'Updating `summary` table ... ';
$table = $db->query('SELECT `list`.`id` as `id`, `list`.`year` as `year`, `department`.`department_id` as `department_id`, `htmldata`.`code` as `code`, `htmldata`.`title` as `title`, `htmldata`.`title_english` as `title_english`, `htmldata`.`credit` as `credit`, `htmldata`.`target` as `target`, `htmldata`.`style` as `style`, `htmldata`.`note` as `note` FROM `list` LEFT JOIN `htmldata` ON `list`.`id` = `htmldata`.`id` LEFT JOIN `department` ON `list`.`department_code` = `department`.`department_code` ORDER BY `list`.`id`');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$row = array_map(function($s){
		return trim(kana($s));
	}, $row);
	if ($row['code'] === '') {
		$row['code'] = NULL;
	}
	$db->insert('summary', $row);
	echo "\033[30G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
