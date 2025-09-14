<?php
session_start();
session_destroy();
header("Location: welcome.php?message=Vous avez été déconnecté avec succès.");
exit;