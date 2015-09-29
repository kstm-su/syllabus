<?php
require_once(__dir__."/../searchpre5.php");

if (numAnalyze('abc','5..6')==='(`abc` BETWEEN 5 AND 6)') {
	echo "numAnalyze('abc','5..6') -> ".numAnalyze('abc','5..6')."  (`abc` BETWEEN 5 AND 6)".PHP_EOL;
	exit;
}

while(1){
	$x=explode(" ",trim(fgets(STDIN)));
	echo numAnalyze($x[0],$x[1]).PHP_EOL;
}
