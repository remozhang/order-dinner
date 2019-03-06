<?php
require_once 'AipOcr.php';

$config = require_once('src/config.php');
// 你的 APPID AK SK
$baiduAI = $config['baiduAI'];
$client = new AipOcr($baiduAI['appId'], $baiduAI['apiKey'], $baiduAI['secretKey']);

// 思路: 设置请求图片cookie与请求订餐cookie一致即可
$fileUrl = 'http://techbbs.hi2000.com/dinner/test.php';
$orderUrl = 'http://techbbs.hi2000.com/dinner/dinner.php';

$savePath = './img/';
$phpSessId = rand();
$captcha = downloadCaptcha($fileUrl, $savePath,$phpSessId);
$orderAccount = $config['orderAccount'];
order($client, $captcha, $orderAccount, $orderUrl, $phpSessId);
function order($client, $captcha, $orderAccount, $orderUrl, $phpSessId, $i = 0)
{
    try {
        $resultCaptcha = $client->basicAccurate($captcha);

        if (!isset($resultCaptcha['words_result']['0']['words'])) {
            throw new Exception('识别验证码识别失败!');
        } else {
            $words = $resultCaptcha['words_result']['0']['words'];
        }
        if (strlen($words) < 4 ) {
            throw new Exception('验证码识别位数不够!');
        }

        $data = array(
            'username' => mb_convert_encoding($orderAccount['username'], 'GBK', 'UTF-8'),
            'validate' => mb_convert_encoding($orderAccount['validate'], 'GBK', 'UTF-8'),
            'reserve' => mb_convert_encoding('订餐', 'GBK', 'UTF-8'),
            'code' => $words
        );

        $response = orderDinner($orderUrl, $data, $phpSessId);
        $response = mb_convert_encoding($response, 'UTF-8', 'GBK');
        if (!preg_match('/订餐成功/', $response)) {
            throw new Exception($response);
        }
        file_put_contents('error.log', date('Y-m-d H:i:s') . ': 订餐成功!' . "\n", FILE_APPEND);

    } catch (Exception $e) {
        $i++;
        if ($i > 10) {
            $log = '今天运行订餐已经连续失败10次!';
            file_put_contents('error.log', date('Y-m-d H:i:s') . ': ' . $log . "\n", FILE_APPEND);

        } else {
            $log = $e->getMessage();
            file_put_contents('error.log', date('Y-m-d H:i:s') . ': ' . $log . "\n", FILE_APPEND);
            order($client, $captcha, $orderAccount, $orderUrl, $phpSessId, $i);
        }
    }

}

/**
 * @param $url
 * @param $data
 * @param null $phpSessId
 *
 * @return mixed
 */
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
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

/**
 * @param $fileUrl
 * @param $savePath
 * @param null $phpSessId
 *
 * @return bool|string
 */
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