<?php$route = '/taxonomy/';$app->get($route, function ()  use ($app){	$request = $app->request();	$_GET = $request->params();	$ReturnObject = array();	if($query=='')		{		$Query = "SELECT * FROM  WHERE name LIKE '%" . $query . "%'";		}	else		{		$Query = "SELECT * FROM ";		}	$Query .= " ORDER BY name ASC";	$DatabaseResult = mysql_query($Query) or die('Query failed: ' . mysql_error());	while ($Database = mysql_fetch_assoc($DatabaseResult))		{		}	$api->response()->header("Content-Type", "application/json");	echo stripslashes(format_json(json_encode($ReturnObject)));	});?>