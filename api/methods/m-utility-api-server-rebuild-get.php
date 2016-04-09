<?php

$route = '/utility/api/server/rebuild/';
$app->get($route, function ()  use ($app,$githuborg,$githubrepo,$gclient){

  $request = $app->request();
  $params = $request->params();

	$ref = "gh-pages";
	$APIsJSONURL = $params['apis_json_url'];
	$Resource_Store_File = "apis.json";

	$CheckFile = $gclient->repos->contents->getContents($githuborg, $githubrepo, $ref, $Resource_Store_File);
	$APIsJSONContent = base64_decode($CheckFile->getcontent());

	$APIsJSON = json_decode($APIsJSONContent,true);

	foreach($APIsJSON['apis'] as $APIsJSON)
		{

		$properties = $APIsJSON['properties'];

		foreach($properties as $property)
			{

			$property_type = $property['type'];

			if(strtolower($property_type)=="x-openapi-spec")
				{

				$swagger_url = $property['url'];
        $SwaggerJSON = file_get_contents($swagger_url);
				$Swagger = json_decode($SwaggerJSON,true);

				$Swagger_Title = $Swagger['info']['title'];
				$Swagger_Description = $Swagger['info']['description'];
				$Swagger_TOS = $Swagger['info']['termsOfService'];
				$Swagger_Version = $Swagger['info']['version'];

				$Swagger_Host = $Swagger['host'];
				$Swagger_BasePath = $Swagger['basePath'];

				$Swagger_Scheme = $Swagger['schemes'][0];
				$Swagger_Produces = $Swagger['produces'][0];

				echo $Swagger_Title . "<br />";

				$Method = "";
				$Method .= "<?php" . chr(13);

				$Swagger_Definitions = $Swagger['definitions'];

				$Swagger_Paths = $Swagger['paths'];
				foreach($Swagger_Paths as $key => $value)
					{

					$Path_Route = $key;
					echo $Path_Route . "<br />";

					// Each Path Variable
					$id = 0;
					$Path_Variable_Count = 1;
					$Path_Variables = "";
					$Begin_Tag = "{";
					$End_Tag = "}";
					$path_variables_array = return_between($Path_Route, $Begin_Tag, $End_Tag, EXCL);

					$Path_Route = str_replace("{",":",$Path_Route);
					$Path_Route = str_replace("}","",$Path_Route);

					if(is_array($path_variables_array))
						{
						foreach($path_variables_array as $var)
							{
							echo "VAR: " . $var . "<br />";
							if($Path_Variable_Count==1)
								{
								$Path_Variables .= chr(36) . $var;
								$Path_Variable_Count++;
								$id = $var;
								}
							else
								{
								$Path_Variables .= "," . chr(36) . $var;
								}
							}
						}
					else
						{
						if(strlen($path_variables_array)>2)
							{
							$Path_Variables =  chr(36) . $path_variables_array;
							$id = chr(36) . $path_variables_array;
							}
						}

					// Each Path
					foreach($value as $key2 => $value2)
						{

						$Definition = "";
						$Path = "";
						$Path_Verb = $key2;

						$Path_Summary = $value2['summary'];
						$Path_Desc = $value2['description'];
						$Path_OperationID = $value2['operationId'];
						$Path_Parameters = $value2['parameters'];

						echo $Path_Verb . "<br />";
						echo $Path_Summary . "<br />";

						$Path .= chr(36) . "route = '" . $Path_Route . "';" . chr(13);
						$Path .= chr(36) . "app->" . strtolower($Path_Verb) . "(" . chr(36) . "route, function (" . $Path_Variables . ")  use (" . chr(36) . "app){" . chr(13) . chr(13);

						$Path .= chr(9) . chr(36) . "request = " . chr(36) . "app->request();" . chr(13);
						$Path .= chr(9) . chr(36) . "_GET = " . chr(36) . "request->params();" . chr(13) . chr(13);

						$Path_Responses = $value2['responses'];
						foreach($Path_Responses as $key3 => $value3)
							{

							$Response_Code = $key3;
							$Response_Desc = $value3['description'];
							$Response_Definition = $value3['schema']['items'][chr(36)."ref"];
							$Response_Definition = str_replace("#/definitions/", "", $Response_Definition);

							if($Response_Code=="200")
								{
								$Definition = $Response_Definition;
								}
							}

						foreach($Path_Parameters as $parameter)
							{
							$Parameter_Name = $parameter['name'];
							$Parameter_In = $parameter['in'];
							$Parameter_Desc = $parameter['description'];
							$Parameter_Required = $parameter['required'];
							$Parameter_Type = $parameter['type'];
							echo $Parameter_Name . "(" . $Parameter_In . ")<br />";
							if($Parameter_In=='query')
								{
								$Path .= chr(9) . "if(isset(" . chr(36) . "_GET['" . $Parameter_Name . "'])){ " . chr(36) . $Parameter_Name . " = " . chr(36) . "_GET['" . $Parameter_Name . "']; } else { " . chr(36) . $Parameter_Name . " = '';}" . chr(13);
								}
							}

						// Each Verb
						if($Path_Verb=="get")
							{

							$Path .= chr(13) . chr(9) . chr(36) . "ReturnObject = array();" . chr(13) . chr(13);

							if($id!='')
								{
								$Path .= chr(9) . chr(36) . "Query = " . chr(34) . "SELECT * FROM " . strtolower($Definition) . " WHERE slug = '" . chr(34) . " . " . chr(36) . "slug . " . chr(34) . "'" . chr(34) . ";" . chr(13);
								}
							else
								{
								$Path .= chr(9) . "if(" . chr(36) . "query=='')" . chr(13);
								$Path .= chr(9) . chr(9) . "{" . chr(13);
								$Path .= chr(9) . chr(9) . chr(36) . "Query = " . chr(34) . "SELECT * FROM " . strtolower($Definition) . " WHERE name LIKE '%" . chr(34) . " . " . chr(36) . "query . " . chr(34) . "%'" . chr(34) . ";" . chr(13);
								$Path .= chr(9) . chr(9) . "}" . chr(13);
								$Path .= chr(9) . "else" . chr(13);
								$Path .= chr(9) . chr(9) . "{" . chr(13);
								$Path .= chr(9) . chr(9) . chr(36) . "Query = " . chr(34) . "SELECT * FROM " . strtolower($Definition) . chr(34) . ";" . chr(13);
								$Path .= chr(9) . chr(9) . "}" . chr(13);

								$Path .= chr(13) . chr(9) . chr(36) . "Query .= " . chr(34) . " ORDER BY name ASC" . chr(34) . ";" . chr(13) . chr(13);
								}

							$Path .= chr(9) . chr(36) . "DatabaseResult = mysql_query(" . chr(36) . "Query) or die('Query failed: ' . mysql_error());" . chr(13) . chr(13);

							$Path .= chr(9) . "while (" . chr(36) . "Database = mysql_fetch_assoc(" . chr(36) . "DatabaseResult))" . chr(13);
							$Path .= chr(9) . chr(9) . "{" . chr(13);

							foreach($Swagger_Definitions as $key => $value)
								{
								echo $key . "<br />";
								if($key == $Definition)
									{
									$Definition_Properties = $value['properties'];

									// Incoming
									foreach($Definition_Properties as $key4 => $value4)
										{
										$Definition_Property_Name = $key4;
										echo $Definition_Property_Name . "<br />";

										if(isset($value4['description'])){ $Definition_Property_Desc = $value4['description']; } else { $Definition_Property_Desc = ""; }
										if(isset($value4['type'])){ $Definition_Property_Type = $value4['type']; } else { $Definition_Property_Type = ""; }
										if(isset($value4['format'])){ $Definition_Property_Format = $value4['format']; } else { $Definition_Property_Format = ""; }

										$Path .= chr(9) . chr(9) . chr(36) . $Definition_Property_Name . " = " . chr(36) . "Database['" . $Definition_Property_Name . "'];" . chr(13);
										}

									// Outgoing
									$Path .= chr(13) . chr(9) . chr(9) . chr(36) . "F = array();" . chr(13);
									foreach($Definition_Properties as $key4 => $value4)
										{
										$Definition_Property_Name = $key4;
										echo $Definition_Property_Name . "<br />";

										if(isset($value4['description'])){ $Definition_Property_Desc = $value4['description']; } else { $Definition_Property_Desc = ""; }
										if(isset($value4['type'])){ $Definition_Property_Type = $value4['type']; } else { $Definition_Property_Type = ""; }
										if(isset($value4['format'])){ $Definition_Property_Format = $value4['format']; } else { $Definition_Property_Format = ""; }

										$Path .= chr(9) . chr(9) . chr(36) . "F['" . $Definition_Property_Name . "'] = " . chr(36) . $Definition_Property_Name . ";" . chr(13);
										}
									$Path .= chr(13) . chr(9) . chr(9) . "array_push(" . chr(36) . "ReturnObject, " . chr(36) . "F);" . chr(13) . chr(13);
									}
								}

							$Path .= chr(9) . chr(9) . "}" . chr(13) . chr(13);

							$Path .= chr(9) . chr(36) . "api->response()->header(" . chr(34) . "Content-Type" . chr(34) . ", " . chr(34) . "application/json" . chr(34) . ");" . chr(13);
							$Path .= chr(9) . "echo stripslashes(format_json(json_encode(" . chr(36) . "ReturnObject)));" . chr(13);

							}
						elseif($Path_Verb=="post")
							{

							$Path .= chr(13) . chr(9) . chr(36) . "slug = PrepareFileName(" . chr(36) . "name);" . chr(13). chr(13);
						  	$Path .= chr(13) . chr(9) . chr(36) . "Query = " . chr(34) . "SELECT * FROM " . strtolower($Definition) . " WHERE name = '" . chr(34) . " . " . chr(36) . "name . " . chr(34) . "'" . chr(34) . ";" . chr(13). chr(13);
							$Path .= chr(9) . chr(36) . "Database = mysql_query(" . chr(36) . "Query) or die('Query failed: ' . mysql_error());" . chr(13). chr(13);
							$Path .= chr(9) . "if(" . chr(36) . "Database && mysql_num_rows(" . chr(36) . "Database))" . chr(13);
							$Path .= chr(9) . chr(9) . "{" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "Link = mysql_fetch_assoc(" . chr(36) . "Database);" . chr(13) . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject = array();" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject['message'] = " . chr(34) . ucfirst($Definition) . " Already Exists!" . chr(34) . ";" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject['slug'] = " . chr(36) . "slug;" . chr(13) . chr(13);
							$Path .= chr(9) . chr(9) . "}" . chr(13);
							$Path .= chr(9) . "else" . chr(13);
							$Path .= chr(9) . chr(9) . "{" . chr(13);

							$Path .= chr(9) . chr(9) . chr(36) . "query = " . chr(34) . "INSERT INTO " . strtolower($Definition) . "(" . chr(34) . ";" . chr(13) . chr(13);

							foreach($Path_Parameters as $parameter)
								{
								$Parameter_Name = $parameter['name'];
								$Parameter_In = $parameter['in'];
								$Parameter_Desc = $parameter['description'];
								$Parameter_Required = $parameter['required'];
								$Parameter_Type = $parameter['type'];
								//echo $Parameter_Name . "<br />";
								$Path .= chr(9) . chr(9) . "if(isset(" . chr(36) . $Parameter_Name . ")){ " . chr(36) . "query .= " . chr(36) . $Parameter_Name . " . " . chr(34) . "," . chr(34) . "; }" . chr(13);
								}

							$Path .= chr(13) . chr(9) . chr(9) . chr(36) . "query .= " . chr(34) . ") VALUES(" . chr(34) . ";" . chr(13) . chr(13);

							foreach($Path_Parameters as $parameter)
								{
								$Parameter_Name = $parameter['name'];
								$Parameter_In = $parameter['in'];
								$Parameter_Desc = $parameter['description'];
								$Parameter_Required = $parameter['required'];
								$Parameter_Type = $parameter['type'];
								$Path .= chr(9) . chr(9) . "if(isset(" . chr(36) . $Parameter_Name . ")){ " . chr(36) . "query .= " . chr(34) . "'" . chr(34) . " . mysql_real_escape_string(" . chr(36) . $Parameter_Name . ") . " . chr(34) . "'," . chr(34) . "; }" . chr(13);
								}

							$Path .= chr(13) . chr(9) . chr(9) . chr(36) . "query .= " . chr(34) . ")" . chr(34) . ";" . chr(13) . chr(13);

							$Path .= chr(9) . chr(9) . "mysql_query(" . chr(36) . "query) or die('Query failed: ' . mysql_error());" . chr(13) . chr(13);

							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject = array();" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject['message'] = " . chr(34) . ucfirst($Definition) . " Added!" . chr(34) . ";" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject['slug'] = " . chr(36) . "slug;" . chr(13);

							$Path .= chr(9) . chr(9) . "}" . chr(13) . chr(13);

							$Path .= chr(9) . chr(36) . "api->response()->header(" . chr(34) . "Content-Type" . chr(34) . ", " . chr(34) . "application/json" . chr(34) . ");" . chr(13);
							$Path .= chr(9) . "echo stripslashes(format_json(json_encode(" . chr(36) . "ReturnObject)));" . chr(13);

							}
						elseif($Path_Verb=="put")
							{

						  	$Path .= chr(13) . chr(9) . chr(36) . "Query = " . chr(34) . "SELECT * FROM " . strtolower($Definition) . " WHERE slug = '" . chr(34) . " . " . chr(36) . "slug . " . chr(34) . "'" . chr(34) . ";" . chr(13). chr(13);
							$Path .= chr(9) . chr(36) . "Database = mysql_query(" . chr(36) . "Query) or die('Query failed: ' . mysql_error());" . chr(13). chr(13);
							$Path .= chr(9) . "if(" . chr(36) . "Database && mysql_num_rows(" . chr(36) . "Database))" . chr(13);
							$Path .= chr(9) . chr(9) . "{" . chr(13);

							$Path .= chr(9) . chr(9) . chr(36) . "query = " . chr(34) . "UPDATE " . strtolower($Definition) . " SET" . chr(34) . ";" . chr(13) . chr(13);

							foreach($Path_Parameters as $parameter)
								{
								$Parameter_Name = $parameter['name'];
								$Parameter_In = $parameter['in'];
								$Parameter_Desc = $parameter['description'];
								$Parameter_Required = $parameter['required'];
								$Parameter_Type = $parameter['type'];

								$Path .= chr(9) . chr(9) . "if(isset(" . chr(36) . $Parameter_Name . "))" . chr(13);
								$Path .= chr(9) . chr(9) .chr(9) . "{" . chr(13);
								$Path .= chr(9) . chr(9) .chr(9) . chr(36) . "query .= " . chr(34) . $Parameter_Name . "='" . chr(34) . " . mysql_real_escape_string(" . chr(36) . $Parameter_Name . ") . " . chr(34) . "'" . chr(34) . ";" . chr(13);
								$Path .= chr(9) . chr(9) .chr(9) . "}" . chr(13);

								}

							$Path .= chr(13) . chr(9) . chr(9) . chr(36) . "query .= " . chr(34) . " WHERE slug = '" . chr(34) . " . " . chr(36) . "slug . " . chr(34) . "'" . chr(34) . ";" . chr(13);

							$Path .= chr(9) . chr(9) . "mysql_query(" . chr(36) . "query) or die('Query failed: ' . mysql_error());" . chr(13) . chr(13);

							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject = array();" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject['message'] = " . chr(34) . ucfirst($Definition) . " Updated!" . chr(34) . ";" . chr(13);
							$Path .= chr(9) . chr(9) . chr(36) . "ReturnObject['slug'] = " . chr(36) . "slug;" . chr(13);

							$Path .= chr(9) . chr(9) . "}" . chr(13) . chr(13);

							$Path .= chr(9) . chr(36) . "api->response()->header(" . chr(34) . "Content-Type" . chr(34) . ", " . chr(34) . "application/json" . chr(34) . ");" . chr(13);
							$Path .= chr(9) . "echo stripslashes(format_json(json_encode(" . chr(36) . "ReturnObject)));" . chr(13);

							}
						elseif($Path_Verb=="delete")
							{

						  	$Path .= chr(9) . chr(36) . "Query = " . chr(34) . "DELETE FROM " . strtolower($Definition) . " WHERE slug = '" . chr(34) . " . " . chr(36) . "slug . " . chr(34) . "'" . chr(34) . ";" . chr(13). chr(13);
							$Path .= chr(9) . "mysql_query(" . chr(36) . "Query) or die('Query failed: ' . mysql_error());" . chr(13). chr(13);

							$Path .= chr(9) . chr(36) . "ReturnObject = array();" . chr(13);
							$Path .= chr(9) . chr(36) . "ReturnObject['message'] = " . chr(34) . ucfirst($Definition) . " Deleted!" . chr(34) . ";" . chr(13);
							$Path .= chr(9) . chr(36) . "ReturnObject['slug'] = " . chr(36) . "slug;" . chr(13);

							$Path .= chr(13) . chr(9) . chr(36) . "api->response()->header(" . chr(34) . "Content-Type" . chr(34) . ", " . chr(34) . "application/json" . chr(34) . ");" . chr(13);
							$Path .= chr(9) . "echo stripslashes(format_json(json_encode(" . chr(36) . "ReturnObject)));" . chr(13);
							}

						$Path .= chr(13) . chr(9) . "});" . chr(13);
						$Path .= chr(13) . chr(13);

						$Method .= $Path;
						}
					}

				echo "<hr />";

				echo "--definitions--<br />";
				foreach($Swagger_Definitions as $key => $value)
					{
					echo $key . "<br />";
					$Definition_Properties = $value['properties'];
					foreach($Definition_Properties as $key4 => $value4)
						{
						$Definition_Property_Name = $key4;
						echo $Definition_Property_Name . "<br />";

						if(isset($value4['description'])){ $Definition_Property_Desc = $value4['description']; } else { $Definition_Property_Desc = ""; }
						if(isset($value4['type'])){ $Definition_Property_Type = $value4['type']; } else { $Definition_Property_Type = ""; }
						if(isset($value4['format'])){ $Definition_Property_Format = $value4['format']; } else { $Definition_Property_Format = ""; }

						echo $Definition_Property_Type . "<br />";
						echo $Definition_Property_Desc . "<br />";
						}
					//var_dump($value);
					echo "<hr />";
					}

        $Method .= "?>" . chr(13);

  			$AccountFolder = "/var/www/html/adopta_agency/open-referral-api/api/methods/";
  			$MethodName = "index.php";
  			$MethodFile = $AccountFolder . $MethodName;
  			echo "Writing: " . $MethodFile . "<br />";
  			$fp = fopen($MethodFile, "w+");
  			fwrite($fp, $Method);
  			fclose($fp);

				}
			}
		}
	});
?>
