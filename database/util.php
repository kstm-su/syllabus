<?php

/* DOMのパース処理時は警告を無視 */
libxml_use_internal_errors(true);

/* HTMLのソースをSimpleXMLに変換 */
function htmlobject ($html) {
	$doc = new DOMDocument();
	$doc->loadHTML($html);
	return simplexml_import_dom($doc);
}

/* POSTリクエストを送信 */
function post_request($url, $data) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
	$res = curl_exec($curl);
	curl_close($curl);
	return $res;
}
