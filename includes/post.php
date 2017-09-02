<?php
$ReturnObject = array();

$request = $app->request();
$_get = $request->params();
$_get = filter_var_array($_get,FILTER_SANITIZE_STRING);
$_body = $request->getBody();
$_body = json_decode($_body,true);	
$_body = filter_var_array($_body,FILTER_SANITIZE_STRING);

if(isset($_body['name']))
	{
		
	// Override any ID
	$id = getGUID();
	$_body['id'] = $id;
	
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

	// Build The Query To Insert
	$query = "INSERT INTO " . $schema ."(";

	// Fields
	$count = 0;
	$field_count = count($schema_properties);
	foreach($schema_properties as $field => $value)
		{
		if(isset($_body[$field]))
			{			
			$query .= $field;
			$count++;
			if($field_count != $count){ $query .= ","; }
			}
		}

	$query .= ") VALUES(";
	
	// Values
	$count = 0;
	$field_count = count($schema_properties);
	foreach($schema_properties as $field => $value)
		{
		if(isset($_body[$field]))
			{			
			$query .= "'" . filter_var($_body[$field], FILTER_SANITIZE_STRING) . "'";
			$count++;
			if($field_count != $count){ $query .= ", "; } 
			}
		}	

	$query .= ")";
	
	//echo $query . "\n";

	// Execute Query
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $response = $conn->exec($query);
	//echo $response . "\n";
	
	// We need to do something if fails -- return proper status code
    if($response==1)
    	{
    	// Success
    	}
    else
    	{
    	// INSERT FAILED -- what do we do
    	}
	// Return Values
	$F = array();
	foreach($schema_properties as $field => $value)
		{
		$F[$field] = filter_var($_body[$field], FILTER_SANITIZE_STRING);
		}	

	$ReturnObject = $F;
	}

$app->response()->header("Content-Type", "application/json");
echo stripslashes(format_json(json_encode($ReturnObject)));
?>