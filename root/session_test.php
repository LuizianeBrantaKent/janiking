<?php
session_start();
$_SESSION['ping'] = ($_SESSION['ping'] ?? 0) + 1;
echo "Session counter: " . $_SESSION['ping'];
