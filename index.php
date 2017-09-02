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
//    echo $name . ": " . $values . "\n\r";
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

  		$route = $path;

			$route2 = $path;
			$route2 = str_replace("{",":",$route2);
			$route2 = str_replace("}","",$route2);

			$route_array = explode(":",$route2);
			$id_count = count($route_array);

  		// This logic is too vebose -- clean up
			// I just don't know how to do the $app->[verb] and the $ids -- simmer on

  		// GET
    	if($verb == 'get')
    		{

    		if($id_count==2)
    			{
	    		$app->get($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		elseif($id_count==3)
    			{
	    		$app->get($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		else
    			{
	    		$app->get($route2, function ()  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		}

  		// POST
    	if($verb == 'post')
    		{

    		if($id_count==2)
    			{
	    		$app->post($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		elseif($id_count==3)
    			{
	    		$app->post($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		else
    			{
	    		$app->post($route2, function ()  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}

    		}

  		// PUT
    	if($verb == 'put')
    		{

    		if($id_count==2)
    			{
	    		$app->put($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		elseif($id_count==3)
    			{
	    		$app->put($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		else
    			{
	    		$app->put($route2, function ()  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}

    		}

  		// DELETE
    	if($verb == 'delete')
    		{

    		if($id_count==2)
    			{
	    		$app->delete($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		elseif($id_count==3)
    			{
	    		$app->delete($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}
    		else
    			{
	    		$app->delete($route2, function ()  use ($app,$conn,$route,$verb,$openapi_yaml,$head){
	      			include "includes/" . $verb . ".php";
	      			});
    			}

    		}

    	}
	}

$route = "/";
$app->get($route, function ()  use ($app,$conn,$route,$verb){
		// Get the master OpenAPI URL (Considering moving local for performance, for now its fine.)
		$apis_json_yaml_raw = file_get_contents($apis_json);
		$apis_json_yaml = yaml_parse($apis_json_yaml_raw);
		$app->response()->header("Content-Type", "application/json");
		echo stripslashes(format_json(json_encode($apis_json_yaml)));
  	});

$app->run();

?>
