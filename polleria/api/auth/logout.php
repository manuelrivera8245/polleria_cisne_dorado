<?php
// 1. Iniciar la sesión para poder acceder a ella
session_start();

// 2. Destruir todas las variables de sesión (nombre, rol, id, etc.)
session_unset();

// 3. Destruir la sesión completamente en el servidor
session_destroy();

// 4. Redirigir al usuario a la página de inicio (subimos dos niveles ../../)
header("Location: ../../index.php");
exit;
?>