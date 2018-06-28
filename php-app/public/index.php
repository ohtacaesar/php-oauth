<?php

session_start(['read_and_close' => 1]);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SESSION DUMP</title>
</head>

<body>
<a href="//auth.localhost.test:8080/github.php?from=http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ?>">ログイン</a> |
<a href="//auth.localhost.test:8080/logout.php?from=http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] ?>">ログアウト</a>
<?php if(isset($_SESSION['login'])): ?>| ログイン済み<?php endif; ?>

<pre><?php var_dump($_SESSION) ?></pre>
<pre><?php var_dump($_SERVER) ?></pre>

</body>

</html>
