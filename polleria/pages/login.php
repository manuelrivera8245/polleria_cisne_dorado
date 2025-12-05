<div class="login-container" style="max-width: 400px; margin: 80px auto; padding: 20px; background: #222; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
    <h2 style="color: #F3C400; text-align: center;">Iniciar Sesión</h2>
    
    <form id="formLogin">
        <div style="margin-bottom: 15px;">
            <label style="color: #ccc;">Correo Electrónico:</label>
            <input type="email" name="email" required 
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="color: #ccc;">Contraseña:</label>
            <input type="password" name="password" required 
                   style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: none;">
        </div>

        <button type="submit" class="menu-btn" style="width: 100%; cursor: pointer; font-weight: bold;">
            INGRESAR
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 15px; color: #888;">
        ¿No tienes cuenta? <a href="index.php?page=registro" style="color: #F3C400;">Regístrate aquí</a>
    </p>
</div>

<script src="public/js/auth.js"></script>