<?php

$meta_path = $route;
$meta_verb = $verb;

// Grab All The Headers
$request_headers = "";
if(isset($_get))
	{
	foreach ($head as $name => $values) {
	    $request_headers .= $name . "=" . $values . ",";
	}
	$request_headers = substr($request_headers,0,strlen($request_headers)-1);
	}
	
// Grab all the parametes
$request_parameters = "";
if(isset($_get))
	{
	foreach ($_get as $name => $values) 
		{
    	$request_parameters .= $name . "=" . $values . ",";
		}
	$request_parameters = substr($request_parameters,0,strlen($request_parameters)-1);	
	}

$request_body = "";
if(isset($_body))
	{
	$request_body = $_body;	
	}
?>