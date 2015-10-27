<?php
header("Content-Type: application/json; charset=UTF-8; Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");

//公開前にパーミッション設定と、dbクラスのパスの置き換えを行うこと
require_once(__dir__.'/../lib/util.php');
require_once(__dir__.'/SearchConfig.php');

$db=new DBGuest();

function generator(){
	static $count=0;
	return $count++;
}

function numAnalyze($column,$value){
	global $db;
	$ret=["",[]];
	if (!is_string($value)) {
		return $ret;
	}
	$value=$db->escape($value);
	if (is_numeric($value)) {
		$id=generator();
		$ret[0]="(::a$column = :a$id)";
		$ret[1]["a$id"]=(float)$value;
		return $ret;
	}
	$numarray=explode('..',$value);
	if (is_numeric($numarray[0])&&is_numeric($numarray[1])) {
		$id=[generator(),generator()];
		$ret[0]="(::a$column BETWEEN :a$id[0] AND :a$id[1])";
		$ret[1]["a$id[0]"]=(float)$numarray[0];
		$ret[1]["a$id[1]"]=(float)$numarray[1];
		return $ret;
	}
	if (is_numeric($numarray[0])) {
		$id=generator();
		$ret[0]="(::a$column >= :a$id)";
		$ret[1]["a$id"]=(float)$numarray[0];
		return $ret;
	}
	if (is_numeric($numarray[1])) {
		$id=generator();
		$ret[0]="(::a$column <= :a$id)";
		$ret[1]["a$id"]=(float)$numarray[1];
		return $ret;
	}
	return $ret;
}

function strAnalyze($column,$value){
	global $db;
	$ret=["",[]];
	if (!is_string($value)) {
		return $ret;
	}
	$value=$db->escape($value);
	$id=generator();
	$ret[0]="(::$column LIKE :a$id)";
	$ret[1]["a$id"]='%'.(string)$value.'%';
	return $ret;
}

function schAnalyze($value){
	global $db;
	$ret=["",[]];
	if (!is_string($value)) {
		return $ret;
	}
	$value=$db->escape(mb_strtolower($value));
	if ($value==='null') {
		$id=[generator(),generator()];
		$ret[0]="(::a$id[0] IS NULL AND ::a$id[1] IS NULL)";
		$ret[1]=[
			"a$id[0]"=>'day',
			"a$id[1]"=>'period'
		];
		return $ret;
	}
	if (is_numeric($value)&&is_integer($value)) {
		$id=[generator(),generator()];
		$ret[0]="(::a$id[0] = :q$id[1])";
		$ret[1]=[
			"a$id[0]"=>'period',
			"a$id[1]"=>(int)$value
		];
		return $ret;
	}
	$dow=['su','mo','tu','we','th','fr','sa'];
	foreach ($dow as $x =>$y) {
		if (substr($value,0,2)===$y) {
			$id=[generator(),generator()];
			$ret[0]="(::a$id[0] = :a$id[1])";
			$ret[1]=[
				"a$id[0]"=>'day',
				"a$id[1]"=>(int)$db->escape($x)
			];
			break;
		}
	}
	if ($ret[0]===""||strlen($value)<=2||!is_numeric(substr($value,2,1))) {
		return $ret;
	}
	$id=[generator(),generator()];
	$ret[0]="($ret[0] AND (::a$id[0] = :a$id[1]))";
	$ret[1]+=[
		"a$id[0]"=>'period',
		"a$id[1]"=>(int)$db->escape(substr($value,2,1))
	];
	return $ret;
}

function caseNum($haystack,$needle){
	global $db;
	$needle=str_replace(' ',',',$needle);
	$id=generator();
	$ret=["",["a$id"=>(string)$db->escape($haystack[1])]];
	foreach ($needle as $num) {
		if (is_string($num)) {
			$numarray=explode(',',$num);
			$query="";
			$queryarray=[];
			foreach ($numarray as $x) {
				$y=numAnalyze($id,$x);
				if ($y[0]!=="") {
					if ($query!=="") {
						$query.=' AND ';
					}	
					$query.=$y[0];
					$queryarray+=$y[1];
				}
			}
			if ($query!=="") {
				if ($ret[0]!=="") {
					$ret[0].=' ) OR ( ';
				}
				$ret[0].=$query;
				$ret[1]+=$queryarray;
			}
		}
	}
	$id=[generator(),generator()];
	$ret[0]="(SELECT ::a$id[0] FROM ::a$id[1] WHERE (".$ret[0].'))';
	$ret[1]+=["a$id[0]"=>(string)$db->escape($haystack[2]),"a$id[1]"=>(string)$db->escape($haystack[0])];
	return $ret;
}

