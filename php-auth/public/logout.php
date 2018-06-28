<?php

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

if ($redirectUrl === null) {
    $redirectUrl = $defaultUrl;
}

session_start();
session_destroy();

header('Location: ' . $redirectUrl);
