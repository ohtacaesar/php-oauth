<?php

session_start(['read_and_close' => 1]);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SESSION DUMP</title>
</head>

<body>

<p>
    <a href="//auth.localhost.test:8080/github.php?from=http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ?>">ログイン</a>
    |
    <a href="//auth.localhost.test:8080/logout.php?from=http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ?>">ログアウト</a>
    <?php if (isset($_SESSION['login'])): ?>| ログイン済み<?php endif; ?>
</p>

<ul>
    <li><a href="phpinfo.php">phpinfo</a></li>
    <li><a href="postgresql.php">postgresql</a></li>
</ul>


<pre><?php var_dump($_SESSION) ?></pre>
<pre><?php var_dump($_SERVER) ?></pre>

</body>

</html>
