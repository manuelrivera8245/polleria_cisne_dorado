<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Repartidor') {
    header("Location: index.php?page=login");
    exit;
}
?>

<div class="repartidor-container">
    <h2 class="section-title"> Mis Entregas Pendientes</h2>
    
    <div id="lista-entregas" class="entregas-grid">
        <p style="color:#aaa;">Cargando...</p>
    </div>
</div>

<style>
.repartidor-container { max-width: 800px; margin: 0 auto; padding: 20px; color: #fff; }
.entregas-grid { display: grid; gap: 20px; }
.entrega-card { 
    background: #222; border: 1px solid #444; border-radius: 10px; padding: 20px; 
    border-left: 5px solid #F3C400; 
}
.entrega-header { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem; }
.entrega-info p { margin: 5px 0; color: #ccc; }
.entrega-info strong { color: #fff; }
.entrega-actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }

.btn-estado { 
    flex: 1; padding: 12px; border: none; border-radius: 5px; 
    font-weight: bold; cursor: pointer; color: #fff; 
    transition: 0.3s;
}
.btn-camino { background: #2196F3; }
.btn-puerta { background: #E91E63; }
.btn-entregado { background: #4CAF50; }
.btn-mapa { background: #333; border: 1px solid #777; color: #F3C400; }

.btn-estado:hover { opacity: 0.9; transform: translateY(-2px); }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    cargarEntregas();
    setInterval(cargarEntregas, 10000);
});

async function cargarEntregas() {
    const contenedor = document.getElementById('lista-entregas');
    try {
        const res = await fetch('api/pedidos/mis_entregas.php');
        const entregas = await res.json();
        
        contenedor.innerHTML = '';
        if (entregas.length === 0) {
            contenedor.innerHTML = '<div style="text-align:center; padding:40px; color:#666;"><h3>No tienes entregas pendientes 🎉</h3></div>';
            return;
        }

        entregas.forEach(p => {
            // Nombre del cliente
            const cliente = p.nombre_invitado || p.nombre_cliente || 'Cliente Web';
            
            // Botones dinámicos según el estado
            let botones = '';
            
            // Botón Mapa (Waze/Google Maps)
            const linkMapa = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(p.direccion_entrega)}`;
            
            if (p.estado === 'En Camino' || p.estado === 'Listo') {
                botones += `<button onclick="cambiarEstado(${p.id_pedido}, 'En Puerta')" class="btn-estado btn-puerta"><i class="fa-solid fa-house-user"></i> Estoy en Puerta</button>`;
            }
            if (p.estado === 'En Puerta') {
                botones += `<button onclick="cambiarEstado(${p.id_pedido}, 'Entregado')" class="btn-estado btn-entregado"><i class="fa-solid fa-check"></i> Entregado</button>`;
            }

            const card = document.createElement('div');
            card.className = 'entrega-card';
            card.innerHTML = `
                <div class="entrega-header">
                    <span>Pedido #${p.id_pedido}</span>
                    <span style="color:#F3C400">S/ ${p.total}</span>
                </div>
                <div class="entrega-info">
                    <p><i class="fa-solid fa-user"></i> <strong>${cliente}</strong></p>
                    <p><i class="fa-solid fa-phone"></i> <a href="tel:${p.telefono_contacto}" style="color:#F3C400">${p.telefono_contacto}</a></p>
                    <p><i class="fa-solid fa-location-dot"></i> ${p.direccion_entrega}</p>
                    <p><i class="fa-solid fa-money-bill"></i> Pago: ${p.metodo_pago}</p>
                </div>
                <div class="entrega-actions">
                    <a href="${linkMapa}" target="_blank" class="btn-estado btn-mapa" style="text-align:center; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-map-location-dot"></i> Ver Mapa
                    </a>
                    ${botones}
                </div>
            `;
            contenedor.appendChild(card);
        });

    } catch (error) {
        console.error(error);
    }
    if (p.estado === 'Listo') {
    // Si está listo, el repartidor avisa que sale
    botones += `<button onclick="cambiarEstado(${p.id_pedido}, 'En Camino')" class="btn-estado btn-camino"><i class="fa-solid fa-motorcycle"></i> Iniciar Ruta</button>`;
    }
    else if (p.estado === 'En Camino') {
        botones += `<button onclick="cambiarEstado(${p.id_pedido}, 'En Puerta')" class="btn-estado btn-puerta"><i class="fa-solid fa-house-user"></i> Estoy en Puerta</button>`;
    }
    else if (p.estado === 'En Puerta') {
        botones += `<button onclick="cambiarEstado(${p.id_pedido}, 'Entregado')" class="btn-estado btn-entregado"><i class="fa-solid fa-check"></i> Entregado</button>`;
    }
}

async function cambiarEstado(id, nuevoEstado) {
    if(!confirm(`¿Marcar pedido como ${nuevoEstado}?`)) return;
    
    try {
        const res = await fetch('api/pedidos/actualizar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, estado: nuevoEstado })
        });
        const data = await res.json();
        if(data.ok) cargarEntregas();
        else alert('Error al actualizar');
    } catch (e) { alert('Error de conexión'); }
}
</script>