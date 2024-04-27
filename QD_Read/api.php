<?php

//基于7.4  8.0需要改改
require '../cURL.php';

/**
 * 使用DES-EDE3-CBC加密方法对数据进行加密
 * @param string $data 需要加密的数据
 * @return string 加密后的数据，以base64编码格式返回
 */
function qie($data)
{
    $key = '0821CAAD409B84020821CAAD';
    $iv = base64_decode("AAAAAAAAAAA=");
    $encrypted = openssl_encrypt($data, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return base64_encode($encrypted);
}

/**
 * 使用DES-EDE3-CBC加密方法对数据进行加密，此方法与qie使用不同的密钥和初始化向量
 * @param string $data 需要加密的数据
 * @return string 加密后的数据，以base64编码格式返回
 */
function qse($data)
{
    $key = '{1dYgqE)h9,R)hKqEcv4]k[h';
    $iv = '01234567';
    $encrypted = openssl_encrypt($data, 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encrypted);
}

$url = "/v3/bookdetail/get?bookId=";

// 调用qd_query函数，打印结果
print_r(qd_query($url));

/**
 * 构造请求并发送查询
 * @param string $uri 请求的URI
 * @param string $data 请求附带的数据，为空时从URI解析
 * @return mixed 返回查询结果
 */
function qd_query($uri, $data = "")
{
    // 如果$data为空，从$uri解析出数据部分进行处理
    if (empty($data)) {
        $parts = explode('?', $uri);
        $str1 = strtolower($parts[1]);
        $parts = explode('&', $str1);
        sort($parts);
        $str1 = implode('&', $parts);
        $time = time() . rand(111, 999);
        // 使用qie和qse函数进行加密处理，并构造请求头
        $QDInfo = qie("0|7.9.340|1080|2206|1000014|12|1|Mi 12|793|1000014|4|0|$time|0|0|||||1");
        $sign = md5($str1);
        $QDSign = qse("Rv1rPTnczce|$time|0||1|7.9.340|0|$sign|f189adc92b816b3e9da29ea304d4a7e4");
    } else {
        // 如果$data不为空，对其进行解码并处理
        $str1 = strtolower(urldecode($data));
        $parts = explode('&', $str1);
        sort($parts);
        $str1 = implode('&', $parts);
        $time = time() . rand(111, 999);
        // 使用qie和qse函数进行加密处理，并构造请求头
        $QDInfo = qie("0|7.9.242|1080|2206|1000014|12|1|Mi 12|792|1000014|4|0|$time|0|0|||||1");
        $sign = md5($str1);
        $QDSign = qse("Rv1rPTnczce|$time|0||1|7.9.242|0|$sign|f189adc92b816b3e9da29ea304d4a7e4");
    }

    // 构造请求头
    $headers = [
        "User-Agent" => "Mozilla/mobile QDReaderAndroid/7.9.340/1226/1000014/Redmi",
        "QDInfo" => "$QDInfo",
        "QDSign" => "$QDSign",
        "tstamp" => $time
    ];

    // 完善请求URL，并根据是否有数据选择GET或POST方法发送请求
    $url = "https://druidv6.if.qidian.com/argus/api$uri";
    if (empty($data))
    {
        $query = cURL::get($url)->header($headers)->jsonobject();
    } else {
        $query = cURL::post($url,$data)->header($headers)->jsonobject();
    }
    return $query;
}