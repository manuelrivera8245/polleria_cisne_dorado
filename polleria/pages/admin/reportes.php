<section id="tab-reportes" class="tab-content" style="display: none;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="color:#aaa; font-weight:normal; margin:0;">Reportes Financieros</h3>
    </div>

    <div style="background:#1e1e1e; padding:20px; border-radius:12px; border:1px solid #333; margin-bottom: 20px;">
        <h4 style="color:#F3C400; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px;">Filtro y Exportación</h4>
        
        <div style="display:flex; gap:15px; align-items:flex-end;">
            <div style="flex-grow:1;">
                <label style="color:#aaa; font-size:0.9rem; display:block; margin-bottom:5px;">Rango de Análisis</label>
                <select id="select-rango-reporte" class="input-dark" style="width:100%;">
                    <option value="dia">Ventas por Día</option>
                    <option value="semana">Ventas de la Semana</option>
                    <option value="mes" selected>Ventas por Mes</option>
                    <option value="anio">Ventas por Año</option>
                </select>
            </div>

            <div style="flex-grow:1;">
                <label style="color:#aaa; font-size:0.9rem; display:block; margin-bottom:5px;">Periodo Específico</label>
                <input type="date" id="input-periodo" class="input-dark" style="width:100%;">
            </div>
            
            <button class="btn-primary" onclick="obtenerReporteGrafico()" style="padding:10px 15px; background:#2196F3; border:none;">
                <i class="fa-solid fa-chart-line"></i> Generar Gráfico
            </button>

            <button class="btn-success" id="btn-exportar-csv" style="padding:10px 15px; background:#4CAF50; border:none;" disabled title="Genera el gráfico primero">
                <i class="fa-solid fa-file-csv"></i> Exportar CSV
            </button>
        </div>
    </div>
    
    <div style="background:#1e1e1e; padding:20px; border-radius:12px; border:1px solid #333; margin-bottom: 20px;">
        <h4 style="color:#F3C400; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px;">Gráfico de Ventas</h4>
        <div style="height: 350px;">
            <canvas id="ventasChart"></canvas>
        </div>
        <p id="msg-reporte" style="text-align:center; color:#666; margin-top:20px; display:none;">Selecciona un rango y periodo para generar el reporte.</p>
    </div>

    </section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chartInstance = null; // Para guardar la instancia del gráfico
let ultimoReporteData = null; // Para guardar los datos para exportar

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar el input de periodo con el mes/año actual
    const hoy = new Date();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const anio = hoy.getFullYear();
    document.getElementById('input-periodo').value = `${anio}-${mes}-01`;
    document.getElementById('input-periodo').setAttribute('type', 'month');

    // Escuchar cambios en el selector de rango
    document.getElementById('select-rango-reporte').addEventListener('change', (e) => {
        const rango = e.target.value;
        const inputPeriodo = document.getElementById('input-periodo');
        
        // Cambiar tipo de input según el rango
        if (rango === 'dia' || rango === 'semana') {
            inputPeriodo.setAttribute('type', 'date');
            inputPeriodo.valueAsDate = new Date();
        } else if (rango === 'mes') {
            inputPeriodo.setAttribute('type', 'month');
            inputPeriodo.value = `${anio}-${mes}`;
        } else if (rango === 'anio') {
            inputPeriodo.setAttribute('type', 'number');
            inputPeriodo.setAttribute('min', '2023');
            inputPeriodo.setAttribute('max', anio);
            inputPeriodo.value = anio;
        }
    });
});


