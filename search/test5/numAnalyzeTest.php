<?php
include(__dir__."/../searchpre5.php");
while(1){
	$x=explode(" ",trim(fgets(STDIN)));
	echo numAnalyze($x[0],$x[1]).PHP_EOL;
}
