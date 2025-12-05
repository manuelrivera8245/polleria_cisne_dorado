<?php
// pages/home.php
require_once 'config/db.php';

$destacados = [];

try {
    $db = (new Database())->connect();
    
    // Consulta para obtener los 3 productos más vendidos
    $sql = "SELECT 
                p.id_producto, 
                p.nombre, 
                p.precio, 
                SUM(dp.cantidad) as total_vendido
            FROM detalle_pedidos dp
            INNER JOIN productos p ON dp.id_producto = p.id_producto
            GROUP BY dp.id_producto
            ORDER BY total_vendido DESC
            LIMIT 3";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay ventas, mostramos 3 productos cualquiera para que no quede vacío
    if (count($destacados) == 0) {
        $stmtFallback = $db->query("SELECT * FROM productos WHERE estado = 'Disponible' LIMIT 3");
        $destacados = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $destacados = [];
}
?>

<section class="hero-banner-wrapper">
    <img src="public/img/banner_home.jpg" alt="Banner Principal" class="hero-img-full">
</section>

<section class="menu-section">
    <h2>NUESTRO MENÚ</h2>
    <p class="home-subtitle">Selecciona una categoría</p>

    <div class="categorias-grid">
        
        <a href="index.php?page=menu&q=Pollo" class="cat-card" style="background-image: url('public/img/categoria/Pollos_a_la_brasa.png');">
            <div class="overlay"><i class="fa-solid fa-drumstick-bite"></i><h3>Pollos a la Brasa</h3></div>
        </a>

        <a href="index.php?page=menu&q=Parrilla" class="cat-card" style="background-image: url('public/img/categoria/Parrillas.png');">
            <div class="overlay"><i class="fa-solid fa-fire"></i><h3>Parrillas</h3></div>
        </a>

        <a href="index.php?page=menu&q=Caldo" class="cat-card" style="background-image: url('public/img/categoria/Caldos.png');">
            <div class="overlay"><i class="fa-solid fa-mug-hot"></i><h3>Caldos</h3></div>
        </a>

        <a href="index.php?page=menu&q=Especial" class="cat-card" style="background-image: url('public/img/categoria/Especiales.png');">
            <div class="overlay"><i class="fa-solid fa-star"></i><h3>Especiales</h3></div>
        </a>

        <a href="index.php?page=menu&q=Chaufa" class="cat-card" style="background-image: url('public/img/categoria/Innovacion.png');">
            <div class="overlay"><i class="fa-solid fa-bolt"></i><h3>Innovación</h3></div>
        </a>

    </div>
</section>

<section class="vendido-section">
    <h2>LO MÁS VENDIDO</h2>
    <div class="vendidos-grid">
        
        <?php if (count($destacados) > 0): ?>
            
            <?php foreach ($destacados as $index => $prod): ?>
                <div class="card-vendido">
                    <div class="icon-top" style="color: <?php echo ($index === 0) ? '#FFD700' : '#C0C0C0'; ?>; font-size:1.5rem; margin-bottom:10px;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    
                    <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                    
                    <p class="precio" style="color:#F3C400; font-weight:bold; font-size:1.2rem;">
                        S/ <?php echo number_format($prod['precio'], 2); ?>
                    </p>
                    
                    <?php if(isset($prod['total_vendido'])): ?>
                        <small style="color:#aaa; display:block; margin-bottom:10px;">
                            ¡<?php echo $prod['total_vendido']; ?> vendidos!
                        </small>
                    <?php endif; ?>

                    <a href="index.php?page=menu" class="btn-ver">Ver en Carta</a>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <p class="loading-msg">Aun no hay datos de ventas.</p>
        <?php endif; ?>

    </div>
</section>