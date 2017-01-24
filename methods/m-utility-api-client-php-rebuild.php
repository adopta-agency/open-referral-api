<?php
$route = '/utility/api/client-php/rebuild/';
$app->get($route, function ()  use ($app){

	$ReturnObject = array();

  	$request = $app->request();
  	$params = $request->params();

	$APIsJSONURL = $params['apis_json_url'];
	$Resource_Store_File = "apis.json";

	$APIsJSONContent = file_get_contents($APIsJSONURL);
	$APIsJSON = json_decode($APIsJSONContent,true);	
	
	$site_url = $APIsJSON['url'];

	foreach($APIsJSON['apis'] as $APIsJSON)
		{

		$properties = $APIsJSON['properties'];

		foreach($properties as $property)
			{

			$property_type = $property['type'];
			//echo $property_type;

			if(strtolower($property_type)=="x-openapi-spec")
				{

				$swagger_url = $property['url'];
				if(substr($swagger_url, 0,4) != "http") { $swagger_url = $site_url . $swagger_url; }

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

				$Swagger_Definitions = $Swagger['definitions'];

				$Swagger_Paths = $Swagger['paths'];
				foreach($Swagger_Paths as $key => $value)
					{

					$Path_Route = $key;

					// Each Path Variable
					$id = 0;
					$Path_Variable_Count = 1;
					$Path_Variables = "";
					$Begin_Tag = "{";
					$End_Tag = "}";
					$path_variables_array = return_between($Path_Route, $Begin_Tag, $End_Tag, EXCL);

					$Path_Route = str_replace("{",":",$Path_Route);
					$Path_Route = str_replace("}","",$Path_Route);
				

					// Each Path
					foreach($value as $key2 => $value2)
						{

		
						$Method = "";
						$Method .= "<?php" . chr(13);

						$PHP_File_Name = "";
	
						$Definition = "";
						$Path = "";
						$Path_Verb = $key2;					

						$Path_Summary = $value2['summary'];
						$Path_Desc = $value2['description'];
						$Path_OperationID = $value2['operationId'];
						
						if(isset($value2['parameters']))
							{
							$Path_Parameters = $value2['parameters'];
							}
						else 
							{
							$Path_Parameters = array();
							}


						if($Path_Verb=="post")
							{
																										
													
							$pos = strpos($Path_Route, ':');
							if ($pos !== false) {
								$patharray = explode("/",$Path_Route);
								if(count($patharray) == 5)
									{
									echo "<strong>" . $Path_Route . "</strong><br /><br />";
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . $Parameter_Name . " = " . chr(36). "item['" . $Parameter_Name . "'];";											
											echo $field . "<br />";				
											}
										}									
									echo "<br />";
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . "item['" . $Parameter_Name . "'] = " . chr(36) . $Parameter_Name . ";";											
											echo $field . "<br />";				
											}
										}									
									echo "<br />";									
									
								foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . 'query .= "' . $Parameter_Name . ',";';
											echo $field . "<br />";				
											}
										}									
									echo "<br />";																										
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . 'query .= ",' . $Parameter_Name . '=' . chr(39) . '" . mysql_real_escape_string(' . chr(36) . $Parameter_Name . ') . "' . chr(39) . '";';
											echo $field . "<br />";				
											}
										}									
									echo "<br />";	
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . 'query .= "' . chr(39) . '" . mysql_real_escape_string(' . chr(36) . $Parameter_Name . ') . "' . chr(39) . ',";';
											echo $field . "<br />";				
											}
										}									
									echo "<br />";																
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . $Parameter_Name . " =  mysql_real_escape_string(" . chr(36). "_GET['" . $Parameter_Name . "']);";
											echo $field . "<br />";				
											}
										}									
									echo "<br />";									
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = "'" . $Parameter_Name . "' => urlencode(" . chr(36) . $Parameter_Name . "),";
											echo $field . "<br />";				
											}
										}									
									echo "<br />";
										
									$field = "";									
									foreach($Path_Parameters as $parameter)
										{										
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];

											 $field .= '<div class="form-group">' . chr(13);
											 $field .= '   <label for="name">' . $Parameter_Name . ':</label>' . chr(13);
											 $field .= '   <input type="text" class="form-control" id="' . $Parameter_Name . '" name="' . $Parameter_Name . '">' . chr(13);
											 $field .= '</div>' . chr(13);
											 												
											}																																	
										}									
									?><strong>Add:</strong><textarea rows="10" cols="150"><?php echo $field; ?></textarea><br /><?php											
																				
										
									$field = "";									
									foreach($Path_Parameters as $parameter)
										{										
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];

											 $field .= '<div class="form-group">' . chr(13);
											 $field .= '   <label for="name">' . $Parameter_Name . ':</label>' . chr(13);
											 $field .= '   <input type="text" class="form-control" id="' . $Parameter_Name . '" name="' . $Parameter_Name . '" value="<?php echo ' . chr(36) . $Parameter_Name . '; ?>">' . chr(13);
											 $field .= '</div>' . chr(13);
											 												
											}																																	
										}									
									?><strong>Edit:</strong><textarea rows="10" cols="150"><?php echo $field; ?></textarea><br /><?php
											
	
									echo "<br /><hr /><br />";																			
									
									}
								} 
							else 
								{
							   	echo "<strong>" . $Path_Route . "</strong><br /><br />";
								
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . $Parameter_Name . " = " . chr(36). "item['" . $Parameter_Name . "'];";											
											echo $field . "<br />";				
											}
										}									
									echo "<br />";
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . 'query .= "' . $Parameter_Name . ',";';
											echo $field . "<br />";				
											}
										}									
									echo "<br />";										
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . 'query .= ",' . $Parameter_Name . '=' . chr(39) . '" . mysql_real_escape_string(' . chr(36) . $Parameter_Name . ') . "' . chr(39) . '";';
											echo $field . "<br />";				
											}
										}									
									echo "<br />";	
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . 'query .= "' . chr(39) . '" . mysql_real_escape_string(' . chr(36) . $Parameter_Name . ') . "' . chr(39) . ',";';
											echo $field . "<br />";				
											}
										}									
									echo "<br />";																			
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = chr(36) . $Parameter_Name . " =  filter_var(" . chr(36). "_GET['" . $Parameter_Name . "'], FILTER_SANITIZE_STRING);";
											echo $field . "<br />";				
											}
										}									
									echo "<br />";									
									
									foreach($Path_Parameters as $parameter)
										{
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];
											$field = "'" . $Parameter_Name . "' => urlencode(" . chr(36) . $Parameter_Name . "),";
											echo $field . "<br />";				
											}
										}									
									echo "<br />";
										
									$field = "";									
									foreach($Path_Parameters as $parameter)
										{										
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];

											 $field .= '<div class="form-group">' . chr(13);
											 $field .= '   <label for="name">' . $Parameter_Name . ':</label>' . chr(13);
											 $field .= '   <input type="text" class="form-control" id="' . $Parameter_Name . '" name="' . $Parameter_Name . '">' . chr(13);
											 $field .= '</div>' . chr(13);
											 												
											}																																	
										}									
									?><strong>Add:</strong><textarea rows="10" cols="150"><?php echo $field; ?></textarea><br /><?php											
																				
										
									$field = "";									
									foreach($Path_Parameters as $parameter)
										{										
										if(isset($parameter['name']))
											{
											$Parameter_Name = $parameter['name'];

											 $field .= '<div class="form-group">' . chr(13);
											 $field .= '   <label for="name">' . $Parameter_Name . ':</label>' . chr(13);
											 $field .= '   <input type="text" class="form-control" id="' . $Parameter_Name . '" name="' . $Parameter_Name . '" value="<?php echo ' . chr(36) . $Parameter_Name . '; ?>">' . chr(13);
											 $field .= '</div>' . chr(13);
											 												
											}																																	
										}									
									?><strong>Edit:</strong><textarea rows="10" cols="150"><?php echo $field; ?></textarea><br /><?php
																			
								
								}						

							}
						
						}
					}
				}
			}
		}	

	//$app->response()->header("Content-Type", "application/json");
	echo stripslashes(format_json(json_encode($ReturnObject, ENT_QUOTES)));	

	});
?>
