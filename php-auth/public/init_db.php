<?php

$sql = file_get_contents('schema.sql');
if ($sql === false) {
    echo 'fail load';

    return;
}


$pdo = new PDO(
    'pgsql:host=postgres;port=5432;dbname=postgres',
    'postgres',
    null,
    [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

if($pdo->exec($sql) === false) {
    echo 'fail to execute query.';
}
