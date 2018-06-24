<?php

$ini = parse_ini_file('/var/run/secrets/secrets.ini');
define('CLIENT_ID', $ini['client_id']);
define('CLIENT_SECRET', $ini['client_secret']);

function setUserInfo($accessToken)
{
    $url = "https://api.github.com/user?" . http_build_query(['access_token' => $accessToken]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Awesome-Octocat-App');
    $str = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($str, true);
    foreach (['login', 'avatar_url', 'id', 'node_id'] as $key) {
        $_SESSION[$key] = $data[$key];
    }

    return 0;
}

function setAccessToken($code)
{
    $ch = curl_init('https://github.com/login/oauth/access_token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Awesome-Octocat-App");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'code' => $code,
    ]);
    $str = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    parse_str($str, $data);

    $accessToken = $data['access_token'];
    $_SESSION["access_token"] = $accessToken;

}

session_start();

if (isset($_SESSION['id'])) {
    var_dump($_SESSION);
    return;
}

if (isset($_SESSION['access_token'])) {
    setUserInfo($_SESSION['access_token']);
    return;
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    setAccessToken($code);
    $accessToken = $_SESSION['access_token'];
    setUserInfo($accessToken);

    return;
}

$query = http_build_query([
    'client_id' => CLIENT_ID,
    'scope' => 'read:user',
]);
$url = 'https://github.com/login/oauth/authorize?' . $query;
header("Location: ${url}");

return;
