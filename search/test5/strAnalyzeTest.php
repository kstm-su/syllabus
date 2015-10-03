<?php
require_once(__dir__."/../searchpre5.php");

if (strAnalyze('abc','def')!=='(`abc` LIKE `%def%`) ') {
	echo "strAnalyze('abc','def') -> ".strAnalyze('abc','def')." (`abc` LIKE `%def%`) ".PHP_EOL;
	exit;
}
if (strAnalyze('abc',array('def','ghi'))!=='') {
	echo "strAnalyze('abc',array('def','ghi')) -> ".strAnalyze('abc','def')."".PHP_EOL;
	exit;
}
