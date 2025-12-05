<section class="menu-section" style="padding: 20px; max-width: 1200px; margin: 0 auto;">
    <h2 style="color: #F3C400; text-align: center; font-size: 2.5rem; margin-bottom: 30px; text-transform: uppercase;">
        <i class="fa-solid fa-utensils"></i> Nuestra Carta
    </h2>

    <?php
    require_once 'config/db.php';
    $productos = [];
    try {
        $db = (new Database())->connect();
        $stmt = $db->query("SELECT * FROM productos WHERE estado = 'Disponible'");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $productos = [];
    }
    ?>

    <div class="productos-grid">
        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $prod): ?>
                <div class="producto-card">
                    
                    <div class="img-container">
                        <?php if (!empty($prod['imagen']) && file_exists($prod['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fa-solid fa-bowl-food" style="font-size: 60px; color: #444;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="info">
                        <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                        <p class="precio">S/ <?php echo number_format($prod['precio'], 2); ?></p>
                        
                        <button class="btn-agregar" 
                                data-id="<?php echo $prod['id_producto']; ?>" 
                                data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>" 
                                data-precio="<?php echo $prod['precio']; ?>">
                            Agregar <i class="fa-solid fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1/-1; text-align: center; color: #ccc;">
                <p>No hay productos cargados en la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        padding-bottom: 50px;
    }
    .producto-card {
        background: #1a1a1a;
        border: 1px solid #333;
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .producto-card:hover {
        transform: translateY(-5px);
        border-color: #F3C400;
        box-shadow: 0 5px 15px rgba(243, 196, 0, 0.1);
    }
    .img-container {
        height: 200px; /* Aumentamos un poco la altura para que se vea mejor la foto */
        background: #222;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid #333;
        overflow: hidden;
    }
    .info {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .info h3 {
        margin: 0 0 10px 0;
        color: #fff;
        font-size: 1.1rem;
    }
    .precio {
        color: #F3C400;
        font-size: 1.4rem;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .btn-agregar {
        background: #b71c1c;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.2s;
        width: 100%;
    }
    .btn-agregar:hover {
        background: #d32f2f;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // LÓGICA 1: AGREGAR AL CARRITO
    const botones = document.querySelectorAll('.btn-agregar');
    botones.forEach(btn => {
        btn.addEventListener('click', () => {
            const producto = {
                id: btn.dataset.id,
                nombre: btn.dataset.nombre,
                precio: parseFloat(btn.dataset.precio),
                cantidad: 1
            };
            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
            const existe = carrito.find(item => item.id === producto.id);
            if (existe) { existe.cantidad++; } 
            else { carrito.push(producto); }
            localStorage.setItem('carrito', JSON.stringify(carrito));
            alert(`¡${producto.nombre} agregado al carrito!`);
        });
    });

    // LÓGICA 2: BUSCADOR
    const params = new URLSearchParams(window.location.search);
    const busqueda = params.get('q'); 
    if (busqueda) {
        const limpiarTexto = (texto) => texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        const termino = limpiarTexto(busqueda);
        const tarjetas = document.querySelectorAll('.producto-card');
        let encontrados = 0;
        tarjetas.forEach(card => {
            const titulo = card.querySelector('h3');
            if (titulo) {
                if (limpiarTexto(titulo.textContent).includes(termino)) {
                    card.style.display = 'flex';
                    encontrados++;
                } else { card.style.display = 'none'; }
            }
        });
        if (encontrados === 0) {
            const msg = document.createElement('div');
            msg.innerHTML = `<p style="color:#F3C400;text-align:center;margin-top:20px;">No hay resultados para: "${busqueda}"</p>
                             <button onclick="window.location.href='index.php?page=menu'" style="display:block;margin:10px auto;background:#b71c1c;color:white;padding:10px;">Ver Carta Completa</button>`;
            document.querySelector('.productos-grid').before(msg);
        }
    }
});
</script>