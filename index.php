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

// Get permissions
if($appid!='' && $appkey != '')
	{
	$management_base_url = $openapi['hsda-management']['schemes'][0] . '://' . $openapi['hsda-management']['host'] . $openapi['hsda-management']['basePath'];
	$management_base_url = $management_base_url . 'users/auth/?login=' . $admin_login . '&code=' . $admin_code;	
	//echo "management url: " . $management_base_url . "<br />";
	
	// Send Auth Headers
	$headers = array('x-appid: ' . $admin_login,'x-appkey: ' . $admin_code);
	
	$http = curl_init();  
	curl_setopt($http, CURLOPT_URL, $management_base_url);  
	curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);   
	curl_setopt($http, CURLOPT_HTTPHEADER, $headers); 
	
	$output = curl_exec($http);
	//echo $output;
	$user_access = json_decode($output,true);		
	}
else
	{
	$user_access	= array();
	}

//var_dump($user_access);

// Get the master OpenAPI URL (Considering moving local for performance, for now its fine.)
$openapi_yaml = $openapi['hsda-default'];

// grab this path
$paths = $openapi_yaml['paths'];

// grab this securityDefinitions
$securityDefinitions = $openapi_yaml['securityDefinitions'];

// grab this path
$definitions = $openapi_yaml['definitions'];

foreach($paths as $path => $path_details)
	{
	foreach($path_details as $verb => $verb_details)
  		{

  		$route = $path;
  		//echo $route . ' == ' . $verb . "\n";
  		
  		$extpath = str_replace("/","-",$route);
  		if(substr($extpath,0,1)=="-"){ $extpath = substr($extpath,1,strlen($extpath)); }
  		if(substr($extpath,strlen($extpath)-1,1)=="-"){ $extpath = substr($extpath,0,strlen($extpath)-1); }
  		$prepath = "pre/" . $extpath . ".php";
  		$postpath = "post/" . $extpath . ".php";

		$route2 = $path;
		$route2 = str_replace("{",":",$route2);
		$route2 = str_replace("}","",$route2);

		$route_array = explode(":",$route2);
		$id_count = count($route_array);
		
		if(isset($verb_details['security']))
			{
			$security = $verb_details['security'];
			$secured = 1;
			}
		else
			{
			$secured = 0;	
			}

		//echo "is this secured? " . $secured . "\n";
		
		if($secured==1)
			{
			//echo "default: " . $openapi['hsda-default-system'] . "\n";	
			//var_dump($user_access[$openapi['hsda-default-system']]);
		//	echo "setting: " . $user_access[$openapi['hsda-default-system']][$route] . "\n";	
			if(isset($user_access[$openapi['hsda-default-system']][$route][$verb]))
				{
				$access = 1;	
				}
			else
				{
				$access = 0;	
				}
			}
		else
			{
			$access = 1;	
			}
			
		//echo "does user have access? " . $access . "\n";
		
  		// This logic is too vebose -- clean up
		// I just don't know how to do the $app->[verb] and the $ids -- simmer on

		// See if they have access
		if($access==1)
			{
	
	  		// GET
	    	if($verb == 'get')
	    		{
	
	    		if($id_count==2)
	    			{
		    		$app->get($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		elseif($id_count==3)
	    			{
		    		$app->get($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		else
	    			{
		    		$app->get($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		}
	
	  		// POST
	    	if($verb == 'post')
	    		{
	
	    		if($id_count==2)
	    			{
		    		$app->post($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		elseif($id_count==3)
	    			{
		    		$app->post($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		else
	    			{
		    		$app->post($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	
	    		}
	
	  		// PUT
	    	if($verb == 'put')
	    		{
	
	    		if($id_count==2)
	    			{
		    		$app->put($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		elseif($id_count==3)
	    			{
		    		$app->put($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		else
	    			{
		    		$app->put($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	
	    		}
	
	  		// DELETE
	    	if($verb == 'delete')
	    		{
	
	    		if($id_count==2)
	    			{
		    		$app->delete($route2, function ($id)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		elseif($id_count==3)
	    			{
		    		$app->delete($route2, function ($id,$id2)  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	    		else
	    			{
		    		$app->delete($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code){
		      			include "includes/" . $verb . ".php";
		      			});
	    			}
	
	    		}
			}
		else
			{
			$default = array();
			$default['message'] = "Sorry, you do not have access to that path!";
	    	if($verb == 'get')
	    		{				
	    		$app->get($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code,$default){
	      			//$app->response()->header("Content-Type", "application/json");
	      			$app->status(403);
					$app->response()->header("Content-Type", "application/json");
					echo stripslashes(format_json(json_encode($default)));
	      			});
	    		}
	    	if($verb == 'post')
	    		{	
	    		//echo $route2;
	    		$app->post($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code,$default){
	      			//$app->response()->header("Content-Type", "application/json");
	      			$app->status(403);
					$app->response()->header("Content-Type", "application/json");
					echo stripslashes(format_json(json_encode($default)));
	      			});
	    		}
	    	if($verb == 'put')
	    		{				
	    		$app->put($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code,$default){
	      			//$app->response()->header("Content-Type", "application/json");
	      			$app->status(403);
					$app->response()->header("Content-Type", "application/json");
					echo stripslashes(format_json(json_encode($default)));
	      			});
	    		}	    		
	    	if($verb == 'delete')
	    		{				
	    		$app->delete($route2, function ()  use ($app,$conn,$route,$verb,$openapi,$head,$prepath,$postpath,$appid,$admin_login,$admin_code,$default){
	      			//$app->response()->header("Content-Type", "application/json");
	      			$app->status(403);
					$app->response()->header("Content-Type", "application/json");
					echo stripslashes(format_json(json_encode($default)));	      			
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
