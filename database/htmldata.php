<?php

include_once('./db.php');

$db = new DBAdmin();

$table = $db->selectAll('rawhtml');
$db->begin();
while ($row = $table->fetch_assoc()) {
	/* CRLFをLFに統一 */
	$html = str_replace("\r\n", "\n", $row['raw']);

	/* 文字コードを書き換え */
	$html = str_replace('charset=Shift_JIS','charset=utf-8', $html);

	/* HTMLをオブジェクトに変換 */
	$xml = htmlobject($src);

	/* 抽出 */
	$data = array('id' => $row['id']);
	$tmp = $xml->body->center->table->tbody->tr->td->div;
	$data['code'] = $tmp[1]->table->tbody->tr[0]->td[3];
	$data['public'] = (int)(strpos($tmp[0], '市民開放授業') !== FALSE);
	$data['ches'] = (int)(strpos($tmp[0], '県内大学') !== FALSE);
	$tmp = $tmp[1]->table->tbody->tr;
	if (substr($data['code'], 0, 1) !== 'M') {
		$data['subject'] = $tmp[1]->td[1];
		$data['subject_english'] = $tmp[2]->td;
		$data['teacher'] = $tmp[3]->td[1];
		$data['sub_teacher'] = $tmp[3]->td[3];
		$data['season'] = $tmp[4]->td[1];
		$data['schedule'] = $tmp[4]->td[3];
		$data['location'] = $tmp[4]->td[5];
		$data['unit'] = $tmp[4]->td[7];
		$data['target'] = $tmp[5]->td[1];
		$data['style'] = $tmp[5]->td[3];
		$data['note'] = $tmp[5]->td[5];
	} else {
		$data['subject'] = $tmp[1]->td[1];
		$data['subject_english'] = NULL;
		$data['teacher'] = $tmp[2]->td[1];
		$data['sub_teacher'] = NULL;
		$data['season'] = $tmp[3]->td[3];
		$data['schedule'] = $tmp[3]->td[5];
		$data['location'] = $tmp[4]->td[3];
		$data['unit'] = (double)mb_convert_kana($tmp[4]->td[1], 'asKV');
		$data['target'] = $tmp[3]->td[1];
		$data['style'] = NULL;
		$data['note'] = $tmp[1]->td[3];
	}

	$db->replace('htmldata', $data);
	echo "\033[9D\033[2K$i";
}
echo PHP_EOL;
$db->commit();
$db->close();
