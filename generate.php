<?php

namespace PhpDoxy;

ob_implicit_flush();
ob_end_clean();

require_once "vendor/autoload.php";
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>PhpDoxy - Documentation Generation</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
</head>
<body style="background-color:black; margin:0; padding:10px">
<pre style='color:white; margin:0; padding:0'><?php
    generate([]);
    ?>
</pre>
<script type="text/javascript">
    window.scrollTo(0, 10000000);
</script>
</body>

