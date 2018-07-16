<?php

$config = yaml_parse_file('config.yml');

session_start(['read_and_close' => 1]);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>test SESSION DUMP</title>
</head>

<body>

<p>
    <a href="//auth.localhost.dev/github.php?from=http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ?>">ログイン</a>
    |
    <a href="//auth.localhost.dev/logout.php?from=http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ?>">ログアウト</a>
    <?php if (isset($_SESSION['login'])): ?>| ログイン済み<?php endif; ?>
</p>

<ul>
    <li><a href="phpinfo.php">phpinfo</a></li>
    <li><a href="postgresql.php">postgresql</a></li>
    <li><a href="http://auth.localhost.dev/users.php">users</a></li>
</ul>

<pre><?php var_dump($config) ?></pre>
<pre><?php var_dump($_SESSION) ?></pre>
<pre><?php var_dump($_SERVER) ?></pre>

</body>

</html>
