<!DOCTYPE html>
<html>
<head>
<style type="text/css">
body {
  font-size: larger;
}
</style>
</head>
<body>
<?php
foreach($_SERVER as $key => $value) {
    if (substr($key, 0, 5) == 'HTTP_') {
      $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
      echo htmlspecialchars($header) . ': ' . htmlspecialchars($value) . "<br>\n";
    }
}
?>
</body>
</html>