// Función principal para obtener datos y dibujar el gráfico
async function obtenerReporteGrafico() {
    const rango = document.getElementById('select-rango-reporte').value;
    const periodo = document.getElementById('input-periodo').value;
    const msg = document.getElementById('msg-reporte');
    const btnExportar = document.getElementById('btn-exportar-csv');

    msg.style.display = 'block';
    msg.textContent = 'Generando reporte...';
    btnExportar.disabled = true;

    try {
        const url = `api/reportes/reportes_ventas.php?rango=${rango}&periodo=${periodo}`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.ok && data.datos.length > 0) {
            ultimoReporteData = data.datos;
            dibujarGrafico(data.datos, rango);
            msg.style.display = 'none';
            btnExportar.disabled = false;
        } else {
            // Destruir gráfico si no hay datos o hay error
            if(chartInstance) chartInstance.destroy();
            ultimoReporteData = null;
            msg.textContent = 'No se encontraron datos para el periodo seleccionado.';
            btnExportar.disabled = true;
        }

    } catch (e) {
        if(chartInstance) chartInstance.destroy();
        msg.textContent = 'Error al cargar los datos del reporte.';
        console.error("Error al obtener reporte:", e);
        btnExportar.disabled = true;
    }
}

// Función para dibujar el gráfico usando Chart.js
function dibujarGrafico(datos, rango) {
    if (chartInstance) {
        chartInstance.destroy(); // Destruir instancia anterior
    }

    const ctx = document.getElementById('ventasChart').getContext('2d');
    
    // Preparar datos para Chart.js
    const etiquetas = datos.map(item => item.etiqueta); // Días, semanas, meses, etc.
    const ventas = datos.map(item => parseFloat(item.total_ventas));
    const cantidad = datos.map(item => parseInt(item.total_pedidos));

    const tipoGrafico = rango === 'dia' || rango === 'semana' ? 'bar' : 'line';
    
    chartInstance = new Chart(ctx, {
        type: tipoGrafico,
        data: {
            labels: etiquetas,
            datasets: [
                {
                    label: 'Ventas (S/)',
                    data: ventas,
                    backgroundColor: 'rgba(243, 196, 0, 0.7)',
                    borderColor: '#F3C400',
                    yAxisID: 'y',
                    tension: 0.4,
                    type: 'line' // Siempre línea para ventas
                },
                {
                    label: 'Pedidos (#)',
                    data: cantidad,
                    backgroundColor: 'rgba(33, 150, 243, 0.5)',
                    borderColor: '#2196F3',
                    yAxisID: 'y1',
                    type: 'bar' // Siempre barra para pedidos
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Ventas (S/)' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#F3C400' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Pedidos (#)' },
                    grid: { drawOnChartArea: false }, 
                    ticks: { color: '#2196F3' }
                },
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: '#fff' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
}

// Función para Exportar CSV
document.getElementById('btn-exportar-csv').addEventListener('click', () => {
    if (!ultimoReporteData) return alert("Primero genera un reporte para poder exportar.");

    // --- CÓDIGO ACTUALIZADO PARA EXCEL ---
    
    // 1. Definir el Separador de Campos (usar ';' para compatibilidad con Excel regional)
    const separator = ';'; 
    
    let csvContent = "";
    
    // 2. Agregar el BOM (Byte Order Mark) para UTF-8: Esto fuerza a Excel a reconocer la codificación correctamente (¡ crucial para ñ, á, é, etc. !)
    csvContent += "\uFEFF"; 
    
    // 3. Encabezado con separador
    csvContent += "Periodo" + separator + "Total Ventas" + separator + "Total Pedidos\n";
    
    // 4. Contenido
    ultimoReporteData.forEach(row => {
        // Aseguramos que los valores sean strings y reemplazamos comas por puntos en los decimales (Excel)
        const ventas = parseFloat(row.total_ventas).toFixed(2).replace('.', ','); 
        const pedidos = row.total_pedidos;
        const etiqueta = row.etiqueta;

        csvContent += `${etiqueta}${separator}${ventas}${separator}${pedidos}\n`;
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    
    // Crear un blob para asegurar la descarga del BOM
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    link.setAttribute("href", url);
    link.setAttribute("download", "Reporte_Ventas_CisneDorado.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url); // Liberar el objeto URL
});
</script>