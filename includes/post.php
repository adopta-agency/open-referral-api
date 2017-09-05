<?php
$ReturnObject = array();

$override = 0;

$request = $app->request();
$_get = $request->params();
$_get = filter_var_array($_get,FILTER_SANITIZE_STRING);
$_body = $request->getBody();
$_body = json_decode($_body,true);
$_body = filter_var_array($_body,FILTER_SANITIZE_STRING);

// Override any ID
$local_id = getGUID();
$_body['id'] = $local_id;

// grab this path
$api = $openapi['hsda-default']['paths'][$route];

// grab this path
$definitions = $openapi['hsda-default']['definitions'];

// load up the parameters (type,name,description,default)
$parameters = $api[$verb]['parameters'];

// load of up the responses
$responses = $api[$verb]['responses'];
$response_200 = $responses['200'];

// grab our schema
$schema_ref = $response_200['schema']['items']['$ref'];
$schema = str_replace("#/definitions/","",$schema_ref);
$schema_properties = $definitions[$schema]['properties'];
$schema = str_replace("_complete","",$schema);

$path_count_array = explode("/",$route);
$path_count = count($path_count_array);
$core_path = $path_count_array[1];
$core_path = substr($core_path,0,strlen($core_path)-1);
//echo "path: " . $core_path . "<br />";
if(isset($_body[$core_path . "_id"])){ $_body[$core_path . "_id"] = $id; }

// Load any pre extensions for this route
if (file_exists($prepath))
	{
	include $prepath;
	}

// Meta
include "meta/pre.php";

// override primary query
if($override==0)
	{

	// Fields
	$field_string = "";
	foreach($schema_properties as $field => $value)
		{
		if(isset($value['type']) && $value['type'] != 'array')
			{
			if(isset($_body[$field]))
				{
				$field_string .= $field . ",";
				}
			}
		else
			{
			// Deal With Array
			}
		}
	if(substr($field_string,strlen($field_string)-1,1) == ","){ $field_string = substr($field_string,0,strlen($field_string)-1); }

	// Values
	$value_string = "";
	foreach($schema_properties as $field => $value)
		{
		if(isset($value['type']) && $value['type'] != 'array')
			{
			if(isset($_body[$field]))
				{
				if(is_array( $_body[$field]))
					{
					$value_string .= "'" . stripslashes(format_json(json_encode($_body[$field]))) . "',";
					}
				else
					{
					$value_string .= "'" . $_body[$field] . "',";
					}
				}
			}
		else
			{
			// Deal With Array
			}
		}
	if(substr($value_string,strlen($value_string)-1,1) == ","){ $value_string = substr($value_string,0,strlen($value_string)-1); }

	// Build The Query To Insert
	$query = "INSERT INTO " . $schema ."(" . $field_string . ") VALUES(" . $value_string . ")";
	//echo "\n" . $query . "\n";

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
		if(isset($value['type']) && $value['type'] != 'array')
			{
			$F[$field] = filter_var($_body[$field], FILTER_SANITIZE_STRING);
			}
		else
			{
			// Deal With Array

			$path_count_array = explode("/",$route);
			$path_count = count($path_count_array);
			$core_path = $path_count_array[1];
			$core_path = substr($core_path,0,strlen($core_path)-1);
			//echo "path: " . $core_path . "<br />";
			//echo "path count: " . $path_count . "<br />";

			$sub_schema_ref = $value['items']['$ref'];
			$sub_schema = str_replace("#/definitions/","",$sub_schema_ref);
			$sub_schema_properties = $definitions[$sub_schema]['properties'];
			//echo $sub_schema . "\n";
			//var_dump($sub_schema_properties);

			foreach($_body[$field] as $sub_body)
				{

				// Override any ID
				$sub_id = getGUID();
				$sub_body['id'] = $sub_id;
				$sub_body[$core_path . '_id'] = $local_id;

				// Fields
				$field_string = "";
				foreach($sub_schema_properties as $sub_field_1 => $sub_value_1)
					{
					if(isset($sub_value_1['type']))
						{
						if(isset($sub_body[$sub_field_1]))
							{
							$field_string .= $sub_field_1 . ",";
							}
						}
					else
						{
						// Deal With Array
						}
					}
				if(substr($field_string,strlen($field_string)-1,1) == ","){ $field_string = substr($field_string,0,strlen($field_string)-1); }

				// Values
				$value_string = "";
				foreach($sub_schema_properties as $sub_field_2 => $sub_value_2)
					{
					if(isset($sub_value_2['type']))
						{
						if(isset($sub_body[$sub_field_2]))
							{
							$value_string .= "'" . $sub_body[$sub_field_2] . "',";
							}
						}
					else
						{
						// Deal With Array
						}
					}
				if(substr($value_string,strlen($value_string)-1,1) == ","){ $value_string = substr($value_string,0,strlen($value_string)-1); }

				// Build The Query To Insert
				$query = "INSERT INTO " . $sub_schema ."(" . $field_string . ") VALUES(" . $value_string . ")";
				//echo "\n" . $query . "\n";

				// Execute Query
			    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			    $response = $conn->exec($query);
				//echo $response . "\n";

				}

			$F[$field] = $_body[$field];

			}
		}
	}

$ReturnObject = $F;

// Load any post extensions for this path
if (file_exists($postpath))
	{
	include $postpath;
	}

// Meta
include "meta/post.php";

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
