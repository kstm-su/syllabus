<?php
header("Content-Type: application/json; charset=UTF-8; Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");

//公開前にパーミッション設定と、dbクラスのパスの置き換えを行うこと
require_once(__dir__.'/../lib/util.php');

$db=new DBGuest();

function numAnalyze($WHERE,$input){
	global $db;
	$WHERE=$db->escape($WHERE);
	$input=$db->escape($input);
	if (is_numeric($input)) {
		return sprintf("(`%s` = %f) ",$WHERE,$input);
	}
	$numarray=explode('..',$input);
	if (is_numeric($numarray[0])&&is_numeric($numarray[1])) {
		return sprintf("(`%s` BETWEEN %f AND %f) ",$WHERE,$numarray[0],$numarray[1]);	
	}
	if (is_numeric($numarray[0])) {
		return sprintf("(`%s` >= %f) ",$WHERE,$numarray[0]);
	}
	if (is_numeric($numarray[1])) {
		return sprintf("(`%s` <= %f) ",$WHERE,$numarray[1]);
	}
	return "";
}
