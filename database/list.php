<?php

include_once('./db.php');

$data = array(
	'MODE' => '0',
	'STARTNO' => '0',
	'NENDO' => '2015',
	'CODE_JYOUKEN' => '0',
	'BtKENSAKU' => '1'
);

$curl_option = array(
	CURLOPT_URL => 'https://campus-2.shinshu-u.ac.jp/syllabus/syllabus.dll/Search',
	CURLOPT_SSL_VERIFYPEER => FALSE,
	CURLOPT_SSL_VERIFYHOST => FALSE,
	CURLOPT_RETURNTRANSFER => TRUE
);

$db = new DBAdmin();

$i = 0;
$array = array();
$lecture_array = array();

$db->begin();
do {
	$curl = curl_init();
	$curl_option[CURLOPT_POSTFIELDS] = http_build_query($data);
	curl_setopt_array($curl, $curl_option);
	$original_output = curl_exec($curl);
	$utf_encoded = mb_convert_encoding($original_output, 'utf8', 'cp932');
	$utf_output = $utf_encoded;
	//print $utf_output;
	if (!$i) {
		array_pop($data);
		$data += array(
			'BtNEXT' => '1',
			'BtKENSAKU' => '1'
		);
	}
	$data['STARTNO'] = $i * 100;
	$i++;

	$search_word = 'Text?';
	while ($utf_output = strstr($utf_output, $search_word)) {
		$memo_output = $utf_output;
		$memo_output = substr($memo_output, 0, strpos($memo_output,'">'));
		$memo_output = substr($memo_output, strlen($search_word));
		//echo $memo_output.PHP_EOL;
		{
			$sql_txt = explode('&', $memo_output);
			$sql_array = array();
			$sql_query = 'INSERT INTO list VALUES (NULL,';
			foreach($sql_txt as $sql_each_txt){
				$sql_array = array_merge($sql_array, explode('=', $sql_each_txt));
			}
			$db->insert('list', array(NULL, $sql_array[1], $sql_array[3], $sql_array[5], $memo_output));
			/*
			$sql_query.="'".addslashes($sql_array[1])."',";
			$sql_query.="'".addslashes($sql_array[3])."',";
			$sql_query.="'".addslashes($sql_array[5])."',";
			$sql_query.="'".addslashes($memo_output)."');";
			 */
			//print_r($sql_query);
			//$DB->query($sql_query).PHP_EOL;
		}
		$utf_output = substr($utf_output, 1);
	}
	echo $i . PHP_EOL;
	if (strpos($utf_encoded, "<!--\r\n<INPUT type=\"submit\" name=\"BtNEXT\" value=\"次へ >\" >\r\n-->") !== FALSE) {
		break;
	}
} while ($i < 1000);
$db->commit();
$db->close();
