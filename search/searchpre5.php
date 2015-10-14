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
				if ($query==="") {
					$query='(SELECT DISTINCT ::'.(string)$db->escape($haystack[2]).$id[2].' FROM ::'.(string)$db->escape($haystack[0]).$id[0].' WHERE '.$y[0].')';
				}else{
					$query='(SELECT DISTINCT ::'.(string)$db->escape($haystack[2]).$id[2].' FROM ::'.(string)$db->escape($haystack[0]).$id[0].' WHERE ::'.(string)$db->escape($haystack[2]).$id[2].' IN '.$query.' AND '.$y[0].')';
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
	$ret[0]='(SELECT DISTINCT ::'.(string)$db->escape($haystack[2]).$id[2].' FROM ::'.(string)$db->escape($haystack[0]).$id[0].' WHERE ::'.(string)$db->escape($haystack[2]).$id[2].' IN ('.$ret[0].'))';
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
		case STR:{
			$ret=caseStr($SearchOption[1][0],$input[$SearchOption[0]]);			
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
