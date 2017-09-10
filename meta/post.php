<?php
        
$response_body = "";
if(isset($ReturnObject))
	{
	$response_body = $ReturnObject;	
	} 
	
$meta_timestamp = date('Y-m-d H:i:s');
$status = "Complete";

$m = array();
$meta['timestamp'] = $meta_timestamp;
$meta['appid'] = $appid;
$meta['system'] = $openapi['hsda-default-system'];
$meta['path'] = $meta_path;
$meta['verb'] = $meta_verb;
$meta['request_parameters'] = $request_parameters;
$meta['request_headers'] = $request_headers;
$meta['request_body'] = $request_body;
$meta['response_body'] = $ReturnObject;
$meta['status'] = $status;

$meta_base_url = $openapi['hsda-meta']['schemes'][0] . '://' . $openapi['hsda-meta']['host'] . $openapi['hsda-management']['basePath'];
$meta_base_url = $meta_base_url . 'meta/';
//echo "$meta_base_url url: " . $meta_base_url . "<br />";

$fields = "";
$body_string = json_encode($meta);
//var_dump($body_string);
$headers = array('x-appid: ' . $admin_login,'x-appkey: ' . $admin_code);

$http = curl_init();

curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($http,CURLOPT_URL, $meta_base_url);
curl_setopt($http,CURLOPT_POST,count($meta));
curl_setopt($http,CURLOPT_POSTFIELDS, $body_string);
curl_setopt($http, CURLOPT_HTTPHEADER, $headers); 

$output = curl_exec($http);
$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
$info = curl_getinfo($http);

//echo $output;

$filesJson = json_decode($output,true);

//var_dump($filesJson);
?>