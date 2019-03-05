<?php
require_once 'AipOcr.php';

// 你的 APPID AK SK
const APP_ID = '15672391';
const API_KEY = 'YiE5EkBPaA74ODNsarjQdMKl';
const SECRET_KEY = 'q4eDMERT3ZdvtAZHZh6L6uITpB7DX7Eg';

$client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

// 思路: 设置请求图片cookie与请求订餐cookie一致即可

$fileUrl = 'http://techbbs.hi2000.com/dinner/test.php';
$orderUrl = 'http://techbbs.hi2000.com/dinner/dinner.php';

$savePath = './';
$captcha = downloadCaptcha($fileUrl, $savePath,'zhanglei');

$captcha = $client->basicAccurate($captcha);
$words = $captcha['words_result']['0']['words'];

$data = array(
    'username' => urldecode('%D5%C5%C0%D7'),
    'validate' => '123456',
    'reserve' => urldecode('%B6%A9%B2%CD'),
    'code' => $words
);

orderDinner($orderUrl, $data, 'zhanglei');

function orderDinner(
    $url,
    $data,
    $phpSessId = null
) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $phpSessId);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $haha = curl_exec($ch);
    curl_close($ch);
}

function downloadCaptcha(
    $fileUrl,
    $savePath,
    $phpSessId = null
) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST,0);
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $phpSessId);

    $fileContent = curl_exec($ch);
    curl_close($ch);
    file_put_contents($savePath . 'captcha.jpg', $fileContent);

    return file_get_contents($savePath . 'captcha.jpg');
}



