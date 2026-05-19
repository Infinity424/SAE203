<?php
session_start();
session_unset();
session_destroy();
header("Location: ./accueil.php");  //renvoyer a la page d'accueil
exit();
?>