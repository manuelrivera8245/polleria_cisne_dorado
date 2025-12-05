<section id="tab-pedidos" class="tab-content" style="display: none;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="margin:0; color:#aaa; font-weight:normal;">Control de Despachos</h3>
        <button class="btn-refresh" onclick="cargarPedidosAPI()">
            <i class="fa-solid fa-rotate-right"></i> Refrescar Datos
        </button>
    </div>

    <div class="pedidos-tabs" style="display:flex; gap:15px; border-bottom:1px solid #333; margin-bottom:20px;">
        <button class="tab-filter active" onclick="cambiarFiltro('activos')" style="background:none; border:none; color:#F3C400; border-bottom:2px solid #F3C400; padding:10px; cursor:pointer; font-weight:bold; transition:0.3s;">
            <i class="fa-solid fa-fire"></i> En Curso <span id="count-activos" class="badge-counter">0</span>
        </button>
        <button class="tab-filter" onclick="cambiarFiltro('entregados')" style="background:none; border:none; color:#888; padding:10px; cursor:pointer; transition:0.3s;">
            <i class="fa-solid fa-check-double"></i> Entregados <span id="count-entregados" class="badge-counter">0</span>
        </button>
        <button class="tab-filter" onclick="cambiarFiltro('rechazados')" style="background:none; border:none; color:#888; padding:10px; cursor:pointer; transition:0.3s;">
            <i class="fa-solid fa-ban"></i> Rechazados <span id="count-rechazados" class="badge-counter">0</span>
        </button>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora</th>
                    <th>Cliente</th>
                    <th>Dirección</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Repartidor</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="listaPedidosBody">
                <tr><td colspan="8" style="text-align:center; padding:30px; color:#666;">Cargando pedidos...</td></tr>
            </tbody>
        </table>
    </div>

    <div id="paginacion-box" style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; padding-top:10px; border-top:1px solid #333; color:#888; font-size:0.9rem;">
        <span id="info-paginacion">Cargando...</span>
        <div id="botones-paginacion" style="display:flex; gap:5px;"></div>
    </div>

</section>

