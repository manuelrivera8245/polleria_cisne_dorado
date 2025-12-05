<section id="tab-dashboard" class="tab-content dashboard-compacto">
    
    <div class="dashboard-top">
        <div class="stat-card">
            <div class="stat-info">
                <h4>Ventas Hoy</h4>
                <h3 id="reporte-ventas">S/ 0.00</h3>
            </div>
            <div class="icon-box green"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h4>Pedidos Hoy</h4>
                <h3 id="reporte-cantidad">0</h3>
            </div>
            <div class="icon-box blue"><i class="fa-solid fa-receipt"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h4>Plato Estrella ★</h4>
                <h3 id="reporte-top" style="font-size: 1.1rem;">-</h3>
            </div>
            <div class="icon-box gold"><i class="fa-solid fa-crown"></i></div>
        </div>
    </div>

    <div class="dashboard-bottom">
        
        <div class="table-panel">
            <div class="table-header">
                <h3 style="margin:0; color:#fff; font-size:1rem;">
                    <i class="fa-solid fa-clock-rotate-left"></i> Ingresos Recientes
                </h3>
                <button onclick="cambiarPestana('pedidos')" style="background:none; border:none; color:#F3C400; cursor:pointer; font-size:0.8rem; font-weight:bold;">VER TODOS</button>
            </div>
            <div class="table-scroll">
                <table class="admin-table compact-table" style="width:100%">
                    <thead>
                        <tr style="color:#aaa">
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="dashboard-ultimos-pedidos">
                        <tr><td colspan="4" style="text-align:center; padding:20px; color:#555;">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="operational-panel">
            
            <div class="op-card" style="border-left: 4px solid #ff5252;">
                <div class="op-info">
                    <h4>PENDIENTES</h4>
                    <h3 id="reporte-pendientes" class="alert-text">0</h3>
                </div>
                <i class="fa-solid fa-bell fa-2x" style="color:#ff5252; opacity:0.5;"></i>
            </div>

            <div class="op-card" style="border-left: 4px solid #448aff;">
                <div class="op-info">
                    <h4>REPARTIDORES LIBRES</h4>
                    <h3 id="reporte-repartidores" class="info-text">0</h3>
                </div>
                <i class="fa-solid fa-motorcycle fa-2x" style="color:#448aff; opacity:0.5;"></i>
            </div>

            <div class="op-card" style="border-left: 4px solid #69f0ae;">
                <div class="op-info">
                    <h4>ESTADO TIENDA</h4>
                    <h3 id="dashboard-estado-tienda" class="success-text">CARGANDO...</h3>
                </div>
                <i class="fa-solid fa-store fa-2x" style="color:#69f0ae; opacity:0.5;"></i>
            </div>

            <button onclick="abrirModalProducto()" style="padding:15px; background:#252525; border:1px dashed #555; color:#aaa; border-radius:10px; cursor:pointer; font-weight:bold; margin-top:5px;">
                <i class="fa-solid fa-plus"></i> Nuevo Plato Rápido
            </button>

        </div>

    </div>
</section>

<script>
/**
 * Obtiene los datos del dashboard (contadores) y la lista de últimos pedidos.
 */
function actualizarDashboard() {
    fetch('api/reportes/resumen.php')
    .then(res => res.json())
    .then(data => {
        // Formato de moneda Perú
        const ventas = parseFloat(data.ventas_hoy || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 });
        
        document.getElementById('reporte-ventas').textContent = 'S/ ' + ventas;
        document.getElementById('reporte-cantidad').textContent = data.pedidos_hoy || 0;
        document.getElementById('reporte-top').textContent = data.top_producto || '-';
        document.getElementById('reporte-pendientes').textContent = data.pendientes || 0;
        document.getElementById('reporte-repartidores').textContent = data.repartidores || 0;
    })
    .catch(err => console.error("Error resumen:", err));

    // 2. Cargar Tabla de Ingresos Recientes
    fetch('api/pedidos/listar.php')
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('dashboard-ultimos-pedidos');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        // Si no hay datos
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px;">Sin movimientos hoy</td></tr>';
            return;
        }

        // Mostrar solo los 5 más recientes
        data.slice(0, 5).forEach(p => {
            const cliente = p.nombre_invitado ? p.nombre_invitado + ' (Inv)' : (p.nombre_registrado || 'Web');
            
            let color = '#ddd';
            if(p.estado === 'Pendiente') color = '#ff5252'; 
            if(p.estado === 'Entregado') color = '#69f0ae'; 
            if(p.estado === 'En Camino') color = '#448aff';

            // Convertir fecha a hora local
            const fechaObj = new Date(p.fecha_hora);
            const hora = fechaObj.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });

            tbody.innerHTML += `
                <tr>
                    <td style="color:#777">${hora}</td>
                    <td style="font-weight:bold; color:#fff; font-size:0.85rem;">${cliente}</td>
                    <td style="font-weight:bold; color:#F3C400">S/ ${p.total}</td>
                    <td><span style="color:${color}; font-size:0.8rem; font-weight:bold;">${p.estado}</span></td>
                </tr>`;
        });
    })
    .catch(err => {
        console.error("Error tabla dashboard:", err);
        const tbody = document.getElementById('dashboard-ultimos-pedidos');
        if(tbody) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red;">Error de conexión</td></tr>';
    });
    
    // 3. Ejecutar la función que busca el estado de la tienda
    actualizarDashboardStatus();
}

/**
 * Consulta el estado de la tienda (abierto/cerrado) y actualiza el indicador visual.
 */
async function actualizarDashboardStatus() {
    const estadoTxt = document.getElementById('dashboard-estado-tienda');
    if(!estadoTxt) return;

    try {
        const res = await fetch('api/ajustes/obtener.php');
        const data = await res.json();

        if (data.ok && data.config) {
            const abierto = data.config.tienda_abierta;
            if(abierto) {
                estadoTxt.textContent = "ABIERTO";
                estadoTxt.className = "success-text"; // Texto Verde (clase definida en CSS)
                estadoTxt.style.color = "#69f0ae"; 
            } else {
                estadoTxt.textContent = "CERRADO";
                estadoTxt.className = "alert-text"; // Texto Rojo
                estadoTxt.style.color = "#ff5252";
            }
        }
    } catch (e) {
        console.error("Error status:", e);
        estadoTxt.textContent = "ERROR";
        estadoTxt.style.color = "red";
    }
}

// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', actualizarDashboard);
</script>