document.addEventListener('DOMContentLoaded', () => {
    // Referencias al DOM
    const vistaCarrito = document.getElementById('vista-carrito');
    const vistaFormulario = document.getElementById('vista-formulario');
    const contenedorItems = document.getElementById('contenedor-items');
    const totalDisplay = document.getElementById('total-display');
    const inputTotal = document.getElementById('input-total');
    const btnProcesar = document.getElementById('btn-procesar');
    const btnVolver = document.getElementById('btn-volver');
    const formPedido = document.getElementById('formPedido');

    // 1. Cargar productos del LocalStorage
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

    // Función para renderizar la tabla
    /**
     * Renderiza la tabla visual del carrito y calcula el total.
     * Verifica disponibilidad de tienda.
     */
    function renderizarCarrito() {
        contenedorItems.innerHTML = '';
        let totalCalculado = 0;

        if (carrito.length === 0) {
            contenedorItems.innerHTML = '<p style="color: #888; text-align: center;">Tu carrito está vacío ☹️</p>';
            btnProcesar.style.display = 'none'; // Ocultar botón si no hay items
            totalDisplay.textContent = 'S/ 0.00';
            return;
        }

        btnProcesar.style.display = 'block';

        carrito.forEach((prod, index) => {
            const subtotal = prod.precio * prod.cantidad;
            totalCalculado += subtotal;

            const itemDiv = document.createElement('div');
            itemDiv.classList.add('item-carrito');
            itemDiv.innerHTML = `
                <div style="color: #fff;">
                    <strong>${prod.nombre}</strong><br>
                    <small style="color: #aaa;">Cant: ${prod.cantidad} x S/ ${parseFloat(prod.precio).toFixed(2)}</small>
                </div>
                <div style="display: flex; align-items: center;">
                    <span style="color: #F3C400; font-weight: bold; margin-right: 10px;">S/ ${subtotal.toFixed(2)}</span>
                    <button class="btn-eliminar" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
                </div>
            `;
            contenedorItems.appendChild(itemDiv);
        });

        // Actualizar totales visuales y del input oculto
        totalDisplay.textContent = `S/ ${totalCalculado.toFixed(2)}`;
        inputTotal.value = totalCalculado.toFixed(2);

        // Verificar estado de la tienda
        fetch('api/ajustes/obtener.php')
            .then(res => res.json())
            .then(data => {
                if (data.config && !data.config.tienda_abierta) {
                    // Tienda cerrada
                    btnProcesar.disabled = true;
                    btnProcesar.style.backgroundColor = "#555";
                    btnProcesar.textContent = "TIENDA CERRADA";
                    btnProcesar.title = data.config.mensaje || "Vuelve más tarde";

                    // Mostrar alerta visual
                    const alerta = document.createElement('div');
                    alerta.style = "background: #ff5252; color: white; padding: 10px; border-radius: 5px; margin-top: 10px; text-align: center;";
                    alerta.textContent = "⛔ " + (data.config.mensaje || "No estamos atendiendo pedidos por el momento.");

                    // Insertar antes del botón si no existe ya
                    if (!document.getElementById('msg-closed')) {
                        alerta.id = 'msg-closed';
                        btnProcesar.parentNode.insertBefore(alerta, btnProcesar);
                    }
                }
            });
    }

    // 2. Eventos de la Interfaz

    // Eliminar producto
    contenedorItems.addEventListener('click', (e) => {
        if (e.target.closest('.btn-eliminar')) {
            const index = e.target.closest('.btn-eliminar').dataset.index;
            carrito.splice(index, 1); // Quitar del array
            localStorage.setItem('carrito', JSON.stringify(carrito)); // Guardar cambios
            renderizarCarrito(); // Repintar
        }
    });

    // Pasar al formulario
    btnProcesar.addEventListener('click', () => {
        vistaCarrito.style.display = 'none';
        vistaFormulario.style.display = 'block';
    });

    // Volver al carrito
    btnVolver.addEventListener('click', () => {
        vistaFormulario.style.display = 'none';
        vistaCarrito.style.display = 'block';
    });

    // 3. Enviar Pedido al Backend (Corregido para enviar productos)
    formPedido.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btnSubmit = formPedido.querySelector('button[type="submit"]');
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Procesando...";

        // Recopilar datos
        const formData = new FormData(formPedido);
        const dataEnviar = {
            nombre: formData.get('nombre'),
            telefono: formData.get('telefono'),
            direccion: formData.get('direccion'),
            metodo: formData.get('metodo'),
            total: formData.get('total'),
            productos: carrito // ¡IMPORTANTE! Enviamos el array de productos
        };

        try {
            const response = await fetch('api/pedidos/crear.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataEnviar)
            });

            const result = await response.json();

            if (result.ok) {
                // Limpiar carrito tras éxito
                localStorage.removeItem('carrito');

                // Mostrar confirmación bonita (puedes usar SweetAlert si quieres)
                alert(`¡Pedido Confirmado!\nTu código de seguimiento es: ${result.tracking}`);
                window.location.href = 'index.php?page=mis_pedidos'; // Redirigir a "Mis Pedidos"
            } else {
                throw new Error(result.mensaje || "Error desconocido");
            }

        } catch (error) {
            alert("Error: " + error.message);
            btnSubmit.disabled = false;
            btnSubmit.textContent = "CONFIRMAR PEDIDO";
        }
    });

    // Inicializar
    renderizarCarrito();
});