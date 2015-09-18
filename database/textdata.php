<?php

include_once('./db.php');

$db = new DB();
if ($db->access()) {
	echo 'db access failed' . PHP_EOL;
	exit;
}

/* テーブル初期化 */
$db->query('use syllabus');
$db->query('truncate `textdata`');

$table = $db->query('SELECT * FROM `rawtext`');
while ($row = $db->fetch_assoc($table)) {
	/* CRLFをLFに統一 */
	$text = str_replace("\r\n", "\n", $row['raw']);

	/* それぞれの項目に区切る */
	$paragraphs = preg_split('/\-{50}\n(?=【.*?】\n\-{50}\n)/', $text);
	array_shift($paragraphs);

	$data = array();
	foreach ($paragraphs as $i => $paragraph) {
		/* keyとvalueを取り出す */
		$pattern = '/^【(.*?)】\n(?:\-{50}\n\s*([\s\S]*?))?\s*$/';
		preg_match($pattern, $paragraph, $matches);
		if (isset($matches[1]) === FALSE) {
			$matches[1] = '';
		}
		if (isset($matches[2]) === FALSE) {
			$matches[2] = NULL;
		}
		list(, $key, $value) = $matches;

		/* valueの英数字を半角に、カタカナを全角に変換 */
		if (!is_null($value)) {
			$value = mb_convert_kana($value, 'asKV');
		}

		/* DBに追加 */
		$data[] = '(' . implode(', ', array_map(
			function($s) use ($db) {
				if (is_null($s)) {
					return 'NULL';
				}
				return '\'' . $db->escape($s) . '\'';
			}, array($row['id'], $key, $value))) . ')';
	}
	$db->query('INSERT INTO `textdata` (`id`, `key`, `value`) values '
		. implode(', ', $data));
	if ($row['id'] % 100 === 0) {
		echo $row['id'] . PHP_EOL;
	}
}

$db->cutting();
