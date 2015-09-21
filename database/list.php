<?php

include_once('./util.php');
include_once('./db.php');

$db = new DBAdmin();

/* 年度の取得と部局リストの更新 */
$src = file_get_contents(SEARCH_URL);
$html = htmlobject($src);
$year = (int)$html->body->form->table->tbody->tr->td->table->tbody->tr[1]->td[1]->input['value'];
$departments = $html->body->form->table->tbody->tr->td->table->tbody->tr[0]->td[1]->select->option;
$db->begin();
foreach ($departments as $department) {
	$code = (string)$department['value'];
	if ($code) {
		$name = kana((string)$department);
		$db->insert('department', array('code' => $code, 'name' => $name));
	}
}
$db->commit();

/* 講義データのリストを取得 */
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
		echo "\033[9D\033[2K$i\n`list` is updated.\n";
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
			'department' => $q[3],
			'code' => $q[5],
			'place' => $td[7]));
	}
	$db->commit();
	$postdata['BtKENSAKU'] = 1;
	if (isset($postdata['BtNEXT'])) {
		$postdata['STARTNO'] += $j;
	}
	$postdata['BtNEXT'] = 1;
	$i += $j;
	echo "\033[9D\033[2K$i";
}

$db->close();
