<?php

$cookieDomain = '.localhost.test';

session_start([
    'cookie_domain' => $cookieDomain
]);

if(isset($_SESSION["id"])) {
    echo $_SESSION["id"];
}
