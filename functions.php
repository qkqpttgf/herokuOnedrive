<?php

function config_oauth()
{
    global $constStr;
    $constStr['language'] = $_COOKIE['language'];
    if ($constStr['language']=='') $constStr['language'] = getenv('language');
    if ($constStr['language']=='') $constStr['language'] = 'en-us';
    $_SERVER['sitename'] = getenv('sitename');
    if (empty($_SERVER['sitename'])) $_SERVER['sitename'] = $constStr['defaultSitename'][$constStr['language']];
    $_SERVER['redirect_uri'] = 'https://scfonedrive.github.io';

    if (getenv('Onedrive_ver')=='MS') {
        // MS
        // https://portal.azure.com
        $_SERVER['client_id'] = '4da3e7f2-bf6d-467c-aaf0-578078f0bf7c';
        $_SERVER['client_secret'] = '7/+ykq2xkfx:.DWjacuIRojIaaWL0QI6';
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
    }
    if (getenv('Onedrive_ver')=='CN') {
        // CN
        // https://portal.azure.cn
        $_SERVER['client_id'] = '04c3ca0b-8d07-4773-85ad-98b037d25631';
        $_SERVER['client_secret'] = 'h8@B7kFVOmj0+8HKBWeNTgl@pU/z4yLB';
        $_SERVER['oauth_url'] = 'https://login.partner.microsoftonline.cn/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://microsoftgraph.chinacloudapi.cn/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://microsoftgraph.chinacloudapi.cn/Files.ReadWrite.All offline_access';
    }
    if (getenv('Onedrive_ver')=='MSC') {
        // MS Customer
        // https://portal.azure.com
        $_SERVER['client_id'] = getenv('client_id');
        $_SERVER['client_secret'] = base64_decode(equal_replace(getenv('client_secret'),1));
        $_SERVER['oauth_url'] = 'https://login.microsoftonline.com/common/oauth2/v2.0/';
        $_SERVER['api_url'] = 'https://graph.microsoft.com/v1.0/me/drive/root';
        $_SERVER['scope'] = 'https://graph.microsoft.com/Files.ReadWrite.All offline_access';
    }

    $_SERVER['client_secret'] = urlencode($_SERVER['client_secret']);
    $_SERVER['scope'] = urlencode($_SERVER['scope']);
}

function clearbehindvalue($path,$page1,$maxpage,$pageinfocache)
{
    for ($page=$page1+1;$page<$maxpage;$page++) {
        $pageinfocache['nextlink_' . $path . '_page_' . $page] = '';
    }
    return $pageinfocache;
}

function comppass($pass)
{
    if ($_POST['password1'] !== '') if (md5($_POST['password1']) === $pass ) {
        date_default_timezone_set('UTC');
        $_SERVER['Set-Cookie'] = 'password='.$pass.'; expires='.date(DATE_COOKIE,strtotime('+1hour'));
        date_default_timezone_set(get_timezone($_COOKIE['timezone']));
        return 2;
    }
    if ($_COOKIE['password'] !== '') if ($_COOKIE['password'] === $pass ) return 3;
    return 4;
}

