<pre><?php

$config = yaml_parse_file('config.yml');

$pdo = new PDO(
    $config['pdo']['dsn'],
    $config['pdo']['username'],
    $config['pdo']['passwd']
);

$stmt = $pdo->query('select 1 as a');

var_dump($stmt->fetch());

$stmt->closeCursor();


