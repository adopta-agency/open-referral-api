<?php
date_default_timezone_set('America/Los_Angeles');
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('config.php');
require_once('Slim/Slim.php');
require_once('libraries/common.php');

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$request = $app->request();
$params = $request->params();
$head = $request->headers();

//foreach ($head as $name => $values) {
    //echo $name . ": " . $values . "\n\r";
//}
 
if(isset($head['X_APPID'])){ $appid = $head['X_APPID']; } else { $appid = ""; }
if(isset($head['X_APPKEY'])){ $appkey = $head['X_APPKEY']; } else { $appkey = ""; }

//echo $appid . "<br />";
//echo $appkey . "<br />";

//$plan = "admin";
if($appid == "testkin" && $appkey = "testkin")
	{
	$plan = "admin";	
	}
else
	{
	$plan = "public";	
	}
	
// overrride	
$plan = "admin";	

if($plan=="admin")
	{
	require_once "methods/admin.php";
	}
else
	{
	require_once "methods/public.php";	
	}
	
$app->run();	
?>
