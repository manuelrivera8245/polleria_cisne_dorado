<?php
// pages/admin.php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    header("Location: index.php?page=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Cisne Dorado</title>
    
    <link rel="stylesheet" href="public/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Centrar contenido de las tablas */
        .admin-table th, .admin-table td {
            text-align: center;
            vertical-align: middle;
        }
        .admin-table td:nth-child(2), /* Nombre a la izquierda */
        .admin-table td:nth-child(3) { /* Email a la izquierda */
            text-align: left;
        }

        /* Badges de Estado */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: bold; border: 1px solid transparent; }
        .badge-success { background-color: rgba(76, 175, 80, 0.2); color: #4CAF50; border-color: #4CAF50; } /* Disponible */
        .badge-warning { background-color: rgba(255, 193, 7, 0.2); color: #FFC107; border-color: #FFC107; } /* Ocupado */
        .badge-danger  { background-color: rgba(244, 67, 54, 0.2); color: #F44336; border-color: #F44336; } /* Inactivo */
    </style>
</head>
<body>

<div class="admin-layout">
    
    <aside class="sidebar">
        <div class="brand">
            <h3>CISNE DORADO</h3>
        </div>
        
        <nav class="sidebar-menu">
            <p class="menu-label">PRINCIPAL</p>
            <button onclick="cambiarPestana('dashboard')" class="menu-item active" id="nav-dashboard">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </button>
            <button onclick="cambiarPestana('pedidos')" class="menu-item" id="nav-pedidos">
                <i class="fa-solid fa-motorcycle"></i> Pedidos <span id="badge-pedidos" class="badge-pending"></span>
            </button>
            
            <p class="menu-label">GESTIÓN</p>
            <button onclick="cambiarPestana('productos')" class="menu-item" id="nav-productos">
                <i class="fa-solid fa-utensils"></i> Productos
            </button>
            <p class="menu-label">ADMINISTRACIÓN</p>
            <button onclick="cambiarPestana('reportes')" class="menu-item" id="nav-reportes">
                <i class="fa-solid fa-file-invoice-dollar"></i> Reportes
            </button>

            <button onclick="cambiarPestana('usuarios')" class="menu-item" id="nav-usuarios">
                <i class="fa-solid fa-users-gear"></i> Personal
            </button>
            <button onclick="cambiarPestana('ajustes')" class="menu-item" id="nav-ajustes">
                <i class="fa-solid fa-sliders"></i> Ajustes
            </button>
        </nav>

        <div style="margin-top: auto;">
            <a href="api/auth/logout.php" class="menu-item logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <header class="top-bar">
            <h2 class="page-title" id="page-title">Dashboard</h2>
            <div class="user-badge">
                <i class="fa-solid fa-user-circle"></i> Admin: <strong><?php echo $_SESSION['nombre']; ?></strong>
            </div>
        </header>

        <?php include 'admin/dashboard.php'; ?>
        <?php include 'admin/pedidos.php'; ?>
        <?php include 'admin/productos.php'; ?>
        
        <?php include 'admin/reportes.php'; ?>
        
        <div id="tab-usuarios" class="tab-content" style="display: none;">
            <div class="header-action">
                <button class="btn-add" onclick="abrirModalUsuario('crear')">
                    <i class="fa-solid fa-plus"></i> Nuevo Personal
                </button>
            </div>

            <div class="card">
                <h3>Lista de Personal (Repartidores)</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lista-usuarios">
                        <tr><td colspan="6">Cargando personal...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include 'admin/ajustes.php'; ?>

    </main>
</div>

<div id="modalDetalle" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalle del Pedido #<span id="modal-id-pedido"></span></h3>
            <button onclick="cerrarModal('modalDetalle')" class="close-btn">×</button>
        </div>
        <div class="modal-body">
            <table class="admin-table" style="box-shadow:none; border:none;">
                <thead><tr><th>Producto</th><th style="text-align:center">Cant.</th><th>Subtotal</th></tr></thead>
                <tbody id="modal-lista-productos"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalProducto" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-titulo-prod">Gestión de Plato</h3>
            <button onclick="cerrarModal('modalProducto')" class="close-btn">×</button>
        </div>
        <div class="modal-body">
            <form id="formProducto" enctype="multipart/form-data">
                <input type="hidden" id="prodId">
                <label style="color:#aaa; display:block; margin-bottom:5px;">Nombre</label>
                <input type="text" id="newProdNombre" class="input-dark" required>
                <div style="display:flex; gap:10px;">
                    <div style="flex:1">
                        <label style="color:#aaa; display:block; margin-bottom:5px;">Precio (S/.)</label>
                        <input type="number" id="newProdPrecio" step="0.10" class="input-dark" required>
                    </div>
                    <div style="flex:1">
                        <label style="color:#aaa; display:block; margin-bottom:5px;">Categoría</label>
                        <select id="newProdCat" class="input-dark">
                            <option value="1">Pollos</option>
                            <option value="2">Parrillas</option>
                            <option value="3">Especiales</option>
                            <option value="4">Innovación</option>
                            <option value="5">Caldos</option>
                        </select>
                    </div>
                </div>
                <label style="color:#aaa; display:block; margin:10px 0 5px;">Imagen del Plato</label>
                <input type="file" id="newProdImagen" class="input-dark" accept="image/*">
                <p id="txt-imagen-actual" style="font-size:0.8rem; color:#F3C400; display:none;">Imagen actual guardada</p>
                <button type="submit" class="btn-add" style="width:100%; justify-content:center; margin-top:10px;">Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>

<div id="modalUsuario" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="modal-titulo-usuario">Gestión de Personal</h3>
            <button onclick="cerrarModal('modalUsuario')" class="close-btn">×</button>
        </div>
        <div class="modal-body">
            <form id="formUsuario">
                <input type="hidden" id="userId">
                
                <label style="color:#aaa; display:block; margin-bottom:5px;">Nombre Completo</label>
                <input type="text" id="userNombre" class="input-dark" required>
                
                <label style="color:#aaa; display:block; margin-top:10px; margin-bottom:5px;">Email</label>
                <input type="email" id="userEmail" class="input-dark" required>
                
                <label style="color:#aaa; display:block; margin-top:10px; margin-bottom:5px;">Teléfono (9 dígitos)</label>
                <input type="text" id="userTelefono" class="input-dark" 
                       maxlength="9"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       placeholder="999999999" required>

                <label style="color:#aaa; display:block; margin-top:10px; margin-bottom:5px;">Contraseña <small>(Opcional si editas)</small></label>
                <input type="password" id="userPassword" class="input-dark" autocomplete="new-password">

                <div style="display:flex; gap:10px; margin-top:10px;">
                    <div style="flex:1">
                        <label style="color:#aaa; display:block; margin-bottom:5px;">Rol</label>
                        <select id="userRol" class="input-dark" disabled style="background:#333; color:#aaa; cursor:not-allowed;">
                            <option value="Repartidor" selected>Repartidor</option>
                        </select>
                    </div>
                    <div id="div-estado-repartidor" style="flex:1;">
                        <label style="color:#aaa; display:block; margin-bottom:5px;">Estado Inicial</label>
                        <select id="userEstadoRepartidor" class="input-dark">
                            <option value="Disponible">Disponible</option>
                            <option value="Ocupado">Ocupado</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-add" style="width:100%; justify-content:center; margin-top:20px;">Guardar Personal</button>
            </form>
        </div>
    </div>
</div>

<div id="modalEstado" class="modal-overlay" style="z-index: 1100;">
    <div class="modal-content" style="width: 300px; text-align:center;">
        <h3>Cambiar Estado</h3>
        <p style="color:#aaa; margin-bottom:20px;">Selecciona el nuevo estado:</p>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <button onclick="confirmarCambioEstado('Disponible')" class="btn-add" style="background:rgba(76, 175, 80, 0.2); color:#4CAF50; border:1px solid #4CAF50; justify-content:center;">Disponible</button>
            <button onclick="confirmarCambioEstado('Ocupado')" class="btn-add" style="background:rgba(255, 193, 7, 0.2); color:#FFC107; border:1px solid #FFC107; justify-content:center;">Ocupado</button>
            <button onclick="confirmarCambioEstado('Inactivo')" class="btn-add" style="background:rgba(244, 67, 54, 0.2); color:#F44336; border:1px solid #F44336; justify-content:center;">Inactivo</button>
        </div>
        <button onclick="document.getElementById('modalEstado').style.display='none'" style="margin-top:15px; background:none; border:none; color:#aaa; cursor:pointer;">Cancelar</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

<script>
let repartidoresList = [];
let listaPersonalGlobal = []; // Almacena la lista de personal para poder editarlos fácilmente
let idRepartidorSeleccionado = null; 

document.addEventListener('DOMContentLoaded', async () => {
    await cargarRepartidores();
    cargarReportes(); 
    
    if (document.getElementById('nav-usuarios').classList.contains('active')) {
        cargarUsuarios();
    }
});

// NAVEGACIÓN TABS
function cambiarPestana(id) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
    
    const tab = document.getElementById('tab-' + id);
    if (id === 'dashboard') tab.style.display = 'flex';
    else tab.style.display = 'block';

    document.getElementById('nav-' + id).classList.add('active');
    
    const titulos = { 
        'dashboard': 'Dashboard', 
        'pedidos': 'Gestión de Pedidos', 
        'productos': 'Carta Digital', 
        'reportes': 'Reportes Financieros',
        'usuarios': 'Gestión de Personal', 
        'ajustes': 'Ajustes' 
    };
    document.getElementById('page-title').textContent = titulos[id] || 'Panel Admin';

    if (id === 'productos') cargarProductos();
    if (id === 'usuarios') cargarUsuarios(); 
}

// --- LÓGICA DE PERSONAL (REPARTIDORES) ---

async function cargarUsuarios() {
    try {
        const res = await fetch('api/usuarios/listar.php');
        const data = await res.json();
        
        // Guardamos en variable global para usar al editar
        listaPersonalGlobal = data;

        const tbody = document.getElementById('lista-usuarios');
        tbody.innerHTML = '';
        
        // FILTRO: Solo Repartidores y Admins (excluye clientes y errores 'N/A')
        const personalFiltrado = data.filter(u => u.nombre !== 'N/A' && u.rol !== 'Cliente');

        personalFiltrado.forEach(user => {
            const isRepartidor = user.rol === 'Repartidor';
            
            // Definir estilos de estado (Badges)
            let estadoHtml = '<span>-</span>';
            if (isRepartidor) {
                let badgeClass = 'badge-danger'; // Default inactivo
                if (user.estado_repartidor === 'Disponible') badgeClass = 'badge-success';
                else if (user.estado_repartidor === 'Ocupado') badgeClass = 'badge-warning';
                
                estadoHtml = `<span class="badge ${badgeClass}">${user.estado_repartidor}</span>`;
            }

            // Botón de cambio de estado (Solo repartidores)
            const toggleBtn = isRepartidor ? 
                `<button class="btn-toggle" onclick="abrirModalEstado(${user.id_usuario})" title="Cambiar Estado">
                    <i class="fa-solid fa-sync"></i>
                </button>` : '';

            // Botón Editar + Nuevo Botón Eliminar
            tbody.innerHTML += `
                <tr>
                    <td>${user.id_usuario}</td>
                    <td><strong>${user.nombre}</strong></td>
                    <td>${user.email}</td>
                    <td>${user.rol}</td>
                    <td>${estadoHtml}</td>
                    <td class="admin-actions">
                        <button class="btn-edit" onclick="prepararEdicionPorId(${user.id_usuario})" title="Editar Datos" style="background:none; border:none; color:#aaa; cursor:pointer; margin-right:5px;">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        ${toggleBtn}
                        <button onclick="eliminarUsuario(${user.id_usuario})" title="Eliminar Usuario" style="background:none; border:none; color:#ff5252; cursor:pointer; margin-left:5px;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        if (personalFiltrado.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">No hay personal registrado.</td></tr>';
        }
    } catch(e) {
        console.error(e);
        document.getElementById('lista-usuarios').innerHTML = '<tr><td colspan="6">Error de conexión.</td></tr>';
    }
}

// BUSCA EL USUARIO EN LA LISTA GLOBAL Y ABRE EL MODAL
function prepararEdicionPorId(id) {
    const user = listaPersonalGlobal.find(u => u.id_usuario == id);
    if(user) {
        abrirModalUsuario('editar', user);
    } else {
        alert("Error: No se encontraron datos del usuario.");
    }
}

function abrirModalUsuario(modo, user = {}) {
    document.getElementById('formUsuario').reset();
    document.getElementById('userId').value = user.id_usuario || '';
    
    const titulo = document.getElementById('modal-titulo-usuario');
    const inputPass = document.getElementById('userPassword');
    const inputEmail = document.getElementById('userEmail'); // Referencia al input email

    // Siempre forzamos el Rol a Repartidor por defecto en la UI
    document.getElementById('userRol').value = 'Repartidor';

    if (modo === 'editar') {
        titulo.textContent = `Editar Personal: ${user.nombre}`;
        document.getElementById('userNombre').value = user.nombre;
        document.getElementById('userEmail').value = user.email;
        inputEmail.placeholder = "correo@ejemplo.com"; // Placeholder normal
        document.getElementById('userTelefono').value = user.telefono || '';
        document.getElementById('userEstadoRepartidor').value = user.estado_repartidor || 'Inactivo';
        inputPass.required = false; 
        
        if(user.rol === 'Administrador') {
             const rolSelect = document.getElementById('userRol');
             if(!rolSelect.querySelector('option[value="Administrador"]')){
                 const opt = document.createElement('option');
                 opt.value = 'Administrador'; opt.text = 'Administrador'; rolSelect.add(opt);
             }
             rolSelect.value = 'Administrador';
        }
    } else {
        // MODO CREAR
        titulo.textContent = 'Crear Nuevo Personal';
        inputPass.required = true; 
        document.getElementById('userEstadoRepartidor').value = 'Disponible';
        
        // TRUCO VISUAL: Cambiar placeholder para indicar funcionalidad automática
        inputEmail.placeholder = "Usuario (ej: juan)";
    }

    document.getElementById('modalUsuario').style.display = 'flex';
}

// GUARDAR (CREAR O EDITAR)
document.getElementById('formUsuario').addEventListener('submit', async (e) => {
    e.preventDefault();

    const userId = document.getElementById('userId').value;
    const telefono = document.getElementById('userTelefono').value;
    const rol = document.getElementById('userRol').value;
    
    // 1. LÓGICA DE EMAIL AUTOMÁTICO PARA REPARTIDORES
    let emailIngresado = document.getElementById('userEmail').value.trim();
    
    // Si es repartidor y NO escribió un correo completo (sin @), le agregamos el dominio
    if (rol === 'Repartidor' && !emailIngresado.includes('@')) {
        emailIngresado = emailIngresado + '@delivery.com';
    }

    // Validación Final de Teléfono
    if (telefono.length !== 9) {
        alert("El teléfono debe tener exactamente 9 dígitos.");
        return;
    }

    const data = {
        id_usuario: userId,
        nombre: document.getElementById('userNombre').value,
        email: emailIngresado, // Usamos el email procesado
        telefono: telefono,
        rol: rol,
        password: document.getElementById('userPassword').value,
        estado_repartidor: document.getElementById('userEstadoRepartidor').value
    };

    try {
        const res = await fetch('api/usuarios/gestionar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await res.json();
        
        if (result.ok) {
            alert(result.mensaje); // Mostrará "Usuario creado exitosamente"
            cerrarModal('modalUsuario');
            cargarUsuarios();
        } else {
            alert('Error: ' + (result.error || 'No se pudo guardar.'));
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión o servidor.');
    }
});
// --- CAMBIO DE ESTADO (MODAL RÁPIDO) ---

function abrirModalEstado(idRepartidor) {
    idRepartidorSeleccionado = idRepartidor;
    document.getElementById('modalEstado').style.display = 'flex';
}

async function confirmarCambioEstado(nuevoEstado) {
    if (!idRepartidorSeleccionado) return;

    try {
        // Enviamos SOLO lo necesario. La API corregida sabrá actualizar solo esto.
        const res = await fetch('api/usuarios/gestionar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_usuario: idRepartidorSeleccionado,
                rol: 'Repartidor', // Requerido para validar lógica interna
                estado_repartidor: nuevoEstado
                // NO enviamos email ni nombre para evitar validaciones innecesarias
            })
        });

        const result = await res.json();
        
        if (result.ok) {
            document.getElementById('modalEstado').style.display = 'none';
            cargarUsuarios(); // Recargar tabla
            if(typeof cargarRepartidores === 'function') cargarRepartidores(); // Actualizar otras vistas si existen
        } else {
             alert('Error: ' + (result.error || 'No se pudo cambiar el estado.'));
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión.');
    }
}

// --- OTRAS FUNCIONES (Productos, Modales generales) ---

async function cargarRepartidores() { try { const res = await fetch('api/usuarios/repartidores.php'); repartidoresList = await res.json(); } catch (e) {} }

function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }
window.onclick = function(e) { if(e.target.className === 'modal-overlay') e.target.style.display = 'none'; }

// ... (Resto de funciones de productos y pedidos se mantienen igual) ...
function abrirModalCrear() { document.getElementById('formProducto').reset(); document.getElementById('prodId').value = ''; document.getElementById('modal-titulo-prod').textContent = 'Nuevo Plato'; document.getElementById('txt-imagen-actual').style.display = 'none'; document.getElementById('modalProducto').style.display = 'flex'; }
function prepararEdicion(id, nombre, precio, id_cat) { document.getElementById('prodId').value = id; document.getElementById('newProdNombre').value = nombre; document.getElementById('newProdPrecio').value = precio; document.getElementById('newProdCat').value = id_cat; document.getElementById('modal-titulo-prod').textContent = 'Editar Plato'; document.getElementById('txt-imagen-actual').style.display = 'block'; document.getElementById('modalProducto').style.display = 'flex'; }

// *** IMPORTANTE: EL LISTENER DE PRODUCTOS FUE ELIMINADO PARA EVITAR DUPLICIDAD (ESTÁ EN productos.php) ***

async function verDetalle(id) { document.getElementById('modalDetalle').style.display = 'flex'; document.getElementById('modal-id-pedido').textContent = id; const lista = document.getElementById('modal-lista-productos'); lista.innerHTML = '<tr><td colspan="3">Cargando...</td></tr>'; const res = await fetch(`api/pedidos/detalles.php?id=${id}`); const data = await res.json(); lista.innerHTML = ''; data.forEach(d => { lista.innerHTML += `<tr><td>${d.nombre}</td><td style="text-align:center">${d.cantidad}</td><td style="color:#F3C400">S/ ${d.subtotal}</td></tr>`; }); }
async function cambiarEstado(id, est) { await fetch('api/pedidos/actualizar.php', {method:'POST',body:JSON.stringify({id:id, estado:est})}); cargarReportes(); if(typeof cargarPedidosAPI === 'function') cargarPedidosAPI();}
async function asignarRep(id, rep) { if(rep && confirm('¿Asignar?')) { await fetch('api/pedidos/actualizar.php', {method:'POST',body:JSON.stringify({id:id, id_repartidor:rep})}); if(typeof cargarPedidosAPI === 'function') cargarPedidosAPI(); } }

// --- NUEVA FUNCIÓN PARA ELIMINAR USUARIOS ---
async function eliminarUsuario(id) {
    if(!confirm('¿Estás seguro de ELIMINAR a este usuario? Esta acción no se puede deshacer.')) return;
    
    try {
        const res = await fetch('api/usuarios/eliminar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        
        if(data.ok) {
            alert('Usuario eliminado correctamente.');
            cargarUsuarios(); // Recargar la tabla
            // Si eliminaste un repartidor, actualizamos la lista global
            if(typeof cargarRepartidores === 'function') cargarRepartidores();
        } else {
            alert('Error: ' + (data.error || 'No se pudo eliminar'));
        }
    } catch (e) {
        console.error(e);
        alert('Error de conexión');
    }
}
</script>
</body>
</html>