function caseStr($haystack,$needle){
	global $db;
	global $idg;
	list($haystack,$incase)=$haystack;
	$needle=str_replace(' ',',',$needle);
	$needle=str_replace('%','',$needle);
	$id=[generator(),generator(),generator()];
	$ret=["",[
		(string)$db->escape($haystack[0]).$id[0]=>(string)$db->escape($haystack[0]),
			(string)$db->escape($haystack[1]).$id[1]=>(string)$db->escape($haystack[1]),
			(string)$db->escape($haystack[2]).$id[2]=>(string)$db->escape($haystack[2])
		]];
	foreach ($needle as $str) {
		$strarray=explode(',',$str);
		$query="";
		$queryarray=[];
		foreach ($strarray as $x) {
			$y=strAnalyze((string)$db->escape($haystack[1]).$id[1],$x);
			if ($y[0]!=="") {
				$query_memo='(SELECT DISTINCT ::'.(string)$db->escape($haystack[2]).$id[2].' FROM ::'.(string)$db->escape($haystack[0]).$id[0].' WHERE '.$y[0].')';
				if (!is_null($incase)) {
					list($query_memo,$y_memo)=caseIn($incase,$query_memo);
					$y[1]+=$y_memo;
				}
				if ($query==="") {
					$query=$query_memo.' AS D'.generator();
				}else{
					$query.=' JOIN '.$query_memo.' AS D'.generator().' using(::id'.$idg.') ';
				}
				$queryarray+=$y[1];
			}
		}
		if ($query!=="") {
			if ($ret[0]!=="") {
				$ret[0].=' ) UNION ( ';	
			}
			$ret[0].=$query;
			$ret[1]+=$queryarray;
		}
	}
	if($ret[0]!==""){
		$ret[0]='(SELECT DISTINCT ::id'.$idg.' FROM ('.$ret[0].'))';
	}
	return $ret;
}

function caseIn($haystack,$str){
	global $db;
	$id=[generator(),generator(),generator()];
	return ['(SELECT DISTINCT ::IN'.$id[2].' FROM ::IN'.$id[0].' WHERE ::IN'.$id[1].' IN('.$str.'))',[
		"IN$id[0]"=>(string)$db->escape($haystack[0]),
			"IN$id[1]"=>(string)$db->escape($haystack[1]),
			"IN$id[2]"=>(string)$db->escape($haystack[2])
		]];
}

function caseSem($haystack,$needle){
	global $db;
	global $idg;
	list($haystack,$incase)=$haystack;
	$needle=str_replace(' ',',',$needle);
	$id=[generator(),generator(),generator(),generator(),generator(),generator()];
	$ret=["",[
		(string)$db->escape($haystack[0]).$id[0]=>(string)$db->escape($haystack[0]),
			(string)$db->escape($haystack[1]).$id[1]=>(string)$db->escape($haystack[1]),
			(string)$db->escape($haystack[2]).$id[2]=>(string)$db->escape($haystack[2]),
			(string)$db->escape($incase[0]).$id[3]=>(string)$db->escape($incase[0]),
			(string)$db->escape($incase[1]).$id[4]=>(string)$db->escape($incase[1]),
			(string)$db->escape($incase[2]).$id[5]=>(string)$db->escape($incase[2])
		]];
	foreach ($needle as $str) {
		$strarray=explode(',',$str);
		$query="";
		$queryarray=[];
		foreach ($strarray as $x) {
			switch ($x) {
			case 'first':
				if ($query!=="") {
					$query.=' AND ';	
				}
				$query.='(first=1)';
				break;
			case 'second':
				if ($query!=="") {
					$query.=' AND ';	
				}
				$query.='(second=1)';
				break;
			case 'fullyear':
				if ($query!=="") {
					$query.=' AND ';	
				}
				$query.='(second=1) AND (second=1)';
				break;
			case 'other':
				if ($query!=="") {
					$query.=' AND ';	
				}
				$query.='(intensive=1)';
				break;
			}
		}
		if ($query!=="") {
			if ($ret[0]!=="") {
				$ret[0].=' ) OR ( ';	
			}
			$ret[0].=$query;
		}
	}
	if ($ret[0]!=="") {
		$ret[0]='SELECT ::'.(string)$db->escape($incase[2]).$id[5].' FROM ::'.(string)$db->escape($incase[0]).$id[3].' WHERE ::'.(string)$db->escape($incase[1]).$id[4].' IN (SELECT ::'.(string)$db->escape($haystack[2]).$id[2].' FROM ::'. (string)$db->escape($haystack[0]).$id[0].' WHERE ('.$ret[0].'))';
	}
	//var_dump($needle);
	return $ret;
}

