class AdminDashboard {
    /**
     * Inicializa el dashboard con el ID del cuerpo de la tabla.
     * @param {string} tableBodyId ID del elemento tbody.
     */
    constructor(tableBodyId) {
        this.tableBody = document.getElementById(tableBodyId);
        this.init();
    }

    /**
     * Inicia la carga de datos si el elemento tabla existe.
     */
    init() {
        if (this.tableBody) {
            this.cargarPedidos();
        }
    }

    /**
     * Obtiene la lista de pedidos desde el backend y renderiza la tabla.
     */
    async cargarPedidos() {
        try {
            const res = await fetch("api/pedidos/listar.php");
            const pedidos = await res.json();
            this.renderizarTabla(pedidos);
        } catch (error) {
            console.error(error);
        }
    }

    /**
     * Envía una solicitud para actualizar el estado de un pedido.
     * @param {number} id ID del pedido.
     * @param {string} nuevoEstado Nuevo estado seleccionado.
     */
    async cambiarEstado(id, nuevoEstado) {
        try {
            const res = await fetch("api/pedidos/actualizar.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id, estado: nuevoEstado })
            });
            const data = await res.json();

            if (data.ok) {
                alert("Estado actualizado correctamente");
                this.cargarPedidos(); // Recargar tabla para ver cambios (o actualizar DOM directo)
            } else {
                alert("Error al actualizar");
            }
        } catch (error) {
            alert("Error de conexión");
        }
    }

    /**
     * Elimina un pedido previa confirmación del usuario.
     * @param {number} id ID del pedido a eliminar.
     */
    async eliminarPedido(id) {
        if (!confirm("¿Estás seguro de ELIMINAR este pedido? Esta acción no se puede deshacer.")) return;

        try {
            const res = await fetch("api/pedidos/eliminar.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();

            if (data.ok) {
                // Eliminamos la fila del DOM visualmente sin recargar toda la página
                document.getElementById(`fila-${id}`).remove();
                alert("Pedido eliminado");
            } else {
                alert("Error al eliminar");
            }
        } catch (error) {
            alert("Error de conexión");
        }
    }

    /**
     * Dibuja las filas de la tabla con los datos de los pedidos.
     * @param {Array} pedidos Lista de objetos de pedido.
     */
    renderizarTabla(pedidos) {
        this.tableBody.innerHTML = "";

        const estados = ['Pendiente', 'En Preparacion', 'Listo', 'En Camino', 'Entregado', 'Rechazado'];

        pedidos.forEach(p => {
            const tr = document.createElement("tr");
            tr.id = `fila-${p.id_pedido}`; // ID para manipular el DOM luego
            tr.style.borderBottom = "1px solid #444";

            // Crear selector de opciones dinámico
            let opciones = "";
            estados.forEach(est => {
                const selected = p.estado === est ? "selected" : "";
                opciones += `<option value="${est}" ${selected}>${est}</option>`;
            });

            tr.innerHTML = `
                <td style="padding:10px;">#${p.id_pedido}</td>
                <td style="padding:10px;">${p.nombre_invitado || 'Cliente Web'}</td>
                <td style="padding:10px;">${p.direccion_entrega}</td>
                <td style="padding:10px;">S/ ${p.total}</td>
                
                <td style="padding:10px;">
                    <select onchange="dashboard.cambiarEstado(${p.id_pedido}, this.value)" 
                            style="padding:5px; border-radius:4px; background:#222; color:#fff; border:1px solid #555;">
                        ${opciones}
                    </select>
                </td>
                
                <td style="padding:10px;">
                    <button onclick="dashboard.eliminarPedido(${p.id_pedido})" 
                            style="background:#c62828; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;
            this.tableBody.appendChild(tr);
        });
    }
}

const dashboard = new AdminDashboard("listaPedidosBody");