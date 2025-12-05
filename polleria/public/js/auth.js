/**
 * Clase AuthManager
 * Gestiona el inicio de sesión y registro
 */
class AuthManager {
    /**
     * Constructor. Obtiene referencias a los formularios.
     */
    constructor() {
        this.loginForm = document.getElementById("formLogin");
        this.registroForm = document.getElementById("registro-form");
        this.init();
    }

    /**
     * Configura los eventos de envío de formulario.
     */
    init() {
        if (this.loginForm) {
            this.loginForm.addEventListener("submit", (e) => this.login(e));
        }
        if (this.registroForm) {
            this.registroForm.addEventListener("submit", (e) => this.registrar(e));
        }
    }

    /**
     * Maneja el proceso de inicio de sesión.
     * @param {Event} e Evento del formulario.
     */
    async login(e) {
        e.preventDefault();
        const datos = Object.fromEntries(new FormData(this.loginForm));
        await this.enviarDatos("api/auth/login.php", datos, (res) => {
            window.location.href = res.redirect;
        });
    }

    /**
     * Maneja el proceso de registro de nuevo usuario con validaciones.
     * @param {Event} e Evento del formulario.
     */
    async registrar(e) {
        e.preventDefault();
        const datos = Object.fromEntries(new FormData(this.registroForm));

        // Referencias a los elementos de error (etiquetas <small> en tu HTML)
        const passError = document.getElementById("password-error");
        const emailError = document.getElementById("email-error");

        // Limpiar errores previos
        passError.style.display = "none";
        emailError.style.display = "none";
        let valid = true;

        // Validación 1: Contraseñas
        if (datos.password !== datos['confirm-password']) {
            passError.textContent = "Las contraseñas no coinciden.";
            passError.style.display = "block"; // Mostrar error dinámicamente
            valid = false;
        }

        // Validación 2: Longitud (Ejemplo extra para asegurar puntos)
        if (datos.password.length < 6) {
            passError.textContent = "La contraseña debe tener al menos 6 caracteres.";
            passError.style.display = "block";
            valid = false;
        }

        if (!valid) return; // Detener si hay errores visuales

        // Enviar al backend si todo está OK
        await this.enviarDatos("api/auth/registro.php", datos, (res) => {
            // Éxito: podrías mostrar un modal bonito o redirigir
            window.location.href = "index.php?page=home";
        });
    }

    /**
     * Método auxiliar para enviar datos JSON a una URL.
     * @param {string} url Endpoint de destino.
     * @param {object} datos Objeto con los datos a enviar.
     * @param {function} onSuccess Callback a ejecutar si la respuesta es exitosa.
     */
    async enviarDatos(url, datos, onSuccess) {
        try {
            const res = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datos)
            });

            const resultado = await res.json();

            if (resultado.ok) {
                onSuccess(resultado);
            } else {
                alert(" Error: " + resultado.message);
            }
        } catch (error) {
            console.error(error);
            alert("Error de conexión con el servidor");
        }
    }
}

// Instanciar
document.addEventListener("DOMContentLoaded", () => {
    new AuthManager();
});