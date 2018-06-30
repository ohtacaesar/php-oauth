<?php

$pdo = new \PDO(
    'pgsql:host=postgres;port=5432;dbname=postgres',
    'postgres',
    null,
    [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$stmt = $pdo->query('select * from users');
$users = null;
if ($stmt) {
    $users = $stmt->fetchAll();
    $stmt->closeCursor();
}

?><!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Users</title>
</head>

<body>
<?php if (is_null($users)) echo 'ユーザー情報の取得に失敗しました。' ?>
<table>
    <thead>
    <tr>
        <th>id</th>
        <th>login</th>
        <th>name</th>
        <th>created_at</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($users) foreach ($users as $user): ?>
        <tr>
            <td><? echo $user['id'] ?></td>
            <td><? echo $user['login'] ?></td>
            <td><? echo $user['name'] ?></td>
            <td><? echo $user['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>

</html>
