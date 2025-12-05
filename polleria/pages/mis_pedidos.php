<div class="mis-pedidos-container">
    <h2 class="section-title">Mis Pedidos</h2>
    
    <div id="pedidos-list" class="pedidos-grid">
        <!-- Los pedidos se cargarán aquí dinámicamente -->
        <div class="loading-spinner">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Cargando pedidos...
        </div>
    </div>
</div>

<style>
    .mis-pedidos-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
        min-height: 60vh;
    }

    .section-title {
        color: #F3C400;
        font-size: 2rem;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-bottom: 2px solid #7a0000;
        display: inline-block;
        padding-bottom: 10px;
    }

    .pedidos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    .pedido-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .pedido-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        border-color: #F3C400;
    }

    .pedido-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 10px;
    }

    .pedido-id {
        font-weight: bold;
        color: #fff;
        font-size: 1.1rem;
    }

    .pedido-date {
        color: #aaa;
        font-size: 0.9rem;
    }

    .pedido-body {
        margin-bottom: 15px;
    }

    .pedido-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .pedido-label {
        color: #ccc;
    }

    .pedido-value {
        color: #fff;
        font-weight: 500;
    }

    .pedido-total {
        font-size: 1.2rem;
        color: #F3C400;
        font-weight: bold;
        text-align: right;
        margin-top: 10px;
    }

    .pedido-status {
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
        margin-top: 15px;
        display: block;
    }

    /* Estados */
    .status-pendiente { background: #FF9800; color: #000; }
    .status-preparacion { background: #2196F3; color: #fff; }
    .status-listo { background: #00BCD4; color: #fff; }
    .status-camino { background: #9C27B0; color: #fff; }
    .status-puerta { background: #E91E63; color: #fff; }
    .status-entregado { background: #4CAF50; color: #fff; }
    .status-rechazado { background: #F44336; color: #fff; }

    .loading-spinner {
        text-align: center;
        color: #F3C400;
        font-size: 1.2rem;
        grid-column: 1 / -1;
        padding: 40px;
    }

    .empty-state {
        text-align: center;
        grid-column: 1 / -1;
        padding: 40px;
        color: #aaa;
    }
    
    .tracking-code {
        font-family: monospace;
        background: rgba(0,0,0,0.3);
        padding: 2px 6px;
        border-radius: 4px;
        color: #F3C400;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pedidosList = document.getElementById('pedidos-list');
    let isFirstLoad = true;

    const statusClasses = {
        'Pendiente': 'status-pendiente',
        'En Preparacion': 'status-preparacion',
        'Listo': 'status-listo',
        'En Camino': 'status-camino',
        'En Puerta': 'status-puerta',
        'Entregado': 'status-entregado',
        'Rechazado': 'status-rechazado'
    };

    const statusLabels = {
        'Pendiente': 'Pendiente',
        'En Preparacion': 'En Preparación',
        'Listo': 'Listo para enviar',
        'En Camino': 'En Camino',
        'En Puerta': 'En Puerta',
        'Entregado': 'Entregado',
        'Rechazado': 'Rechazado'
    };

    function fetchPedidos() {
        fetch('api/pedidos/mis_pedidos.php')
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    renderPedidos(data.pedidos);
                } else {
                    if (data.message === 'No autorizado') {
                        window.location.href = 'index.php?page=login';
                    } else {
                        pedidosList.innerHTML = '<div class="empty-state">Error al cargar pedidos.</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                pedidosList.innerHTML = '<div class="empty-state">Error de conexión.</div>';
            });
    }

    function renderPedidos(pedidos) {
        if (pedidos.length === 0) {
            pedidosList.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-receipt" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <p>Aún no has realizado ningún pedido.</p>
                    <a href="index.php?page=menu" class="menu-btn" style="margin-top: 20px; display: inline-block;">Ir al Menú</a>
                </div>
            `;
            return;
        }

        // Si no es la primera carga, solo actualizamos los estados para evitar parpadeos molestos
        // Pero para simplificar y asegurar que se vean nuevos pedidos, regeneramos todo por ahora.
        // Una mejora sería comparar y solo actualizar DOM si hay cambios.
        
        let html = '';
        pedidos.forEach(pedido => {
            const statusClass = statusClasses[pedido.estado] || 'status-pendiente';
            const statusLabel = statusLabels[pedido.estado] || pedido.estado;
            const fecha = new Date(pedido.fecha_hora).toLocaleDateString('es-PE', {
                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            html += `
                <div class="pedido-card">
                    <div class="pedido-header">
                        <span class="pedido-id">#${pedido.id_pedido}</span>
                        <span class="pedido-date">${fecha}</span>
                    </div>
                    <div class="pedido-body">
                        <div class="pedido-info-row">
                            <span class="pedido-label">Tracking:</span>
                            <span class="pedido-value tracking-code">${pedido.codigo_tracking || '-'}</span>
                        </div>
                        <div class="pedido-info-row">
                            <span class="pedido-label">Pago:</span>
                            <span class="pedido-value">${pedido.metodo_pago}</span>
                        </div>
                        <div class="pedido-total">
                            S/ ${parseFloat(pedido.total).toFixed(2)}
                        </div>
                    </div>
                    <div class="pedido-status ${statusClass}">
                        ${statusLabel}
                    </div>
                </div>
            `;
        });

        pedidosList.innerHTML = html;
    }

    // Cargar inicialmente
    fetchPedidos();

    // Actualizar cada 10 segundos (Polling)
    setInterval(fetchPedidos, 10000);
});
</script>
