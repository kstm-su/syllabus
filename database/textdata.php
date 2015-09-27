<?php

include_once('./util.php');

$db = new DBAdmin();
$db->truncate('textdata');

echo 'Updating `textdata` table ... ';
$q = $db->query('SELECT `id`, `text` FROM `raw`');
$db->begin();
while ($row = $q->fetch_assoc()) {
	/* CRLFをLFに統一 */
	$text = str_replace("\r\n", "\n", $row['text']);

	/* それぞれの項目に区切る */
	$paragraphs = preg_split('/\-{50}\r?\n(?=【.*?】\r?\n\-{50}\r?\n)/', $text);
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
		if (!$key && !$value) {
			continue;
		}

		/* 英数字を半角に、カタカナを全角に変換 */
		$key = trim(kana($key));
		if (!is_null($value)) {
			$value = trim(kana($value));
		}

		if (isset($data[$key])) {
			if (!is_null($value)) {
				$data[$key] .= "\n$value";
			}
		} else {
			$data[$key] = $value;
		}
	}

	foreach ($data as $key => $value) {
	/* DBに追加 */
		$db->insert('textdata',
			array(
				'id' => $row['id'],
				'key' => $key,
				'value' => $value
			)
		);
	}
	echo "\033[31G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
