<?php
// Iniciamos sesión si no está iniciada para poder usar $_SESSION
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cisne Dorado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="public/css/style.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<header class="nav-header">
    <div class="nav-left">
        <a href="index.php?page=home" class="logo-link">
            <img src="public/img/logo.jpg" alt="Logo Cisne Dorado" class="logo-img">
        </a>
    </div>

 <nav class="nav-menu">
    <a href="index.php?page=home">INICIO</a>
    <a href="index.php?page=menu">CARTA</a>
    <a href="index.php?page=local">LOCAL</a>

    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador'): ?>
        <a href="index.php?page=admin" class="nav-link-highlight">ADMIN</a>
    <?php endif; ?>

    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Cliente'): ?>
        <a href="index.php?page=mis_pedidos" class="nav-link-highlight">PEDIDOS</a>
    <?php endif; ?>

    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Repartidor'): ?>
        <a href="index.php?page=repartidor" class="nav-link-highlight" style="color: #F3C400;">ENTREGAS</a>
    <?php endif; ?>
</nav>
    <div class="search-container">
        <form action="index.php" method="GET" class="search-form">
            <input type="hidden" name="page" value="menu"> <input type="text" name="q" class="search-input" placeholder="Buscar antojo..." required>
            <button type="submit" class="search-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    <div class="nav-right">
        <?php if(isset($_SESSION['nombre'])): ?>
            <span class="user-welcome">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
            <a href="api/auth/logout.php" title="Cerrar Sesión" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <a href="index.php?page=login" title="Iniciar Sesión">
                <i class="fa-solid fa-user"></i>
            </a>
        <?php endif; ?>
        
        <a href="index.php?page=carrito" title="Ver Carrito">
            <i class="fa-solid fa-cart-shopping"></i>
        </a>
    </div>
</header>