<style>
    /* Pequeño estilo local para los contadores */
    .badge-counter { background: #333; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-left: 5px; color: #fff; }
</style>

<script>
// --- VARIABLES GLOBALES DEL MÓDULO ---
var todosLosPedidos = [];
var listaRepartidoresPedidos = []; 
var filtroActual = 'activos'; 
var paginaActual = 1;
var filasPorPagina = 10;

/**
 * Carga la lista de pedidos y repartidores desde la API.
 */
async function cargarPedidosAPI() {
    const tbody = document.getElementById('listaPedidosBody');
    
    // Mostrar cargando solo si está vacío
    if(tbody.innerHTML.trim() === '' || todosLosPedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:30px; color:#aaa;">Cargando datos...</td></tr>';
    }

    try {
        // A. PRIMERO cargamos los repartidores (AWAIT para esperar a que termine)
        const resRep = await fetch('api/usuarios/repartidores.php');
        listaRepartidoresPedidos = await resRep.json();

        // B. LUEGO cargamos los pedidos
        const resPed = await fetch('api/pedidos/listar.php');
        todosLosPedidos = await resPed.json();

        // C. FINALMENTE dibujamos
        actualizarContadores();
        renderizarTabla();
        
    } catch (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#ff5252;">Error de conexión</td></tr>';
    }
}

/**
 * Cambia el filtro visual de pedidos (pendientes, entregados, rechazados).
 * @param {string} filtro Tipo de filtro ('activos', 'entregados', 'rechazados').
 */
function cambiarFiltro(filtro) {
    filtroActual = filtro;
    paginaActual = 1; // Al cambiar de pestaña, volvemos a la página 1

    // Gestión visual de los botones (Estilos)
    const botones = document.querySelectorAll('.tab-filter');
    botones.forEach(b => {
        b.style.color = '#888';
        b.style.borderBottom = 'none';
        b.classList.remove('active');
    });
    
    // Activar el botón clickeado
    event.currentTarget.style.color = '#F3C400';
    event.currentTarget.style.borderBottom = '2px solid #F3C400';
    event.currentTarget.classList.add('active');

    renderizarTabla();
}

/**
 * Dibuja la tabla de pedidos aplicando filtros y paginación.
 */
function renderizarTabla() {
    const tbody = document.getElementById('listaPedidosBody');
    tbody.innerHTML = '';

    // A. FILTRADO
    const filtrados = todosLosPedidos.filter(p => {
        if (filtroActual === 'activos') {
            // Muestra todo lo que NO está finalizado (ni entregado ni rechazado)
            return ['Pendiente', 'En Preparacion', 'Listo', 'En Camino', 'En Puerta'].includes(p.estado);
        } else if (filtroActual === 'entregados') {
            return p.estado === 'Entregado';
        } else if (filtroActual === 'rechazados') {
            return p.estado === 'Rechazado';
        }
    });

    // B. PAGINACIÓN
    const totalItems = filtrados.length;
    const totalPaginas = Math.ceil(totalItems / filasPorPagina);
    const inicio = (paginaActual - 1) * filasPorPagina;
    const itemsPagina = filtrados.slice(inicio, inicio + filasPorPagina);

    // C. SI NO HAY DATOS
    if (itemsPagina.length === 0) {
        let mensaje = 'No hay pedidos pendientes.';
        if(filtroActual === 'entregados') mensaje = 'No hay historial de entregas.';
        if(filtroActual === 'rechazados') mensaje = 'No hay pedidos rechazados.';
        
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:40px; color:#666;">${mensaje}</td></tr>`;
        document.getElementById('paginacion-box').style.display = 'none';
        return;
    }
    document.getElementById('paginacion-box').style.display = 'flex';

    // D. DIBUJAR FILAS
    const estadosSelect = ['Pendiente', 'En Preparacion', 'Listo', 'En Camino', 'En Puerta', 'Entregado', 'Rechazado'];

    itemsPagina.forEach(p => {
        // Fecha formateada
        const fecha = new Date(p.fecha_hora).toLocaleString('es-PE', {
            day: '2-digit', month: '2-digit', year: 'numeric', 
            hour: '2-digit', minute: '2-digit', hour12: true
        });
        
        // Nombre Cliente
        const cliente = p.nombre_invitado ? p.nombre_invitado + ' (Inv)' : (p.nombre_registrado || 'Web');

        // ESTADO: Si está activo es un Select, si es historial es Texto fijo
        let htmlEstado = '';
        if (filtroActual === 'activos') {
            htmlEstado = `<select onchange="cambiarEstadoP(${p.id_pedido},this.value)" class="select-estado" style="${getColorP(p.estado)}">`;
            estadosSelect.forEach(e => htmlEstado += `<option value="${e}" ${p.estado==e?'selected':''}>${e}</option>`);
            htmlEstado += '</select>';
        } else {
            let color = p.estado === 'Entregado' ? '#4CAF50' : '#ff5252';
            htmlEstado = `<span style="color:${color}; font-weight:bold; border:1px solid ${color}; padding:4px 8px; border-radius:4px;">${p.estado}</span>`;
        }

        // REPARTIDOR: Select o Texto
        let htmlRep = '';
        if (filtroActual === 'activos') {
            htmlRep = `<select onchange="asignarRepP(${p.id_pedido},this.value)" class="select-estado">`;
            htmlRep += '<option value="">-- Asignar --</option>';
            listaRepartidoresPedidos.forEach(r => {
                const sel = p.id_repartidor == r.id_usuario ? 'selected' : '';
                htmlRep += `<option value="${r.id_usuario}" ${sel}>${r.nombre}</option>`;
            });
            htmlRep += '</select>';
        } else {
            const rName = listaRepartidoresPedidos.find(r => r.id_usuario == p.id_repartidor)?.nombre || '-';
            htmlRep = `<span style="color:#aaa; font-size:0.9rem;">${rName}</span>`;
        }

        tbody.innerHTML += `
            <tr>
                <td><span style="color:#F3C400; font-weight:bold">#${p.id_pedido}</span></td>
                <td style="color:#ddd; font-size:0.9rem;">${fecha}</td>
                <td>
                    <div style="font-weight:bold; color:#fff;">${cliente}</div>
                    <div style="font-size:0.8rem; color:#777;">${p.telefono_contacto}</div>
                </td>
                <td style="font-size:0.9rem;">${p.direccion_entrega}</td>
                <td style="font-weight:bold; color:#F3C400;">S/ ${p.total}</td>
                <td>${htmlEstado}</td>
                <td>${htmlRep}</td>
                <td>
                    <button class="btn-action" onclick="verDetalle(${p.id_pedido})" title="Ver Productos">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    actualizarPaginacion(totalItems, totalPaginas, inicio);
}

/**
 * Actualiza los controles de paginación de la tabla.
 * @param {number} total Total de items.
 * @param {number} paginas Número total de páginas.
 * @param {number} inicio Índice de inicio actual.
 */
function actualizarPaginacion(total, paginas, inicio) {
    document.getElementById('info-paginacion').innerText = `Viendo ${inicio+1} - ${Math.min(inicio+filasPorPagina, total)} de ${total}`;
    const div = document.getElementById('botones-paginacion');
    
    div.innerHTML = `
        <button onclick="cambiarPagina(${paginaActual-1})" ${paginaActual==1?'disabled':''} style="background:#333; border:1px solid #444; color:#fff; padding:5px 10px; cursor:pointer; border-radius:4px; margin-right:5px;">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <span style="padding:5px 10px; color:#fff;">Página ${paginaActual} / ${paginas}</span>
        <button onclick="cambiarPagina(${paginaActual+1})" ${paginaActual==paginas?'disabled':''} style="background:#333; border:1px solid #444; color:#fff; padding:5px 10px; cursor:pointer; border-radius:4px; margin-left:5px;">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    `;
}

/**
 * Navega a una página específica de la tabla.
 * @param {number} p Número de página.
 */
function cambiarPagina(p) {
    if(p > 0) { paginaActual = p; renderizarTabla(); }
}

/**
 * Actualiza los contadores de las pestañas de filtro.
 */
function actualizarContadores() {
    const activos = todosLosPedidos.filter(p => ['Pendiente','En Preparacion','Listo','En Camino','En Puerta'].includes(p.estado)).length;
    const entregados = todosLosPedidos.filter(p => p.estado === 'Entregado').length;
    const rechazados = todosLosPedidos.filter(p => p.estado === 'Rechazado').length;
    
    document.getElementById('count-activos').innerText = activos;
    document.getElementById('count-entregados').innerText = entregados;
    document.getElementById('count-rechazados').innerText = rechazados;
}

/**
 * Obtiene el estilo CSS para el estado del pedido.
 * @param {string} e Estado del pedido.
 * @return {string} Estilo CSS inline.
 */
function getColorP(e) {
    if(e=='En Puerta') return 'border:1px solid #E91E63; color:#E91E63';
    if(e=='Pendiente') return 'border:1px solid #F3C400; color:#F3C400';
    return '';
}

/**
 * Actualiza el estado de un pedido en el backend.
 * @param {number} id ID del pedido.
 * @param {string} est Nuevo estado.
 */
async function cambiarEstadoP(id, est) {
    try {
        const res = await fetch('api/pedidos/actualizar.php', {
            method:'POST', 
            headers:{'Content-Type':'application/json'}, 
            body:JSON.stringify({id:id, estado:est})
        });
        const data = await res.json();
        
        if(!data.ok) {
            alert('Error: ' + (data.error || 'No se pudo cambiar estado'));
        }
        cargarPedidosAPI();
    } catch(e) {
        alert("Error de conexión");
    }
}

/**
 * Asigna un repartidor a un pedido.
 * @param {number} id ID del pedido.
 * @param {number} rep ID del repartidor.
 */
async function asignarRepP(id, rep) {
    if(!rep) return; // Si selecciona "-- Asignar --", no hace nada

    if(confirm('¿Asignar este pedido al repartidor seleccionado?')) {
        try {
            const res = await fetch('api/pedidos/actualizar.php', {
                method:'POST', 
                headers:{'Content-Type':'application/json'}, 
                body:JSON.stringify({id:id, id_repartidor:rep})
            });
            const data = await res.json();
            
            if(data.ok) {
                alert("¡Asignado correctamente!");
                cargarPedidosAPI(); // Recargar la tabla para ver el estado "En Camino"
            } else {
                alert("Error: " + (data.error || data.message || "Error desconocido"));
                cargarPedidosAPI(); // Resetear el select
            }
        } catch(e) { 
            console.error(e);
            alert("Error de red"); 
        }
    } else {
        cargarPedidosAPI(); // Si cancela, volvemos a cargar para regresar el select a su sitio
    }
}

// INICIALIZACIÓN AUTOMÁTICA AL CARGAR EL MÓDULO
document.addEventListener('DOMContentLoaded', cargarPedidosAPI);
</script>