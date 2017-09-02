<?php
$request = $app->request();
$_get = $request->params();

// grab this path
$api = $openapi_yaml['paths'][$route];

// grab this path
$definitions = $openapi_yaml['definitions'];

// load up the parameters (type,name,description,default)
$parameters = $api[$verb]['parameters'];

// load of up the responses
$responses = $api[$verb]['responses'];
$response_200 = $responses['200'];

// grab our schema
$schema_ref = $response_200['schema']['items']['$ref'];
$schema = str_replace("#/definitions/","",$schema_ref);
$schema_properties = $definitions[$schema]['properties'];

$Query = "DELETE FROM " . $schema;

if(isset($id))
	{
	$path_count_array = explode("/",$route);	
	$path_count = count($path_count_array);	
	$core_path = $path_count_array[1];
	$core_path = substr($core_path,0,strlen($core_path)-1);
	//echo "path: " . $core_path . "<br />";
	//echo "path count: " . $path_count . "<br />";
	
	if(isset($id2))
		{
		if($path_count == 6)
			{
			$Query .= " WHERE id = '" . $id2 . "' AND " . $core_path . "_id = '" . $id . "'";	
			}		
		}
	else
		{
		if($path_count == 5)
			{
			$Query .= " WHERE " . $core_path . "_id = '" . $id . "'";	
			}
		else
			{
			$Query .= " WHERE id = '" . $id . "'";	
			}
		}
	}

// Execute Query
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$response = $conn->exec($Query);
//echo $response . "\n";

$ReturnObject = array();
$ReturnObject['message'] = $schema . " Deleted";
if(isset($id))
	{
	if(isset($id2))
		{		
		$ReturnObject['id'] = $id2;
		$ReturnObject[$core_path . '_id'] = $id;
		}
	else
		{
		$ReturnObject['id'] = $id;	
		}
	}
	
//echo $head['ACCEPT'] . "<br />";
if(isset($head['ACCEPT']) && $head['ACCEPT'] == 'text/csv')
	{
	$app->response()->header("Content-Type", "text/csv");	
	$return_csv = generateCsv($ReturnObject);
	echo $return_csv;
	}
elseif(isset($head['ACCEPT']) && $head['ACCEPT'] == 'application/xml')
	{
	$app->response()->header("Content-Type", "application/xml");	
	$return_xml = arrayToXml($ReturnObject);
	echo $return_xml;
	}
else
	{
	$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject)));
	}
?>