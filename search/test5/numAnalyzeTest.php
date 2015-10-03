<?php
require_once(__dir__."/../searchpre5.php");

if (numAnalyze('abc','5..6')!=='(`abc` BETWEEN 5.000000 AND 6.000000) ') {
	echo "numAnalyze('abc','5..6') -> ".numAnalyze('abc','5..6')."  (`abc` BETWEEN 5.000000 AND 6.000000) ".PHP_EOL;
	exit;
}
if (numAnalyze('abc','5..')!=='(`abc` >= 5.000000) ') {
	echo "numAnalyze('abc','5..') -> ".numAnalyze('abc','5..')."  (`abc` >= 5.000000) ".PHP_EOL;
	exit;
}
if (numAnalyze('abc','..5')!=='(`abc` <= 5.000000) ') {
	echo "numAnalyze('abc','5..') -> ".numAnalyze('abc','..5')."  (`abc` <= 5.000000) ".PHP_EOL;
	exit;
}
if (numAnalyze('abc','5')!=='(`abc` = 5.000000) ') {
	echo "numAnalyze('abc','5') -> ".numAnalyze('abc','5')."  (`abc` = 5.000000) ".PHP_EOL;
	exit;
}
