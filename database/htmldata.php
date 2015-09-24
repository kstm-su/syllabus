<?php

include_once('./util.php');

$db = new DBAdmin();

echo 'Updating `htmldata` table ... ';
$q = $db->query('SELECT `id`, `html` FROM `raw`');
$db->begin();
while ($row = $q->fetch_assoc()) {
	/* CRLFをLFに統一 */
	$src = str_replace("\r\n", "\n", $row['html']);

	/* 文字コードを書き換え */
	$src = str_replace('charset=Shift_JIS','charset=utf-8', $src);

	/* HTMLをオブジェクトに変換 */
	$html = htmlobject($src);

	/* 抽出 */
	$data = array('id' => $row['id']);
	$tmp = $html->body->center->table->tbody->tr->td->div;
	$data['code'] = $tmp[1]->table->tbody->tr[0]->td[3];
	$data['public'] = (int)(strpos($tmp[0], '市民開放授業') !== FALSE);
	$data['ches'] = (int)(strpos($tmp[0], '県内大学') !== FALSE);
	$tmp = $tmp[1]->table->tbody->tr;
	$data['title'] = $tmp[1]->td[1];
	if (substr($data['code'], 0, 1) !== 'M') {
		$data['title_english'] = $tmp[2]->td;
		$data['teacher'] = $tmp[3]->td[1];
		$data['sub_teacher'] = $tmp[3]->td[3];
		$data['semester'] = $tmp[4]->td[1];
		$data['schedule'] = $tmp[4]->td[3];
		$data['classroom'] = $tmp[4]->td[5];
		$data['credit'] = $tmp[4]->td[7];
		$data['target'] = $tmp[5]->td[1];
		$data['style'] = $tmp[5]->td[3];
		$data['note'] = $tmp[5]->td[5];
	} else {
		$data['teacher'] = $tmp[2]->td[1];
		$data['semester'] = $tmp[3]->td[3];
		$data['schedule'] = $tmp[3]->td[5];
		$data['classroom'] = $tmp[4]->td[3];
		$data['credit'] = (double)kana($tmp[4]->td[1]);
		$data['target'] = $tmp[3]->td[1];
		$data['note'] = $tmp[1]->td[3];
	}

	$db->replace('htmldata', $data);
	echo "\033[31G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
