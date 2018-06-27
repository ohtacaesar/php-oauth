<?php

session_start(['read_and_close' => 1]);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SESSION DUMP</title>
</head>

<body>
<pre><?php var_dump($_SESSION) ?></pre>
</body>

</html>
