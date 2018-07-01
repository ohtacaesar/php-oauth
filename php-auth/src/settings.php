<?php

$ini = parse_ini_file('/var/run/secrets/secrets.ini');

define('CLIENT_ID', $ini['client_id']);
define('CLIENT_SECRET', $ini['client_secret']);


/**
 * @param string $url
 * @param array $params
 * @return array
 */
function http_get(string $url, array $params = [])
{
    if ($params) {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Awesome-Octocat-App');
    $str = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$str, $httpCode];
}

/**
 * @param string $url
 * @param array $params
 * @return array
 */
function http_post(string $url, array $params = [])
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Awesome-Octocat-App");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $str = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$str, $httpCode];
}

/**
 * @param $accessToken
 * @return array|false
 */
function fetchUserInfo($accessToken)
{
    list($str, $status) = http_get('https://api.github.com/user', ['access_token' => $accessToken]);
    if ($status !== 200) {
        return false;
    }

    $data = json_decode($str, true);
    if (!$data) {
        return false;
    }

    $user = [];
    foreach (['id', 'login', 'name'] as $key) {
        $user[$key] = $data[$key];
    }

    return $user;
}
