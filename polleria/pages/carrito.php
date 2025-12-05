<section class="carrito-section">
    
    <div id="vista-carrito">
        <h2 class="carrito-title">
            <i class="fa-solid fa-cart-shopping"></i> Tu Pedido
        </h2>
        
        <div id="contenedor-items">
            <p class="carrito-empty">Tu carrito está vacío.</p>
        </div>

        <div class="carrito-footer">
            <h3 class="carrito-total-label">Total: <span id="total-display" class="carrito-total-amount">S/ 0.00</span></h3>
            
            <button id="btn-procesar" class="menu-btn btn-procesar">
                Continuar al Pago <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </div>

<div id="vista-formulario" class="vista-formulario">
        <h2 class="form-title">Finalizar Compra</h2>
        <button id="btn-volver" class="btn-volver">
            <i class="fa-solid fa-arrow-left"></i> Volver al carrito
        </button>

        <form id="formPedido" class="form-pedido">
            <div class="form-group">
                <input type="text" name="nombre" placeholder="Nombre completo" required class="input-style"
       value="<?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : ''; ?>"
       <?php echo isset($_SESSION['nombre']) ? 'readonly style="background-color: #333; cursor: not-allowed; color: #aaa;"' : ''; ?>
>
            </div>

            <div class="form-group">
                <input type="tel" name="telefono" placeholder="Teléfono (9 dígitos)" pattern="[0-9]{9}" required class="input-style"
                       value="<?php echo isset($_SESSION['telefono']) ? htmlspecialchars($_SESSION['telefono']) : ''; ?>">
            </div>

            <div class="form-group">
                <input type="text" name="direccion" placeholder="Dirección de entrega" required class="input-style">
            </div>

            <div class="form-group">
                <label class="form-label">Método de Pago:</label>
                <select name="metodo" class="input-style">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Yape/Plin">Yape / Plin</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Total a Pagar (S/):</label>
                <input type="number" id="input-total" name="total" step="0.10" readonly class="input-style input-total">
            </div>

            <button type="submit" class="menu-btn btn-confirmar">
                CONFIRMAR PEDIDO
            </button>
        </form>
    </div>

</section>

<script src="public/js/carrito.js"></script>