<?php
//echo $_SERVER['REMOTE_ADDR'];
echo '<pre>';
echo json_encode($_GET, JSON_PRETTY_PRINT) . '
';
echo json_encode($_POST, JSON_PRETTY_PRINT) . '
';
echo json_encode($_SERVER, JSON_PRETTY_PRINT) . '
';
echo json_encode($_COOKIE, JSON_PRETTY_PRINT) . '
';
echo '</pre>';
?>
