<?php

$config = yaml_parse_file('config.yml');

$pdo = new PDO(
    $config['pdo']['dsn'],
    $config['pdo']['username'],
    $config['pdo']['passwd']
);

$sql = file_get_contents('schema.sql');

$pdo->exec($sql);
