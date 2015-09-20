<?php

include_once('./util.php');
include_once('./db.php');

$db = new DBAdmin();

$table = $db->selectAll('rawtext');
$db->begin();
while ($row = $table->fetch_assoc()) {
	/* CRLFをLFに統一 */
	$text = str_replace("\r\n", "\n", $row['raw']);

	/* それぞれの項目に区切る */
	$paragraphs = preg_split('/\-{50}\r?\n(?=【.*?】\r?\n\-{50}\r?\n)/', $text);
	array_shift($paragraphs);

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
			$value = kana($value);
		}

		/* DBに追加 */
		$db->replace('textdata',
			array('id' => $row['id'], 'key' => $key, 'value' => $value));
	}
}
$db->commit();
$db->close();
