<?php


$conf = yaml_parse_file("config.yml");

$pdo = new \PDO(
    $conf['pdo']['dsn'],
    $conf['pdo']['username'],
    $conf['pdo']['passwd'],
    [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

var_dump($pdo->query("select 1 as a")->fetchAll());
