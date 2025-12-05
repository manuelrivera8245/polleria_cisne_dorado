<section id="tab-productos" class="tab-content" style="display: none;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="color:#aaa; font-weight:normal; margin:0;">Catálogo de Productos</h3>
        <button class="btn-add" onclick="abrirModalCrear()">
            <i class="fa-solid fa-plus"></i> Nuevo Plato
        </button>
    </div>

    <div id="grid-productos" class="productos-grid">
        <p style="text-align:center; color:#666; width:100%;">Cargando carta...</p>
    </div>

</section>

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
                        <input type="number" id="newProdPrecio" step="0.01" min="0" onchange="if(this.value < 0) this.value = 0;" class="input-dark" required>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('grid-productos')) {
        cargarProductos();
    }
});

/**
 * Obtiene la lista de productos y la renderiza en tarjetas.
 */
async function cargarProductos() {
    const grid = document.getElementById('grid-productos');
    
    try {
        const res = await fetch('api/productos/listar.php');
        const data = await res.json();
        grid.innerHTML = '';

        if(data.data && data.data.length > 0) {
            data.data.forEach(p => {
                // Lógica de Estado
                const esAgotado = p.estado === 'Agotado';
                const estiloCard = esAgotado ? 'opacity:0.6; border-color:#555;' : 'border-color:#F3C400;';
                const textoBtn = esAgotado ? 'Activar' : 'Agotar';
                const colorBtn = esAgotado ? '#4CAF50' : '#ff5252';
                const iconoBtn = esAgotado ? 'fa-check' : 'fa-ban';

                // Imagen
                let imagenHtml = `<div class="prod-icon" style="color:${esAgotado?'#555':'#ddd'};"><i class="fa-solid fa-utensils"></i></div>`;
                if(p.imagen && p.imagen !== "") {
                    // Agregamos timestamp para forzar recarga de imagen si cambió
                    imagenHtml = `<div style="height:100px; margin-bottom:10px; overflow:hidden; border-radius:8px;">
                                    <img src="${p.imagen}?v=${new Date().getTime()}" style="width:100%; height:100%; object-fit:cover;">
                                  </div>`;
                }

                grid.innerHTML += `
                <div class="prod-card" style="${estiloCard} position:relative;">
                    <span style="position:absolute; top:10px; right:10px; background:${esAgotado?'#333':'#F3C400'}; color:${esAgotado?'#aaa':'#000'}; padding:2px 8px; border-radius:4px; font-size:0.7rem; font-weight:bold; z-index:2;">
                        ${p.estado}
                    </span>
                    ${imagenHtml}
                    <div style="height:50px; display:flex; align-items:center; justify-content:center;">
                        <h4 class="prod-title" style="margin:0;">${p.nombre}</h4>
                    </div>
                    <p class="prod-price" style="color:${esAgotado?'#777':'#F3C400'}">S/ ${parseFloat(p.precio).toFixed(2)}</p>
                    <div style="display:flex; gap:10px; justify-content:center; margin-top:15px;">
                        <button class="btn-action" onclick="cambiarEstadoProd(${p.id_producto}, '${p.estado}')" style="color:${colorBtn}; border:1px solid ${colorBtn};" title="${textoBtn}">
                            <i class="fa-solid ${iconoBtn}"></i>
                        </button>
                        <button class="btn-action" onclick="prepararEdicion(${p.id_producto}, '${p.nombre}', '${p.precio}', '${p.id_categoria}')" style="color:#aaa; border:1px solid #555;" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn-action" onclick="eliminarProducto(${p.id_producto})" style="color:#ff5252; border:1px solid #555;" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>`;
            });
        } else {
            grid.innerHTML = '<p style="text-align:center; width:100%; color:#666;">No hay productos registrados.</p>';
        }
    } catch(e){ 
        console.error(e);
        grid.innerHTML = '<p style="color:red; text-align:center;">Error al cargar productos.</p>'; 
    }
}

// 2. GUARDAR PRODUCTO (Crear o Editar)
document.getElementById('formProducto').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const id = document.getElementById('prodId').value;
    const btn = e.target.querySelector('button[type="submit"]');
    const textoOriginal = btn.innerHTML;
    
    btn.innerHTML = 'Guardando...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('nombre', document.getElementById('newProdNombre').value);
    formData.append('precio', document.getElementById('newProdPrecio').value);
    formData.append('id_categoria', document.getElementById('newProdCat').value);
    
    const imagenInput = document.getElementById('newProdImagen');
    if(imagenInput.files[0]) {
        formData.append('imagen', imagenInput.files[0]);
    }

    if(id) formData.append('id', id);

    // Seleccionar URL correcta
    let url = id ? 'api/productos/actualizar.php' : 'api/productos/crear.php';

    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        
        // Verificar si es 404 (Archivo no encontrado)
        if(res.status === 404) {
            alert(`ERROR CRÍTICO: No se encuentra el archivo "${url}".\n\nPor favor ve a la carpeta "api/productos/" y asegúrate de que el archivo se llame "actualizar.php" (con 'a') y no "actulizar.php".`);
            throw new Error("Archivo no encontrado");
        }

        const data = await res.json();
        
        if(data.ok) {
            alert(id ? 'Producto actualizado correctamente' : 'Producto creado correctamente');
            cerrarModal('modalProducto');
            cargarProductos();
        } else {
            alert('Error del servidor: ' + (data.error || 'Desconocido'));
        }
    } catch (error) {
        console.error(error);
        if(error.message !== "Archivo no encontrado") {
            alert('Error de conexión. Revisa la consola para más detalles.');
        }
    } finally {
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    }
});

/**
 * Alterna el estado de disponibilidad del producto.
 * @param {number} id ID del producto.
 * @param {string} estadoActual Estado actual ('Disponible' o 'Agotado').
 */
async function cambiarEstadoProd(id, estadoActual) {
    const nuevo = estadoActual === 'Disponible' ? 'Agotado' : 'Disponible';
    try {
        await fetch('api/productos/estado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, estado: nuevo })
        });
        cargarProductos();
    } catch(e) { alert('Error al cambiar estado'); }
}

/**
 * Elimina un producto de la carta previa confirmación.
 * @param {number} id ID del producto.
 */
async function eliminarProducto(id) {
    if(!confirm('¿Estás seguro de ELIMINAR este plato?')) return;
    try {
        await fetch('api/productos/eliminar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id })
        });
        cargarProductos();
    } catch(e) { alert('Error al eliminar'); }
}

/**
 * Prepara el modal con los datos del producto para su edición.
 * @param {number} id ID del producto.
 * @param {string} nombre Nombre del producto.
 * @param {number} precio Precio del producto.
 * @param {number} id_cat ID de la categoría.
 */
function prepararEdicion(id, nombre, precio, id_cat) {
    document.getElementById('prodId').value = id;
    document.getElementById('newProdNombre').value = nombre;
    document.getElementById('newProdPrecio').value = precio;
    document.getElementById('newProdCat').value = id_cat;
    document.getElementById('modal-titulo-prod').textContent = 'Editar Plato';
    document.getElementById('txt-imagen-actual').style.display = 'block';
    document.getElementById('modalProducto').style.display = 'flex';
}
</script>