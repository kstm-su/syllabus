<?php
header("Content-Type: application/json; charset=UTF-8; Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");

//公開前にパーミッション設定と、dbクラスのパスの置き換えを行うこと
require_once(__dir__.'/../lib/util.php');
require_once(__dir__.'/SearchConfig.php');

$db=new DBGuest();

function numAnalyze($ColumnName,$Value){
	global $db;
	$ret=[[],[]];
	if (!is_string($Value)) {
		return $ret;
	}
	$Value=$db->escape($Value);
	$numarray=explode('..',$Value);
	if (is_numeric($numarray[0])&&is_numeric($numarray[1])) {
		$ret[0][]="(`$ColumnName` BETWEEN ? AND ?) ";
		$ret[1][]=$numarray[0];
		$ret[1][]=$numarray[1];
		return $ret;
	}
	if (is_numeric($numarray[0])) {
		$ret[0][]="(`$ColumnName` >= ?) ";
		$ret[1][]=$numarray[0];
		return $ret;
	}
	if (is_numeric($numarray[1])) {
		$ret[0][]="(`$ColumnName` <= ?) ";
		$ret[1][]=$numarray[1];
		return $ret;
	}
	return $ret;
}

function strAnalyze($ColumnName,$Value){
	global $db;
	$ret=[[],[]];
	if (!is_string($Value)) {
		return $ret;
	}
	$Value=$db->escape($Value);
	$ret[0][]="(`$ColumnName` LIKE ?) ";
	$ret[1][]=$Value;
	return $ret;
}

function caseNum($haystack,$needle){
	
}

$input=array_map(function($req){
	if (is_array($req)) {
		return array_map('kana',$req);
	}
	return array(kana($req));
},$_REQUEST);

$queryarray=[];
$queryvaluearray=[];

foreach ($SEARCHOPTIONS as $SearchOption) {
	echo $SearchOption[0].PHP_EOL;

	if (isset($input[$SearchOption[0]])) {
		$query="";	
		$queryvalue=[];

		switch ($SearchOption[1][0][3]) {
		case NUM:{
			
			break;
		}
		}

		if (sizeof($SearchOption[1])===2&&$query!==""&&$SearchOption[1][1][3]===IN) {
			$query='(SELECT '.$SearchOption[1][1][2].' FROM '.$SearchOption[1][1][0].' WHERE '.$SearchOption[1][1][1]." in ($query))";
		}
		if ($query!=="") {
			$queryarray[]=$query;
			$queryvaluearray+=$queryvalue;	
		}
		echo "!".$query.PHP_EOL;
	}
}