function caseSch($haystack,$needle){
	global $db;
	global $idg;
	$haystack=$haystack[0];
	$needle=str_replace(' ',',',$needle);

	$id=[generator(),generator(),generator()];
	$ret=["",[
		(string)$db->escape($haystack[0]).$id[0]=>(string)$db->escape($haystack[0]),
			(string)$db->escape($haystack[1]).$id[1]=>(string)$db->escape($haystack[1]),
			(string)$db->escape($haystack[2]).$id[2]=>(string)$db->escape($haystack[2])
		]];
	foreach ($needle as $str) {
		$strarray=explode(',',$str);
		$query="";
		$queryarray=[];
		foreach ($strarray as $x) {
			$y=schAnalyze($x);
			if ($y[0]!=="") {
				$query_memo='(SELECT DISTINCT ::'.(string)$db->escape($haystack[2]).$id[2].' FROM ::'.(string)$db->escape($haystack[0]).$id[0].' WHERE '.$y[0].')';
				if ($query==="") {
					$query=$query_memo.' AS D'.generator();
				}else{
					$query.=' JOIN '.$query_memo.' AS D'.generator().' using(::id'.$idg.') ';
				}
				$queryarray+=$y[1];
			}
		}	
		if ($query!=="") {
			if ($ret[0]!=="") {
				$ret[0].=' ) UNION ( ';	
			}
			$ret[0].=$query;
			$ret[1]+=$queryarray;
		}
	}	
	if($ret[0]!==""){
		$ret[0]='(SELECT DISTINCT ::id'.$idg.' FROM ('.$ret[0].'))';
	}
	return $ret;
}

$input=array_map(function($req){
	if (is_array($req)) {
		return array_map('kana',$req);
	}
	return array(kana($req));
},$_REQUEST);

$idg=generator();
$queryvaluearray=["",["id$idg"=>'id']];

foreach ($SEARCHOPTIONS as $SearchOption) {
	//echo $SearchOption[0].PHP_EOL;

	if (isset($input[$SearchOption[0]])) {
		$query="";	
		$queryvalue=[];

		switch ($SearchOption[1][0][3]) {
		case NUM:{
			$ret=caseNum($SearchOption[1][0],$input[$SearchOption[0]]);			
			$query.=$ret[0];
			$queryvalue+=$ret[1];
			//var_dump($query);
			//var_dump($queryvalue);
			break;
		}
		case STR:{
			$ret=caseStr($SearchOption[1],$input[$SearchOption[0]]);			
			$query.=$ret[0];
			$queryvalue+=$ret[1];
			//var_dump($query);
			//var_dump($queryvalue);
			break;
		}
		case SEM:{
			$ret=caseSem($SearchOption[1],$input[$SearchOption[0]]);			
			$query.=$ret[0];
			$queryvalue+=$ret[1];
			break;
		}
		case SCH:{
			$ret=caseSch($SearchOption[1],$input[$SearchOption[0]]);			
			$query.=$ret[0];
			$queryvalue+=$ret[1];
			//var_dump($query);
			//var_dump($queryvalue);
			break;
		}

		}

		if ($query!=="") {
			$id=generator();
			if ($queryvaluearray[0]!=="") {
				$queryvaluearray[0].=' JOIN ('.$query.") as U$id using(::id$idg) ";
			}else{
				$queryvaluearray[0]='('.$query.") as U$id ";
			}
			$queryvaluearray[1]+=$queryvalue;
		}
		//echo "!".$query.PHP_EOL;
		//echo $db->sql($query,$queryvaluearray);
	}
}
if ($queryvaluearray[0]!=="") {
	$queryvaluearray[0]="SELECT ::id$idg FROM ($queryvaluearray[0])";
}
//var_dump($queryvaluearray);
//echo $db->sql($queryvaluearray[0],$queryvaluearray[1]);
if ($queryvaluearray[0]!=="") {
	$id=[generator(),generator(),generator()];
	$queryvaluearray[0]="SELECT ::json$id[0] FROM ::res$id[1] WHERE ::id$idg in(SELECT DISTINCT D$id[2].::id$idg FROM ($queryvaluearray[0])as D$id[2]);";
	$queryvaluearray[1]+=[
		"json$id[0]"=>$db->escape('json'),
		"res$id[1]"=>$db->escape('json')
	];
	$result=$db->query($db->sql($queryvaluearray[0],$queryvaluearray[1]));
	echo '{"syllabus":[' . implode(',', array_map('implode', $result->fetch_all())) . '],"page":'. json_encode(array('begin' => 0, 'end' => 100)) .'}';
}
