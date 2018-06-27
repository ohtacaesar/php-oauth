<?php


$tmp = parse_url($_SERVER['HTTP_HOST']);
$serverHost = $tmp["host"];

$serverHost = array_reverse(explode(".", $serverHost));

$defaultUrl = "//${serverHost[1]}.${serverHost[0]}";
if (isset($tmp["port"])) {
    $defaultUrl .= ':' . $tmp["port"];
}

if (isset($_GET["from"])) {
    $redirectUrl = $_GET["from"];
    $redirectUrl = filter_var($redirectUrl, FILTER_VALIDATE_URL);
    $redirectHost = parse_url($redirectUrl)['host'];

    $redirectHost = array_reverse(explode(".", $redirectHost));

    if ($redirectHost[0] !== $serverHost[0] || $redirectHost[1] !== $serverHost[1]) {
        $redirectUrl = $defaultUrl;
    }
} else {
    $redirectUrl = $defaultUrl;
}

session_start();
session_destroy();

header('Location: '. $redirectUrl);
