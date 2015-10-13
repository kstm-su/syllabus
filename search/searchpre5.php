<?pHp
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

function numAnalyze($Column,$Value){
	global $db;
	$ret=["",[]];
	if (!is_string($Value)) {
		return $ret;
	}
	$Value=$db->escape($Value);
	if (is_numeric($Value)) {
		$id=generator();
		$ret[0]="(::a$Column = :a$id)";
		$ret[1]["a$id"]=(float)$Value;
		return $ret;
	}
	$numarray=explode('..',$Value);
	if (is_numeric($numarray[0])&&is_numeric($numarray[1])) {
		$id=[generator(),generator()];
		$ret[0]="(::a$Column BETWEEN :a$id[0] AND :a$id[1])";
		$ret[1]["a$id[0]"]=(float)$numarray[0];
		$ret[1]["a$id[1]"]=(float)$numarray[1];
		return $ret;
	}
	if (is_numeric($numarray[0])) {
		$id=generator();
		$ret[0]="(::a$Column >= :a$id)";
		$ret[1]["a$id"]=(float)$numarray[0];
		return $ret;
	}
	if (is_numeric($numarray[1])) {
		$id=generator();
		$ret[0]="(::a$Column <= :a$id)";
		$ret[1]["a$id"]=(float)$numarray[1];
		return $ret;
	}
	return $ret;
}

function strAnalyze($ColumnName,$Value){
	global $db;
	$ret=["",[]];
	if (!is_string($Value)) {
		return $ret;
	}
	$Value=$db->escape($Value);
	$ret[0]='(? LIKE ?)';
	$ret[1][]=(string)$db->escape($ColumnName);
	$ret[1][]=(string)$Value;
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
				if (!is_null($y[0])) {
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
	$id=generator();
	$ret[0]="(SELECT FROM ::a$id WHERE (".$ret[0].'))';
	$ret[1]+=["a$id"=>(string)$db->escape($haystack[0])];
	return $ret;
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
			$ret=caseNum($SearchOption[1][0],$input[$SearchOption[0]]);			
			$query.=$ret[0];
			$queryvalue+=$ret[1];
			var_dump($query);
			var_dump($queryvalue);
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
		echo $db->sql($query,$queryvaluearray);
	}
}
