<?php
header("Content-Type: application/json; charset=UTF-8; Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");

$SerchOptions=array("id","year","code","subject","teacher","season","schedule","location","unit","target","style","word");

//NULLなら検索しない、NULL以外ならそのワードで検索する。
//その前準備として、ないものにNULLをつけてわかりやすくしておく。
function CheckAndDefaultSet($input){
	global $SerchOptions;
	$return=array();

	foreach ($SerchOptions as $option) {
		$return[$option]=isset($input[$option])?$input[$option]:NULL;
	}
	return $return;
}


$return=array();
$return['id']=12345;
$return['year']=1988;
$return['code']="ABC12345";
$return['subject']="炎上案件入門";
$return['teacher']="見込甘";
$return['season']="正月";
$return['schedule'][]=array("dweek"=>"MO","period"=>1);
$return['schedule'][]=array("dweek"=>"FR","period"=>5);
$return['location']="都会の会議室";
$return['unit']=1;
$return['target']="ブラックな人々";
$return['style']="実技";
$return['note']="この授業を受ければ炎上案件に遭いやすくなります。";
$return['text'][]="サルでも分かる！炎上案件";
$return['text'][]="これでわかる！鎮火方法";
$return['word']="ぐちゃぐちゃぐちゃ";
$return['url']="http://campus-2.shinshu-u.ac.jp/syllabus/syllabus.dll/Display?NENDO=2015&BUKYOKU=T&CODE=T0511030";

$query_array=CheckAndDefaultSet($_GET);

//ここでDB検索する。
$return=$query_array;

echo json_encode($return,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