function curl_request($url, $data = false, $headers = [])
{
    if (!isset($headers['Accept'])) $headers['Accept'] = '*/*';
    if (!isset($headers['Referer'])) $headers['Referer'] = $url;
    if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'application/x-www-form-urlencoded';
    $sendHeaders = array();
    foreach ($headers as $headerName => $headerVal) {
        $sendHeaders[] = $headerName . ': ' . $headerVal;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    if ($data !== false) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendHeaders);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function encode_str_replace($str)
{
    $str = str_replace('&','&amp;',$str);
    $str = str_replace('+','%2B',$str);
    $str = str_replace('#','%23',$str);
    return $str;
}

function equal_replace($str, $add = false)
{
    if ($add) {
        while(strlen($str)%4) $str .= '=';
    } else {
        while(substr($str,-1)=='=') $str=substr($str,0,-1);
    }
    return $str;
}

function gethiddenpass($path,$passfile)
{
    $ispassfile = fetch_files(spurlencode(path_format($path . '/' . $passfile),'/'));
    //echo $path . '<pre>' . json_encode($ispassfile, JSON_PRETTY_PRINT) . '</pre>';
    if (isset($ispassfile['file'])) {
        $passwordf=explode("\n",curl_request($ispassfile['@microsoft.graph.downloadUrl']));
        $password=$passwordf[0];
        $password=md5($password);
        return $password;
    } else {
        if ($path !== '' ) {
            $path = substr($path,0,strrpos($path,'/'));
            return gethiddenpass($path,$passfile);
        } else {
            return '';
        }
    }
    return '';
}

function get_refresh_token($function_name, $Region, $Namespace)
{
    global $constStr;
    $url = path_format($_SERVER['PHP_SELF'] . '/');

    if ($_GET['authorization_code'] && isset($_GET['code'])) {
        $ret = json_decode(curl_request($_SERVER['oauth_url'] . 'token', 'client_id=' . $_SERVER['client_id'] .'&client_secret=' . $_SERVER['client_secret'] . '&grant_type=authorization_code&requested_token_use=on_behalf_of&redirect_uri=' . $_SERVER['redirect_uri'] .'&code=' . $_GET['code']), true);
        if (isset($ret['refresh_token'])) {
            $tmptoken=$ret['refresh_token'];
            $str = '
        refresh_token :<br>';
            /*for ($i=1;strlen($tmptoken)>0;$i++) {
                $t['t' . $i] = substr($tmptoken,0,128);
                $str .= '
            t' . $i . ':<textarea readonly style="width: 95%">' . $t['t' . $i] . '</textarea><br><br>';
                $tmptoken=substr($tmptoken,128);
            }
            $str .= '
        Add t1-t'.--$i.' to environments.*/
            $str .= '
        <textarea readonly style="width: 95%">' . $tmptoken . '</textarea><br><br>
        Add refresh_token to environments.
        <script>
            var texta=document.getElementsByTagName(\'textarea\');
            for(i=0;i<texta.length;i++) {
                texta[i].style.height = texta[i].scrollHeight + \'px\';
            }
            document.cookie=\'language=; path=/\';
        </script>';
            if (getenv('APIKey')!='') {
                setHerokuConfig($function_name, [ 'refresh_token' => $tmptoken ], getenv('APIKey'));
                $str .= '
            <meta http-equiv="refresh" content="5;URL=' . $url . '">';
            }
            return message($str, $constStr['WaitJumpIndex'][$constStr['language']]);
        }
        return message('<pre>' . json_encode($ret, JSON_PRETTY_PRINT) . '</pre>', 500);
    }

    if ($_GET['install2']) {
        if (getenv('Onedrive_ver')=='MS' || getenv('Onedrive_ver')=='CN' || getenv('Onedrive_ver')=='MSC') {
            return message('
    <a href="" id="a1">'.$constStr['JumptoOffice'][$constStr['language']].'</a>
    <script>
        url=location.protocol + "//" + location.host + "'.$url.'";
        url="'. $_SERVER['oauth_url'] .'authorize?scope='. $_SERVER['scope'] .'&response_type=code&client_id='. $_SERVER['client_id'] .'&redirect_uri='. $_SERVER['redirect_uri'] . '&state=' .'"+encodeURIComponent(url);
        document.getElementById(\'a1\').href=url;
        //window.open(url,"_blank");
        location.href = url;
    </script>
    ', $constStr['Wait'][$constStr['language']].' 1s', 201);
        }
    }

    if ($_GET['install1']) {
        // echo $_POST['Onedrive_ver'];
        if ($_POST['Onedrive_ver']=='MS' || $_POST['Onedrive_ver']=='CN' || $_POST['Onedrive_ver']=='MSC') {
            $tmp['Onedrive_ver'] = $_POST['Onedrive_ver'];
            $tmp['language'] = $_COOKIE['language'];
            if ($_POST['Onedrive_ver']=='MSC') {
                $tmp['client_id'] = $_POST['client_id'];
                $tmp['client_secret'] = equal_replace(base64_encode($_POST['client_secret']));
            }
            $response = json_decode(setHerokuConfig($function_name, $tmp, getenv('APIKey')), true);
            sleep(2);
            $title = $constStr['MayinEnv'][$constStr['language']];
            $html = $constStr['Wait'][$constStr['language']] . ' 3s<meta http-equiv="refresh" content="3;URL=' . $url . '?install2">';
            if (isset($response['id'])&&isset($response['message'])) {
            $html = $response['id'] . '<br>
' . $response['message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = 'Error';
            }
            return message($html, $title, 201);
        }
    }

    if ($_GET['install0']) {
        if (getenv('APIKey')=='') return message($constStr['SetSecretsFirst'][$constStr['language']].'<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button><br>'.'(<a href="https://dashboard.heroku.com/account" target="_blank">'.' API Key</a>)', 'Error', 500);
        $response = json_decode(setHerokuConfig($function_name, [ 'function_name' => $function_name ], getenv('APIKey')), true);
        if (isset($response['id'])&&isset($response['message'])) {
            $html = $response['id'] . '<br>
' . $response['message'] . '<br><br>
function_name:' . $_SERVER['function_name'] . '<br>
<button onclick="location.href = location.href;">'.$constStr['Reflesh'][$constStr['language']].'</button>';
            $title = 'Error';
        } else {
            if ($constStr['language']!='zh-cn') {
                $linklang='en-us';
            } else $linklang='zh-cn';
            $ru = "https://developer.microsoft.com/".$linklang."/graph/quick-start?appID=_appId_&appName=_appName_&redirectUrl=".$_SERVER['redirect_uri']."&platform=option-php";
            $deepLink = "/quickstart/graphIO?publicClientSupport=false&appName=HerokuOnedrive&redirectUrl=".$_SERVER['redirect_uri']."&allowImplicitFlow=false&ru=".urlencode($ru);
            $app_url = "https://apps.dev.microsoft.com/?deepLink=".urlencode($deepLink);
            $html = '
    <form action="?install1" method="post">
        Onedrive_Ver：<br>
        <label><input type="radio" name="Onedrive_ver" value="MS" checked>MS: '.$constStr['OndriveVerMS'][$constStr['language']].'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="CN">CN: '.$constStr['OndriveVerCN'][$constStr['language']].'</label><br>
        <label><input type="radio" name="Onedrive_ver" value="MSC" onclick="document.getElementById(\'secret\').style.display=\'\';">MSC: '.$constStr['OndriveVerMSC'][$constStr['language']].'
            <div id="secret" style="display:none">
                <a href="'.$app_url.'" target="_blank">'.$constStr['GetSecretIDandKEY'][$constStr['language']].'</a><br>
                client_secret:<input type="text" name="client_secret"><br>
                client_id(12345678-90ab-cdef-ghij-klmnopqrstuv):<input type="text" name="client_id"><br>
            </div>
        </label><br>
        <input type="submit" value="'.$constStr['Submit'][$constStr['language']].'">
    </form>';
            $title = 'Install';
        }
        return message($html, $title, 201);
    }

    $html .= '
    <form action="?install0" method="post">
    language:<br>';
    foreach ($constStr['languages'] as $key1 => $value1) {
        $html .= '
    <label><input type="radio" name="language" value="'.$key1.'" '.($key1==$constStr['language']?'checked':'').' onclick="changelanguage(\''.$key1.'\')">'.$value1.'</label><br>';
    }
    $html .= '<br>
    <input type="submit" value="'.$constStr['Submit'][$constStr['language']].'">
    </form>
    <script>
        function changelanguage(str)
        {
            document.cookie=\'language=\'+str+\'; path=/\';
            location.href = location.href;
        }
    </script>';
    $title = $constStr['SelectLanguage'][$constStr['language']];
    return message($html, $title, 201);
}

function get_timezone($timezone = '8')
{
    $timezones = array( 
        '-12'=>'Pacific/Kwajalein', 
        '-11'=>'Pacific/Samoa', 
        '-10'=>'Pacific/Honolulu', 
        '-9'=>'America/Anchorage', 
        '-8'=>'America/Los_Angeles', 
        '-7'=>'America/Denver', 
        '-6'=>'America/Mexico_City', 
        '-5'=>'America/New_York', 
        '-4'=>'America/Caracas', 
        '-3.5'=>'America/St_Johns', 
        '-3'=>'America/Argentina/Buenos_Aires', 
        '-2'=>'America/Noronha',
        '-1'=>'Atlantic/Azores', 
        '0'=>'UTC', 
        '1'=>'Europe/Paris', 
        '2'=>'Europe/Helsinki', 
        '3'=>'Europe/Moscow', 
        '3.5'=>'Asia/Tehran', 
        '4'=>'Asia/Baku', 
        '4.5'=>'Asia/Kabul', 
        '5'=>'Asia/Karachi', 
        '5.5'=>'Asia/Calcutta', //Asia/Colombo
        '6'=>'Asia/Dhaka',
        '6.5'=>'Asia/Rangoon', 
        '7'=>'Asia/Bangkok', 
        '8'=>'Asia/Shanghai', 
        '9'=>'Asia/Tokyo', 
        '9.5'=>'Australia/Darwin', 
        '10'=>'Pacific/Guam', 
        '11'=>'Asia/Magadan', 
        '12'=>'Asia/Kamchatka'
    );
    if ($timezone=='') $timezone = '8';
    return $timezones[$timezone];
}

function GetGlobalVariable($event)
{
    $_GET = $event['queryString'];
    /*$postbody = explode("&",$event['body']);
    foreach ($postbody as $postvalues) {
        $pos = strpos($postvalues,"=");
        $_POST[urldecode(substr($postvalues,0,$pos))]=urldecode(substr($postvalues,$pos+1));
    }
    $cookiebody = explode("; ",$event['headers']['cookie']);
    foreach ($cookiebody as $cookievalues) {
        $pos = strpos($cookievalues,"=");
        $_COOKIE[urldecode(substr($cookievalues,0,$pos))]=urldecode(substr($cookievalues,$pos+1));
    }*/
}

function GetPathSetting($event, $context)
{
    $_SERVER['function_name'] = $context['function_name'];
    $host_name = $event['headers']['host'];
    $serviceId = $event['requestContext']['serviceId'];
    $public_path = path_format(getenv('public_path'));
    $private_path = path_format(getenv('private_path'));
    $domain_path = getenv('domain_path');
    $tmp_path='';
    if ($domain_path!='') {
        $tmp = explode("|",$domain_path);
        foreach ($tmp as $multidomain_paths){
            $pos = strpos($multidomain_paths,":");
            $tmp_path = path_format(substr($multidomain_paths,$pos+1));
            if (substr($multidomain_paths,0,$pos)==$host_name) $private_path=$tmp_path;
        }
    }
    // public_path is not Parent Dir of private_path. public_path 不能是 private_path 的上级目录。
    if ($tmp_path!='') if ($public_path == substr($tmp_path,0,strlen($public_path))) $public_path=$tmp_path;
    if ($public_path == substr($private_path,0,strlen($public_path))) $public_path=$private_path;
    if ( $serviceId === substr($host_name,0,strlen($serviceId)) ) {
        $_SERVER['base_path'] = '/'.$event['requestContext']['stage'].'/'.$_SERVER['function_name'].'/';
        $_SERVER['list_path'] = $public_path;
        $_SERVER['Region'] = substr($host_name, strpos($host_name, '.')+1);
        $_SERVER['Region'] = substr($_SERVER['Region'], 0, strpos($_SERVER['Region'], '.'));
        $path = substr($event['path'], strlen('/'.$_SERVER['function_name'].'/'));
    } else {
        $_SERVER['base_path'] = '/';//$event['requestContext']['path'];
        $_SERVER['list_path'] = $private_path;
        $_SERVER['Region'] = getenv('Region');
        $path = substr($event['path'], strlen($event['requestContext']['path']));
    }
    if (substr($path,-1)=='/') $path=substr($path,0,-1);
    if (empty($_SERVER['list_path'])) {
        $_SERVER['list_path'] = '/';
    } else {
        $_SERVER['list_path'] = spurlencode($_SERVER['list_path'],'/') ;
    }
    $_SERVER['is_imgup_path'] = is_imgup_path($path);
    $_SERVER['PHP_SELF'] = path_format($_SERVER['base_path'] . $path);
    $_SERVER['REMOTE_ADDR'] = $event['requestContext']['sourceIp'];
    $_SERVER['ajax']=0;
    if ($event['headers']['x-requested-with']=='XMLHttpRequest') {
        $_SERVER['ajax']=1;
    }
/*
    $referer = $event['headers']['referer'];
    $tmpurl = substr($referer,strpos($referer,'//')+2);
    $refererhost = substr($tmpurl,0,strpos($tmpurl,'/'));
    if ($refererhost==$host_name) {
        // Guest only upload from this site. 仅游客上传用，referer不对就空值，无法上传
        $_SERVER['current_url'] = substr($referer,0,strpos($referer,'//')) . '//' . $host_name.$_SERVER['PHP_SELF'];
    } else {
        $_SERVER['current_url'] = '';
    }
*/
    return $path;
}

function is_imgup_path($path)
{
    if (path_format('/'.path_format(urldecode($_SERVER['list_path'].path_format($path))).'/')==path_format('/'.path_format(getenv('imgup_path')).'/')&&getenv('imgup_path')!='') return 1;
    return 0;
}

function message($message, $title = 'Message', $statusCode = 200)
{
    return output('<html><meta charset=utf-8><body><h1>' . $title . '</h1><p>' . $message . '</p></body></html>', $statusCode);
}

function needUpdate()
{
    if ($_SERVER['admin'] && getenv('SecretId')!='' && getenv('SecretKey')!='') {
        $current_ver = file_get_contents(__DIR__ . '/version');
        $current_ver = substr($current_ver, strpos($current_ver, '.')+1);
        $current_ver = explode(urldecode('%0A'),$current_ver)[0];
        $current_ver = explode(urldecode('%0D'),$current_ver)[0];
        $github_version = file_get_contents('https://raw.githubusercontent.com/qkqpttgf/OneDrive_SCF/master/version');
        $github_ver = substr($github_version, strpos($github_version, '.')+1);
        $github_ver = explode(urldecode('%0A'),$github_ver)[0];
        $github_ver = explode(urldecode('%0D'),$github_ver)[0];
        if ($current_ver != $github_ver) {
            $_SERVER['github_version'] = $github_version;
            return 1;
        }
    }
    return 0;
}

function output($body, $statusCode = 200, $headers = ['Content-Type' => 'text/html'], $isBase64Encoded = false)
{
    return [
        'isBase64Encoded' => $isBase64Encoded,
        'statusCode' => $statusCode,
        'headers' => $headers,
        'body' => $body
    ];
}

function passhidden($path)
{
    $path = str_replace('+','%2B',$path);
    $path = str_replace('&amp;','&', path_format(urldecode($path)));
    if (getenv('passfile') != '') {
        if (substr($path,-1)=='/') $path=substr($path,0,-1);
        $hiddenpass=gethiddenpass($path,getenv('passfile'));
        if ($hiddenpass != '') {
            return comppass($hiddenpass);
        } else {
            return 1;
        }
    } else {
        return 0;
    }
    return 4;
}

function path_format($path)
{
    $path = '/' . $path;
    while (strpos($path, '//') !== FALSE) {
        $path = str_replace('//', '/', $path);
    }
    return $path;
}

function printInput($event, $context)
{
    if (strlen(json_encode($event['body']))>500) $event['body']=substr($event['body'],0,strpos($event['body'],'base64')+30) . '...Too Long!...' . substr($event['body'],-50);
    echo urldecode(json_encode($event, JSON_PRETTY_PRINT)) . '
 
' . urldecode(json_encode($context, JSON_PRETTY_PRINT)) . '
 
';
}

function size_format($byte)
{
    $i = 0;
    while (abs($byte) >= 1024) {
        $byte = $byte / 1024;
        $i++;
        if ($i == 3) break;
    }
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $ret = round($byte, 2);
    return ($ret . ' ' . $units[$i]);
}

function spurlencode($str,$splite='')
{
    $str = str_replace(' ', '%20',$str);
    $tmp='';
    if ($splite!='') {
        $tmparr=explode($splite,$str);
        for($x=0;$x<count($tmparr);$x++) {
            if ($tmparr[$x]!='') $tmp .= $splite . urlencode($tmparr[$x]);
        }
    } else {
        $tmp = urlencode($str);
    }
    $tmp = str_replace('%2520', '%20',$tmp);
    return $tmp;
}

function time_format($ISO)
{
    $ISO = str_replace('T', ' ', $ISO);
    $ISO = str_replace('Z', ' ', $ISO);
    //return $ISO;
    return date('Y-m-d H:i:s',strtotime($ISO . " UTC"));
}
