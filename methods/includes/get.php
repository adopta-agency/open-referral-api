<?php
$request = $app->request();
$_get = $request->params();

// Get the master OpenAPI URL (Considering moving local for performance, for now its fine.)
$openapi_yaml_raw = file_get_contents($openapi_url);
$openapi_yaml = yaml_parse($openapi_yaml_raw);

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

$ReturnObject = array();

$Query = "SELECT ";

// loop through each property and build fields
$field_string = "";
foreach($schema_properties as $field => $value)
	{
	$field_string .= $field . ",";
	}
$field_string = substr($field_string,0,strlen($field_string)-1);
$Query .= $field_string;

$Query .= " FROM " . $schema;

// Build the Query
$where = "";
$sorting = "";
$paging = "";
$page = 0;
$per_page = 25;
foreach($parameters as $parameter)
	{	

	//echo $parameter['name'] . "<br />";

	// Multiple queries		
	if($parameter['name']=='queries')	
		{
		$qu_arr = explode(',',$_get['queries']);
		foreach($qu_arr as $q)
			{
			//echo $q . "<br />";
			$q_arr = explode('=',$q);
			$field_name = $q_arr[0];
			$field_value = $q_arr[1];
			$where .= " " . $field_name . " LIKE '%" . $field_value . "%' AND";		
			}
		}
		
	// Order
	if($parameter['name']=='sortby')	
		{
		$sortby = $_get['sortby'];
		if(isset($_get['order']))
			{
			$order = $_get['order'];
			}
		else
			{
			$order = "asc";	
			}
		
		$sorting = $sortby . " " . $order;	
		}
		
	// Pagination
	if($parameter['name']=='page')	
		{
		$page = $_get['page'];
		if(isset($_get['per_page']))
			{
			$per_page = $_get['per_page'];
			}
		
		$paging = $page . "," . $per_page;
		}			
		
	}
$where = substr($where,0,strlen($where)-4);
$Query .= " WHERE" . $where;	
$Query .= " ORDER BY " . $sorting;
$Query .= " LIMIT " . $paging;

//echo $Query;

foreach ($conn->query($Query) as $row) 
	{
	$F = array();	
	foreach($schema_properties as $field => $value)
		{			
		$F[$field] = $row[$field];														
		}    	
	array_push($ReturnObject, $F);		
	}

$app->response()->header("Content-Type", "application/json");
echo stripslashes(format_json(json_encode($ReturnObject)));
?>