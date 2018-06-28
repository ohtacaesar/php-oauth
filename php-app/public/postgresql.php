<pre><?php

$pdo = new PDO("pgsql:host=postgres;port=5432;dbname=postgres", 'postgres');

$stmt = $pdo->query('select 1 as a');

var_dump($stmt->fetch());

$stmt->closeCursor();


