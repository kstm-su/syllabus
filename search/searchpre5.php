<?php
header("Content-Type: application/json; charset=UTF-8; Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");

//公開前にパーミッション設定と、dbクラスのパスの置き換えを行うこと
require_once(__dir__.'/../lib/util.php');

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
		$ret[0][]="(`$ColumnName` >= ?) ";
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


