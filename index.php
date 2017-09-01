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

// Get the master OpenAPI URL (Considering moving local for performance, for now its fine.)
$openapi_yaml_raw = file_get_contents($openapi_url);
$openapi_yaml = yaml_parse($openapi_yaml_raw);

// grab this path
$paths = $openapi_yaml['paths'];

// grab this path
$definitions = $openapi_yaml['definitions'];

foreach($paths as $path => $path_details)
	{
	foreach($path_details as $verb => $verb_details)
  		{
  		
  		// GET	
    	if($verb == 'get')
    		{
			$route = $path;
    		$app->get($route, function ()  use ($app,$conn,$route,$verb,$openapi_yaml){
      			include "methods/includes/" . $verb . ".php";
      			});
    		}
    	}
	}

$route = "/";
$app->get($route, function ()  use ($app,$conn,$route,$verb){
  	echo "hello!";
  	});

$app->run();

?>
