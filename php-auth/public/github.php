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
    foreach (['login', 'id', 'name'] as $key) {
        $_SESSION[$key] = $data[$key];
    }


    $conf = yaml_parse_file("config.yml");

    $pdo = new \PDO(
        $conf['pdo']['dns'],
        $conf['pdo']['username'],
        $conf['pdo']['passwd'],
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $sql = file_get_contents('schema.sql');
    if ($pdo->exec($sql) === false) {
        return 1;
    }

    $stmt = $pdo->prepare("select * from users where id = :id");
    $stmt->bindValue('id', $_SESSION['id']);
    $stmt->execute();
    $user = $stmt->fetch();
    $stmt->closeCursor();

    if ($user) {
        return 1;
    }

    $stmt = $pdo->prepare("insert into users(login, id, name) values (:login, :id, :name)");
    $stmt->bindValue('login', $_SESSION['login']);
    $stmt->bindValue('id', $_SESSION['id']);
    $stmt->bindValue('name', $_SESSION['name']);
    $stmt->execute();
    $stmt->closeCursor();
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

$tmp = parse_url($_SERVER['HTTP_HOST']);
$serverHost = $tmp["host"];

$serverHost = array_reverse(explode(".", $serverHost));

$defaultUrl = "//${serverHost[1]}.${serverHost[0]}";
if (isset($tmp["port"])) {
    $defaultUrl .= ':' . $tmp["port"];
}

$redirectUrl = null;

if ($redirectUrl === null && isset($_GET["from"])) {
    $from = $_GET["from"];
    $from = filter_var($from, FILTER_VALIDATE_URL);
    $tmp = parse_url($from)['host'];

    $tmp = array_reverse(explode(".", $tmp));

    if ($tmp[0] === $serverHost[0] && $tmp[1] === $serverHost[1]) {
        $redirectUrl = $from;
    }
}

if ($redirectUrl === null and isset($_SESSION["redirectUrl"])) {
    $redirectUrl = $_SESSION["redirectUrl"];
}

if ($redirectUrl === null) {
    $redirectUrl = $defaultUrl;
}

if (isset($_SESSION['id'])) {
    unset($_SESSION["redirectUrl"]);
    header("Location: " . $redirectUrl);
    return;
}

if (isset($_SESSION['access_token'])) {
    setUserInfo($_SESSION['access_token']);

    unset($_SESSION["redirectUrl"]);
    header("Location: " . $redirectUrl);
    return;
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    setAccessToken($code);
    $accessToken = $_SESSION['access_token'];
    setUserInfo($accessToken);

    unset($_SESSION["redirectUrl"]);
    header("Location: " . $redirectUrl);
    return;
}

$_SESSION["redirectUrl"] = $redirectUrl;

$query = http_build_query([
    'client_id' => CLIENT_ID,
    'scope' => 'read:user',
]);
$url = 'https://github.com/login/oauth/authorize?' . $query;
header("Location: ${url}");

return;
