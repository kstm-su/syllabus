<?php
header("Content-Type: application/json; charset=UTF-8; Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");
//公開前にパーミッション設定と、dbクラスのパスの置き換えを行うこと
require_once('../lib/util.php');
$db=new DBGuest();

//対応予定のオプション一覧です。
$SerchOptions=array("id","year","code","subject","title","teacher","staff","season","semester","schedule","location","room","classroom","unit","credit","target","style","department","word");
//曜日の配列です。順番が大事(mon->2)なので、追加は構わないが、挿入するときはよく注意すること。
$dweek=array("sun","mon","tue","wed","thu","fri","sat");

$input=array_map(function($req){
	if (is_array($req)) {
		return array_map('kana', $req);
	}
	return array(kana($req));
}, $_REQUEST);

$query="";
$summaryquery="";

//idをそれぞれで検索するクエリをここに入れて、最後に統合します。
$queryarray=array();

foreach ($SerchOptions as $SerchOption) {
	if (isset($input[$SerchOption])){
		if (is_array($input[$SerchOption])) {
			switch ($SerchOption){
				/*こちらで振った独自idでの検索です。
				 *このid検索はid[]=55でidが55の授業にヒットさせることができる他、id[]=1..100で1以上100以下のidにヒットさせることができます。
				 * */
			case "id":{
				$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
				foreach ($input[$SerchOption] as &$x) {
					$SerchOptioncount=0;
					$querymemo="SELECT DISTINCT(D0.id) FROM";
					foreach ($x as &$y) {
						$y=explode("..",$y);
						$memo="";
						//..だけの場合や、1..5..6のように3つ以上にわけられる場合は解析しません。
						if (sizeof($y)>2||sizeof($y)<1) {
							continue;
						}
						//まずescapeします。
						$y=array_map([$db,"escape"],$y);

						//要素が1つであり、かつそれが数字として解釈できるなら、それはidとして一意に決まります。
						if(sizeof($y)==1&&is_numeric($y[0])){
							$memo="(SELECT id FROM list WHERE id = $y[0]) as D$SerchOptioncount ";
						}else if(sizeof($y)==2){
							//要素が2つの場合で、数字として解釈できるならそれを条件に入れます。
							//数字として解釈できなかった場合は飛ばします。
							//1..aでaは数字として解釈できないので、1..となり、1以上のidにヒットします。
							$memo="(SELECT id FROM list WHERE ";
							$memocount=0;
							if(is_numeric($y[0])){
								$memo.="$y[0] <= id ";
								$memocount++;
							}
							if(is_numeric($y[1])){
								if ($memocount>0) {
									$memo.="AND ";
								}
								$memo.="id <= $y[1] ";
								$memocount++;
							}
							if ($memocount==0) {
								$memo="";	
							}else{
								$memo.=") as D$SerchOptioncount";	
							}
						}
						if($memo!==""){
							if($SerchOptioncount>0){
								$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
							}else{
								$querymemo.=$memo;
							}
							$SerchOptioncount++;
						}
					}
					if ($SerchOptioncount==0) {
						$querymemo="";
					}
					$x=$querymemo;
				}
				$queryarray[]="SELECT id FROM list WHERE id in(".implode(") UNION (",$input[$SerchOption]).")";
				break;
			}
case "year":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.year) FROM";
		foreach ($x as &$y) {
			$y=explode("..",$y);
			$memo="";
			if (sizeof($y)>2||sizeof($y)<1) {
				continue;
			}
			$y=array_map([$db,"escape"],$y);

			if(sizeof($y)==1&&is_numeric($y[0])){
				$memo="(SELECT year FROM list WHERE year = $y[0]) as D$SerchOptioncount ";
			}else if(sizeof($y)==2){
				$memo="(SELECT year FROM list WHERE ";
				$memocount=0;
				if(is_numeric($y[0])){
					$memo.="$y[0] <= year ";
					$memocount++;
				}
				if(is_numeric($y[1])){
					if ($memocount>0) {
						$memo.="AND ";
					}
					$memo.="year <= $y[1] ";
					$memocount++;
				}
				if ($memocount==0) {
					$memo="";	
				}else{
					$memo.=") as D$SerchOptioncount";	
				}
			}
			if($memo!==""){
				if($SerchOptioncount>0){
					$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
				}else{
					$querymemo.=$memo;
				}
				$SerchOptioncount++;
			}
		}
		if ($SerchOptioncount==0) {
			$querymemo="";
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM list WHERE year in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "code":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			$memo="(SELECT id FROM summary WHERE code LIKE '%$y%' ) as D$SerchOptioncount ";
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM summary WHERE id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "schedule": {
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.id) FROM";
		foreach ($x as $y) {
			$y=$db->escape(mb_strtolower($y));
			$str="";
			if(is_numeric($y)){
				$str="period = $y";
			}else{
				$dweekSubscript= array_search(substr($y,0,3),$dweek);
				if (mb_strpos($y,"集")!==FALSE) {
					if($str!==""){
						$str.=" AND ";
					}
					$str.="intensive = 1 AND day is NULL";
				} 
				if (mb_strpos($y,"不定")!==FALSE) {
					if($str!==""){
						$str.=" AND ";
					}
					$str.="irregular = 1 AND day is NULL";
				} 
				if ($dweekSubscript!==FALSE) {
					if($str!==""){
						$str.=" AND ";
					}
					if (is_numeric($dweekSubscript)) {
						$str.="day = $dweekSubscript ";
					}
				}
				if (strlen($y)>3&&is_numeric($num=substr($y,3))) {
					if($str!==""){
						$str.=" AND ";
					}
					$str.=" period = $num ";
				}
			}
			if($str!==""){
				$memo="(SELECT id FROM schedule where $str)as D$SerchOptioncount ";
				if($SerchOptioncount>0){
					$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
				}else{
					$querymemo.=$memo;
				}
				$SerchOptioncount++;
			}
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM schedule WHERE id in (".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "title":
case "subject":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			$memo="(SELECT id FROM summary WHERE title LIKE '%$y%' or title_english LIKE '%$y%') as D$SerchOptioncount ";
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM summary WHERE id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "word":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="(SELECT id FROM textdata WHERE value LIKE'%$y%') as D$SerchOptioncount ";
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;

		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM textdata WHERE id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "semester":
case "season":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.semester_id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			if (is_numeric($y)) {
				$memo="(SELECT semester_id FROM semester WHERE semester_id = $y) as D$SerchOptioncount ";
			}else{
				$memo="(SELECT semester_id FROM semester WHERE description LIKE '%$y%') as D$SerchOptioncount ";
			}
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.semester_id = D$SerchOptioncount.semester_id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM summary WHERE semester_id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "style":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			$memo="(SELECT id FROM summary WHERE style LIKE '%$y%') as D$SerchOptioncount ";
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM summary WHERE id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "staff":
case "teacher":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.staff_id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			if (is_numeric($y)) {
				$memo="(SELECT staff_id FROM staff WHERE staff_id = $y) as D$SerchOptioncount ";
			}else{
				$memo="(SELECT staff_id FROM staff WHERE name LIKE '%$y%') as D$SerchOptioncount ";
			}
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.staff_id = D$SerchOptioncount.staff_id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="(SELECT id FROM teacher WHERE staff_id in(".implode(") UNION (",$input[$SerchOption])."))";
	break;
}
case "department":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.department_id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			if(is_numeric($y)){
				$memo="(SELECT department_id FROM department WHERE department_id = $y ) as D$SerchOptioncount ";
			}else if(ctype_alnum($y)){
				$memo="(SELECT department_id FROM department WHERE department_code = '$y' ) as D$SerchOptioncount ";
			}else{
				$memo="(SELECT department_id FROM department WHERE name LIKE '%$y%' ) as D$SerchOptioncount ";
			}
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.department_id = D$SerchOptioncount.department_id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM summary WHERE department_id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "room":
case "classroom":
case "location":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.room_id) FROM";
		foreach ($x as &$y) {
			$y=$db->escape($y);
			$memo="";
			if(is_numeric($y)){
				$memo="(SELECT room_id FROM room WHERE room_id = $y ) as D$SerchOptioncount ";
			}else{
				$memo="(SELECT room_id FROM room WHERE name LIKE '%$y%' ) as D$SerchOptioncount ";
			}
			if($SerchOptioncount>0){
				$querymemo.=" JOIN ".$memo."ON D0.room_id = D$SerchOptioncount.room_id";
			}else{
				$querymemo.=$memo;
			}
			$SerchOptioncount++;
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM classroom WHERE room_id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}
case "credit":
case "unit":{
	$input[$SerchOption]=array_map(function($x){return explode(",",$x);},$input[$SerchOption]);
	foreach ($input[$SerchOption] as &$x) {
		$SerchOptioncount=0;
		$querymemo="SELECT DISTINCT(D0.id) FROM";
		foreach ($x as &$y) {
			$y=explode("..",$y);
			$memo="";
			if (sizeof($y)>2||sizeof($y)<1) {
				continue;
			}
			$y=array_map([$db,"escape"],$y);
			if(sizeof($y)==1&&is_numeric($y[0])){
				$memo="(SELECT id FROM summary WHERE credit = $y[0]) as D$SerchOptioncount ";
			}else if(sizeof($y)==2){
				$memo="(SELECT id FROM summary WHERE ";
				$memocount=0;
				if(is_numeric($y[0])){
					$memo.="$y[0] <= credit ";
					$memocount++;
				}
				if(is_numeric($y[1])){
					if ($memocount>0) {
						$memo.="AND ";
					}
					$memo.="credit <= $y[1] ";
					$memocount++;
				}
				if ($memocount==0) {
					$memo="";	
				}else{
					$memo.=") as D$SerchOptioncount";	
				}
			}
			if($memo!==""){
				if($SerchOptioncount>0){
					$querymemo.=" JOIN ".$memo."ON D0.id = D$SerchOptioncount.id";
				}else{
					$querymemo.=$memo;
				}
				$SerchOptioncount++;
			}
		}
		if ($SerchOptioncount==0) {
			$querymemo="";
		}
		$x=$querymemo;
	}
	$queryarray[]="SELECT id FROM summary WHERE id in(".implode(") UNION (",$input[$SerchOption]).")";
	break;
}

default :{
	//	$input[$SerchOption]=str_replace(array(' ',',')," AND '$SerchOption' = ",$input[$SerchOption]);
	//	$input[$SerchOption]="('$SerchOption' = ".implode(") OR ( '$SerchOption' = ",$input[$SerchOption]).")";
}
			}	
		}
	}
}
$lastquery="SELECT `json` FROM `json` WHERE id in (SELECT DISTINCT(U0.id) FROM ";
//$lastquery="SELECT * FROM response WHERE id in (SELECT DISTINCT(U0.id) FROM ";

$QueryArrayCount=0;
foreach ($queryarray as $x) {
	$x="($x)as U$QueryArrayCount ";
	if($QueryArrayCount>0){
		$lastquery.=" JOIN ".$x."ON U0.id = U$QueryArrayCount.id";
	}else{
		$lastquery.=$x;
	}
	$QueryArrayCount++;
}
$lastquery.=") LIMIT 0, 100;";

$result=$db->query($lastquery);
echo '{"syllabus":[' . implode(',', array_map('implode', $result->fetch_all())) . '],"page":'. json_encode(array('begin' => 0, 'end' => 100)) .',"sql":"' . $lastquery . '"}';
/*
$reurn =array();
while($row=$result->fetch_assoc()){
	$return[]=json_decode($row[json]);
}

$db->close();
echo json_encode($return,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
*/
