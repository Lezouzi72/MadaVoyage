<?php
session_start();
session_destroy();
header("Location: log.php");
exit();
?>
