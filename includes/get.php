<?php
$request = $app->request();
$_get = $request->params();
$head = $request->headers();

$override = 0;

// grab this path
$api = $openapi['hsda-default']['paths'][$route];

// grab this path
$definitions = $openapi['hsda-default']['definitions'];

// load up the parameters (type,name,description,default)
if(isset($api[$verb]['parameters']))
	{
	$parameters = $api[$verb]['parameters'];
	}
else
	{
	$parameters = array();	
	}

// load of up the responses
if(isset($api[$verb]['responses']))
	{
	$responses = $api[$verb]['responses'];
	}
else
	{
	$responses = array();
	}
	
$response_200 = $responses['200'];

// grab our schema
$schema_ref = $response_200['schema']['items']['$ref'];
$schema = str_replace("#/definitions/","",$schema_ref);
$schema_properties = $definitions[$schema]['properties'];
$schema = str_replace("_complete","",$schema);

$ReturnObject = array();

// Load any pre extensions for this route
//echo "pre:" . $prepath . "<br />";
if(file_exists($prepath)) 
	{
	include $prepath;
	}
	
// Meta
include "meta/pre.php";	

// override primary query
if($override==0)
	{
	
	$Query = "SELECT ";
	
	// loop through each property and build fields
	$field_string = "";
	foreach($schema_properties as $field => $value)
		{
		if(isset($value['type']) && $value['type'] != 'array')
			{		
			$type = $value['type'];
			$field_string .= $field . ",";
			}
		else
			{			
			// Deal With Array	
			}
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
			if(isset($_get['queries']))
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
			}
	
		// Order
		if($parameter['name']=='sortby')
			{
			if(isset($_get['sortby']))
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
			}
	
		// Pagination
		if($parameter['name']=='page')
			{
			if(isset($_get['page']))			
				{
				$page = $_get['page'];
				if(isset($_get['per_page']))
					{
					$per_page = $_get['per_page'];
					}
				}
			$paging = $page . "," . $per_page;		
			}
	
		}
	
	$where = substr($where,0,strlen($where)-4);
	
	if($where!='')
		{
		$Query .= " WHERE" . $where;
		}
		
	if(isset($id) && $id !='complete')
		{
		$path_count_array = explode("/",$route);	
		///var_dump($path_count_array);
		$path_count = count($path_count_array);	
		$core_path = $path_count_array[1];
		$core_path = substr($core_path,0,strlen($core_path)-1);
		//echo "path: " . $core_path . "<br />";
		//echo "path count: " . $path_count . "<br />";
		
		if(isset($id2) && $id2 !='complete')
			{
			if($path_count == 6)
				{
				$Query .= " WHERE id = '" . $id2 . "' AND " . $core_path . "_id = '" . $id . "'";	
				}		
			}
		else
			{
			if($path_count == 5 && $path_count_array[2] !='complete')
				{
				$Query .= " WHERE " . $core_path . "_id = '" . $id . "'";	
				}
			else
				{
				$Query .= " WHERE id = '" . $id . "'";	
				}
			}
		}
		
	if($sorting != '')
		{
		$Query .= " ORDER BY " . $sorting;
		}
	
	if($paging!='')
		{
		$Query .= " LIMIT " . $paging;
		}
		
	//echo $Query;
	
	$results = $conn->query($Query);
	if(count($results) > 0)
		{
		foreach ($results as $row)
			{
			$F = array();
			$core_resource_id = '';
			foreach($schema_properties as $field => $value)
				{
					
				if(isset($value['type']) && $value['type'] != 'array')
					{			
					$type = $value['type'];
					$F[$field] = $row[$field];
					
					if($field=='id')
						{
						$core_resource_id = $row[$field];	
						}
					}
				else
					{			
						
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
					
					$sub_query = "SELECT ";
					
					// loop through each property and build fields
					$field_string = "";
					foreach($sub_schema_properties as $sub_field_1 => $sub_value_1)
						{
						$type = $sub_value_1['type'];
						$field_string .= $sub_field_1 . ",";
						}
					$field_string = substr($field_string,0,strlen($field_string)-1);
					$sub_query .= $field_string;
					
					$sub_query .= " FROM " . $sub_schema;
					
					$sub_query .= " WHERE " . $core_path . "_id = '" . $core_resource_id . "'";	
					//	echo $sub_query . "/n";
					
					$sub_array = array();
					foreach ($conn->query($sub_query) as $sub_row)
						{	
						$a = array();	
						foreach($sub_schema_properties as $sub_field_2 => $sub_value_2)
							{
							$a[$sub_field_2] = $sub_row[$sub_field_2];
							}
						array_push($sub_array,$a);
						}
						
					$F[$field] = $sub_array;
					
					}			
				}
			array_push($ReturnObject, $F);
			}
		}
	} // End override
	
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
