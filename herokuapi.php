<?php
function HerokuAPI($method, $url, $data = '', $apikey)
{
    if ($method=='PATCH') {
        $headers['Content-Type'] = 'application/json';
    } 
    $headers['Authorization'] = 'Bearer ' . $apikey;
    $headers['Accept'] = 'application/vnd.heroku+json; version=3';
    //if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    //if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$method);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    $response['body'] = curl_exec($ch);
    $response['stat'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    error_log($response['stat'].'
'.$response['body'].'
');
    return $response;
}

function getHerokuConfig($function_name, $apikey)
{
    return HerokuAPI('GET', 'https://api.heroku.com/apps/' . $function_name . '/config-vars', '', $apikey);
}

function setHerokuConfig($function_name, $env, $apikey)
{
    $data = json_encode($env);
    return HerokuAPI('PATCH', 'https://api.heroku.com/apps/' . $function_name . '/config-vars', $data, $apikey);
}
?>
