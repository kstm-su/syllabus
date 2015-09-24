<?php

include_once('./util.php');

$db = new DBAdmin();

/* トップページの取得 */
echo 'Downloading top page ... ';
$src = file_get_contents(SEARCH_URL);
echo PRINT_OK . PHP_EOL;
$html = htmlobject($src);
$tr = $html->body->form->table->tbody->tr->td->table->tbody->tr;
$year = (int)$tr[1]->td[1]->input['value'];
$departments = $tr[0]->td[1]->select->option;


/* 部局リストの更新 */
echo 'Updating `department` table ... ';
$db->begin();
foreach ($departments as $department) {
	$code = (string)$department['value'];
	if ($code) {
		$name = kana((string)$department);
		$db->insert('department', array(
			'department_code' => $code,
			'name' => $name
		));
	}
}
$db->commit();
echo PRINT_OK . PHP_EOL;

/* 講義データのリストを取得 */
echo 'Updating `list` table ... ';
$postdata = array(
	'MODE' => 0,
	'STARTNO' => 0,
	'NENDO' => $year,
	'CODE_JYOUKEN' => 0,
	'BtKENSAKU' => 0
);
for ($i = 0, $j = -1;; $j = -1) {
	$src = post_request(SEARCH_URL, $postdata);
	$html = htmlobject($src);
	$tr = $html->body->form->table[1]->tbody->tr;
	if (count($tr) <= 1) {
		echo "\033[27G\033[K$i " . PRINT_OK . PHP_EOL;
		break;
	}
	$db->begin();
	foreach ($tr as $row) {
		$td = $row->td;
		if (++$j === 0) {
			continue;
		}
		$q = preg_split('/&|=/', $td[3]->a['href']);
		$db->insert('list', array(
			'year' => $q[1],
			'department_code' => $q[3],
			'internal_code' => $q[5],
			'place' => $td[7]));
	}
	$db->commit();
	$postdata['BtKENSAKU'] = 1;
	if (isset($postdata['BtNEXT'])) {
		$postdata['STARTNO'] += $j;
	}
	$postdata['BtNEXT'] = 1;
	$i += $j;
	echo "\033[27G\033[K$i";
}

$db->close();
