<div id="tab-usuarios" class="tab-content" style="display: none;">
    
    <div class="header-action">
        <button class="btn-add" onclick="abrirModalUsuario('crear')">
            <i class="fa-solid fa-plus"></i> Nuevo Usuario/Repartidor
        </button>
    </div>

    <div class="card">
        <h3>Lista de Personal y Clientes</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado Repartidor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="lista-usuarios">
                <tr><td colspan="6">Cargando usuarios...</td></tr>
            </tbody>
        </table>
    </div>

</div>