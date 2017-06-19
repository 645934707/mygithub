<?php
/**
* curl get data and return curl_errorcode
* @param string $url url
* @param string $timeOut timeout
* @return array data&errorcode
*/
function ccurl($url, $timeOut = 30) {
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeOut );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt($ch, CURLOPT_POST, 1);//post-1 get-0
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//Skip the certificate if https
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//Skip the certificate if https
    $rs = curl_exec ( $ch );
    $no = curl_errno($ch);
    $result = array(
        'errorno'=>$no,
        'data'=>$rs,
    );
    curl_close($ch);
    return $result;//return data and errorcode
}

/** 
 * curl POST 
 * 
 * @param   string  url
 * @param   array   数据 
 * @param   int     请求超时时间 
 * @param   string $capath 证书的完整地址 
 * @param   bool    HTTPS时是否进行严格认证 
 * @return  mixed 成功返回对方的回应数据 
 */  
function curlPost($url, $data = array(), $timeout = 30,$capath, $CA = true){ 
    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    if ($SSL && $CA) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);//只信任CA颁布的证书  
        curl_setopt($ch, CURLOPT_CAINFO, $capath); // CA根证书（用来验证的网站证书是否是CA颁布）
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
    } else if ($SSL && !$CA) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //避免data数据过长问题
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //data with URLEncode
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
* curl连接FTP上传文件
* 
* @param string $file 要上传的文件
* @param strimg $destfile 上传到服务器相对文件名
* @param string $server FTP服务器地址
* @param string $user  FTP账号
* @param string $pass FTP密码
* @param string $certpath 证书完整路径
* @param integer $port 端口 默认21
* @param integer $timeout 超时时间  默认30
* @return Boolean 成功返回true
* 
*/
function ftpUpload($file, $destfile, $server, $user, $pass,$certpath, $port=21, $timeout=30){
    $fp = fopen($file , 'r');//打开文件
    if(!$fp){
        return false;
    }
    $ftp_server = 'ftps://'.$server.':'.$port.'/'.$destfile; 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL , $ftp_server);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERPWD ,$user.':'.$pass);
    curl_setopt($ch, CURLOPT_CAINFO , $certpath);//证书
    curl_setopt($ch, CURLOPT_FTP_SSL , CURLFTPSSL_TRY);
    curl_setopt($ch, CURLOPT_FTPSSLAUTH , CURLFTPAUTH_TLS);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 0 );
    curl_setopt($ch, CURLOPT_UPLOAD , 1);
    curl_setopt($ch, CURLOPT_INFILE , $fp);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;

}
$file = './test.jpg';//要上传的文件
$destfile = '/test.jpg';
$server = 'ftps.com';//FTP服务器
$user = 'ftpuser';
$pass = 'ftppass';
$ftpcertpath = '/data/www/CA.crt';//证书
var_dump(ftpUpload($file,$destfile,$server,$user,$pass,$ftpcertpath));
echo '-----------';

$cacert = '/data/www/CA.crt'; //CA根证书
$url = 'https://https.com/test';
$data = '{"test":"test"}';
var_dump(curlPost($url,$data,30,$cacert));