<?php

include_once('./db.php');

$db = new DBAdmin();

/* テーブル初期化 */
$db->truncate('htmldata');

/* DOMのパース処理時は警告を無視 */
libxml_use_internal_errors(true);

$table = $db->selectAll('rawhtml');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$data = array(NULL, $row['id']);
	$data = array_pad($data, 16, NULL);

	/* CRLFをLFに統一 */
	$html = str_replace("\r\n", "\n", $row['raw']);

	/* 文字コードを書き換え */
	$html = str_replace('<META http-equiv="Content-Type" content="text/html; charset=Shift_JIS">',
		'<META http-equiv="Content-Type" content="text/html; charset=utf-8">', $html);

	/* HTMLを連想配列に変換 */
	$domdoc = new DOMDocument();
	$domdoc->loadHTML($html);
	$xml = simplexml_import_dom($domdoc);

	/* 連想配列から抽出 */
	$tmp = $xml->body->center->table->tbody->tr->td->div;
	$data[2] = $tmp[1]->table->tbody->tr[0]->td[3];
	$data[14] = (int)(strpos($tmp[0], '市民開放授業') !== FALSE);
	$data[15] = (int)(strpos($tmp[0], '県内大学') !== FALSE);
	$tmp = $tmp[1]->table->tbody->tr;
	if (substr($data[2], 0, 1) !== 'M') {
		$data[3] = $tmp[1]->td[1];
		$data[4] = $tmp[2]->td;
		$data[5] = $tmp[3]->td[1];
		$data[6] = $tmp[3]->td[3];
		$data[7] = $tmp[4]->td[1];
		$data[8] = $tmp[4]->td[3];
		$data[9] = $tmp[4]->td[5];
		$data[10] = $tmp[4]->td[7];
		$data[11] = $tmp[5]->td[1];
		$data[12] = $tmp[5]->td[3];
		$data[13] = $tmp[5]->td[5];
	} else {
		$data[3] = $tmp[1]->td[1];
		$data[4] = NULL;
		$data[5] = $tmp[2]->td[1];
		$data[6] = NULL;
		$data[7] = $tmp[3]->td[3];
		$data[8] = $tmp[3]->td[5];
		$data[9] = $tmp[4]->td[3];
		$data[10] = (double)mb_convert_kana($tmp[4]->td[1], 'asKV');
		$data[11] = $tmp[3]->td[1];
		$data[12] = NULL;
		$data[13] = $tmp[1]->td[3];
	}

	$db->insert('htmldata', $data);
	if ($row['id'] % 100 === 0) {
		echo $row['id'] . PHP_EOL;
	}
}
$db->commit();
$db->close();
