<?php

//基于7.4  8.0需要改改
require '../cURL.php';

function qie($data)
{
    $key = '0821CAAD409B84020821CAAD';
    $iv = base64_decode("AAAAAAAAAAA=");
    $encrypted = openssl_encrypt($data, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return base64_encode($encrypted);
}

function qse($data)
{
    $key = '{1dYgqE)h9,R)hKqEcv4]k[h';
    $iv = '01234567';
    $encrypted = openssl_encrypt($data, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encrypted);
}

$url = "/v3/bookdetail/get?bookId=1039545713";

print_r(qd_query($url));



function qd_query($uri, $data = "")
{
    if (empty($data)) {
        $parts = explode('?', $uri);
        $str1 = strtolower($parts[1]);
        $parts = explode('&', $str1);
        sort($parts);
        $str1 = implode('&', $parts);
        $time = time() . rand(111, 999);
        $QDInfo = qie("0|7.9.340|1080|2206|1000014|12|1|Mi 12|793|1000014|4|0|$time|0|0|||||1");
        $sign = md5($str1);
        $QDSign = qse("Rv1rPTnczce|$time|0||1|7.9.340|0|$sign|f189adc92b816b3e9da29ea304d4a7e4");
    } else {
        $str1 = strtolower(urldecode($data));
        $parts = explode('&', $str1);
        sort($parts);
        $str1 = implode('&', $parts);
        $time = time() . rand(111, 999);
        $QDInfo = qie("0|7.9.242|1080|2206|1000014|12|1|Mi 12|792|1000014|4|0|$time|0|0|||||1");
        $sign = md5($str1);
        $QDSign = qse("Rv1rPTnczce|$time|0||1|7.9.242|0|$sign|f189adc92b816b3e9da29ea304d4a7e4");
    }

    $headers = [
        "User-Agent" => "Mozilla/mobile QDReaderAndroid/7.9.340/1226/1000014/Redmi",
        "QDInfo" => "$QDInfo",
        "QDSign" => "$QDSign",
        "tstamp" => $time
    ];

    $url = "https://druidv6.if.qidian.com/argus/api$uri";
    if (empty($data))
    {
        $query = cURL::get($url)->header($headers)->jsonobject();
    } else {
        $query = cURL::post($url,$data)->header($headers)->jsonobject();
    }
    return $query;
}
