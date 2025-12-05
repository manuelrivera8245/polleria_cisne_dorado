<div class="login-container" style="max-width: 450px; margin: 50px auto; padding: 30px; background: #222; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
    
    <h2 style="color: #F3C400; text-align: center; margin-bottom: 25px;">CREAR CUENTA</h2>
    
    <form id="registro-form">
        
        <div style="margin-bottom: 15px;">
            <label for="nombre" style="color: #ccc;">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" required
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email" style="color: #ccc;">Correo Electrónico</label>
            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
            <small id="email-error" style="color: #ff4444; display: none; margin-top: 5px;">Correo inválido</small>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="telefono" style="color: #ccc;">Teléfono / Celular</label>
            <input type="tel" id="telefono" name="telefono" placeholder="Ej: 987654321" required
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="password" style="color: #ccc;">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="******" required minlength="6"
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
        </div>

        <div style="margin-bottom: 25px;">
            <label for="confirm-password" style="color: #ccc;">Confirmar Contraseña</label>
            <input type="password" id="confirm-password" name="confirm-password" placeholder="******" required
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
            <small id="password-error" style="color: #ff4444; display: none; margin-top: 5px;">Las contraseñas no coinciden</small>
        </div>

        <button type="submit" class="menu-btn" style="width: 100%; cursor: pointer; font-weight: bold; padding: 12px; background-color: #F3C400; border: none; border-radius: 5px; color: #000;">
            REGISTRARSE
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; color: #888;">
        ¿Ya tienes cuenta? <a href="index.php?page=login" style="color: #F3C400; text-decoration: none;">Inicia Sesión aquí</a>
    </p>
</div>

<script src="public/js/auth.js"></script>