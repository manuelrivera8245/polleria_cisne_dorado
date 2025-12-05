<section id="tab-ajustes" class="tab-content" style="display: none;">
    
    <div style="margin-bottom:20px;">
        <h3 style="color:#aaa; font-weight:normal; margin:0;">Control de Tienda y Perfil</h3>
    </div>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 300px; background:#1e1e1e; padding:30px; border-radius:12px; border:1px solid #333;">
            <h4 style="color:#F3C400; margin-top:0;">Estado del Restaurante</h4>
            <p style="color:#aaa; font-size:0.9rem;">Si cierras la tienda, los clientes no podrán realizar pedidos.</p>
            
            <div style="margin-top:20px; display:flex; align-items:center; gap:15px;">
                <label class="switch">
                    <input type="checkbox" id="check-tienda-abierta" onchange="actualizarTextoEstado(this.checked)">
                    <span class="slider round"></span>
                </label>
                <span id="texto-estado" style="font-weight:bold; color:#fff;">Cargando...</span>
            </div>

            <div style="margin-top:20px;">
                <label style="color:#aaa; font-size:0.8rem;">Mensaje para clientes (Opcional)</label>
                <input type="text" id="mensaje-cierre" class="input-dark" placeholder="Ej: Abrimos a las 6:00 PM">
            </div>

            <button onclick="guardarEstadoTienda()" class="btn-add" style="margin-top:15px; width:100%; justify-content:center;">
                <i class="fa-solid fa-save"></i> Guardar Estado Actual
            </button>
        </div>
    </div>
</section>

<style>
/* Estilos del Switch */
.switch { position: relative; display: inline-block; width: 60px; height: 34px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }

/* Colores: Verde (Checked) / Rojo (Unchecked) */
input:checked + .slider { background-color: #4CAF50; }
input:not(:checked) + .slider { background-color: #F44336; } /* Rojo explícito */
input:checked + .slider:before { transform: translateX(26px); }
</style>

<script>
/**
 * Carga los ajustes actuales de la tienda y del administrador.
 */
async function cargarAjustes() {
    try {
        const res = await fetch('api/ajustes/obtener.php');
        const data = await res.json();
        
        if (data.ok) {
            // 1. Configurar Switch de Tienda
            const abierta = data.config.tienda_abierta === true; 
            const check = document.getElementById('check-tienda-abierta');
            
            check.checked = abierta;
            document.getElementById('mensaje-cierre').value = data.config.mensaje || '';
            
            // Forzar actualización del texto visualmente
            actualizarTextoEstado(abierta);

            // 2. Mostrar datos del Admin (Ahora sí deberían llegar)
            if(data.admin) {
                document.getElementById('admin-nombre').value = data.admin.nombre;
                document.getElementById('admin-email').value = data.admin.email;
            }
        }
    } catch (e) { 
        console.error("Error cargando ajustes:", e); 
    }
}

/**
 * Actualiza el texto visual del estado de la tienda.
 * @param {boolean} estaAbierto Indica si la tienda está abierta.
 */
function actualizarTextoEstado(estaAbierto) {
    const txt = document.getElementById('texto-estado');
    if(estaAbierto) {
        txt.textContent = "TIENDA ABIERTA";
        txt.style.color = "#4CAF50"; // Verde
    } else {
        txt.textContent = "TIENDA CERRADA";
        txt.style.color = "#F44336"; // Rojo
    }
}

/**
 * Envía la configuración actual de apertura/cierre al servidor.
 */
async function guardarEstadoTienda() {
    const estado = document.getElementById('check-tienda-abierta').checked;
    const mensaje = document.getElementById('mensaje-cierre').value;

    const btn = document.querySelector('button[onclick="guardarEstadoTienda()"]');
    // Guardar texto original
    const textoOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

    try {
        const res = await fetch('api/ajustes/guardar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ tienda_abierta: estado, mensaje: mensaje })
        });
        const data = await res.json();
        
        if(data.ok) {
            alert("¡Estado actualizado correctamente!");
            if(typeof actualizarDashboardStatus === 'function') actualizarDashboardStatus();
        } else {
            alert("Error: " + (data.error || "No se pudo guardar"));
        }
    } catch (e) {
        alert("Error de conexión");
    } finally {
        btn.innerHTML = textoOriginal;
    }
}
</script>