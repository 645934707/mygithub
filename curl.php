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