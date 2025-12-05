#  Sistema de Gestión de Pedidos - Cisne Dorado

> **Plataforma web integral para la gestión de delivery, administración de inventario y logística de entregas.**

---

## Integrantes del Equipo (Collaborators)
* Jose Manuel Rivera Laura (@manuelrivera8245)
* Angelo Jesus Saavedra Chavez (@Gelo-cpu)
* Jeremy Yober Reyes Garcilazo (@jerreyesga-debug)
---

##  Descripción del Proyecto

Este sistema fue desarrollado para modernizar el proceso de atención de la **Pollería Cisne Dorado**. Soluciona la necesidad de centralizar los pedidos vía web, permitiendo a los clientes realizar compras sin intermediarios, a los administradores controlar el flujo de caja y productos, y a los repartidores gestionar sus entregas desde una interfaz móvil.

El proyecto sigue una arquitectura **MVC (Modelo-Vista-Controlador)** adaptada, separando la lógica de negocio (API Backend) de las vistas (Frontend).

---

##  Stack Tecnológico

* **Backend:** PHP 8.0+ (Nativo, sin frameworks pesados).
* **Base de Datos:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3 (Diseño Responsivo), JavaScript (ES6 Modules).
* **Librerías:**
    * `Chart.js` para visualización de reportes financieros.
    * `FontAwesome` para interfaz gráfica.
* **Servidor Web:** Apache (XAMPP/Laragon).

---

##  Módulos del Sistema

### 1.  Módulo de Cliente (E-commerce)
* **Catálogo Interactivo:** Filtrado de productos por categorías (Pollos, Parrillas, etc.) y buscador en tiempo real.
* **Carrito de Compras:** Persistencia de datos local (`localStorage`) antes de procesar el pedido.
* **Checkout:** Registro de dirección, teléfono y método de pago (Yape/Plin, Efectivo, Tarjeta).
* **Historial:** Visualización del estado del pedido (Pendiente -> En Camino -> Entregado).

### 2.  Módulo Administrativo (Dashboard)
* **KPIs en Tiempo Real:** Monitor de ventas diarias, pedidos pendientes y "Plato Estrella".
* **Gestión de Carta:** CRUD completo de productos con subida de imágenes al servidor.
* **Logística:** Asignación manual de repartidores a pedidos específicos.
* **Reportes:** Exportación de data histórica de ventas a formato CSV para Excel.

### 3.  Módulo de Repartidor
* **Gestión de Ruta:** Lista priorizada de entregas pendientes.
* **Actualización de Estado:** Botones de acción rápida ("En Puerta", "Entregado") que notifican al sistema central.
* **Geolocalización:** Integración directa con Google Maps/Waze mediante las coordenadas de la dirección.

---

##  Guía de Instalación Local

### Prerrequisitos
* Tener instalado **XAMPP**, **WAMP** o **Laragon**.
* Navegador web moderno (Chrome/Edge/Firefox).

### Pasos
1.  **Clonar el proyecto** en tu carpeta pública (`htdocs` o `www`):
    ```bash
    cd C:/xampp/htdocs
    git clone [https://github.com/tu-usuario/polleria-web.git](https://github.com/tu-usuario/polleria-web.git)
    ```

2.  **Configurar Base de Datos**:
    * Abre tu gestor SQL (ej. phpMyAdmin).
    * Crea una BD llamada `cisne_dorado_delivery`.
    * Importa el archivo script ubicado en `polleria/db.sql`.

3.  **Verificar Conexión**:
    * Revisa el archivo `polleria/config/db.php`. Asegúrate de que las credenciales coincidan con tu servidor local:
    ```php
    private $host = "localhost";
    private $user = "root";
    private $pass = ""; 
    ```

4.  **Despliegue**:
    * Accede desde tu navegador a: `http://localhost/polleria/`

---

##  Credenciales de Acceso (Demo)

El sistema viene precargado con usuarios para probar cada rol:

| Rol | Email | Contraseña | Funcionalidad Principal |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@gmail.com` | `123456` | Control total, reportes y gestión de carta. |
| **Repartidor** | `motorizado@delivery.com` | `123456` | Ver entregas y cambiar estados. |
| **Cliente** | `cliente@gmail.com` | `123456` | Realizar pedidos y ver historial. |

---

##  Estructura del Directorio

```bash
polleria/
├── api/                # Endpoints JSON consumidos por el Frontend
│   ├── auth/           # Login.php, Logout.php, Registro.php
│   ├── pedidos/        # Endpoints para crear y listar pedidos
│   └── productos/      # Endpoints para el CRUD de productos
├── config/             # Configuración de conexión a BD
├── models/             # Clases PHP (Usuario.php, Pedido.php)
├── pages/              # Vistas HTML/PHP renderizadas
│   ├── admin/          # Fragmentos del panel administrativo
│   └── ...
├── public/             # Recursos estáticos
│   ├── css/            # Hoja de estilos principal y admin
│   ├── img/            # Imágenes de productos subidas
│   └── js/             # Lógica del cliente (Fetch API, DOM)
└── index.php           # Router básico de la aplicación
