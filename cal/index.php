<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'google-api-php-client/src');
require_once 'google-api-php-client/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('./client_secrets.json');
$client->addScope('https://www.googleapis.com/auth/calendar');

if(isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	$client->setRedirectUri('http://'.$_SERVER['HTTP_HOST'].'/syllabus/cal/index.php');
	$client->setAccessToken($_SESSION['access_token']);
	$service = new Google_Service_Calendar($client);
	
	$events = $service->events->listEvents('primary');
	while(true){
		foreach($events->getItems() as $event){
			echo $event->getSummary();
		}
		$pageToken = $events->getNextPageToken();
		if($pageToken){
			$optPrams = array('pageToken' => $pageToken);
			$events = $service->events->listEvents('primary', $optParamas);
		}else{
			break;
		}
	}

}else if(!isset($_GET['code'])){
	$auth_url = $client->createAuthUrl();
	header('Location: '.filter_var($auth_url, FILTER_SANITIZE_URL));
}else{
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'] . '/syllabus/cal/index.php';
	header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
}


?>

