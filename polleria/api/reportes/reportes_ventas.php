<?php
// polleria/api/reportes/reportes_ventas.php
header('Content-Type: application/json');
// Nota: Ajusta esta ruta si es necesario.
require_once '../../config/db.php'; 

$rango = $_GET['rango'] ?? 'mes';
$periodo = $_GET['periodo'] ?? date('Y-m');

$db = (new Database())->connect();
$sql = "";
$parametros = [];

try {
    switch ($rango) {
        case 'dia':
            // Reporte por hora para un día específico
            $fecha_inicio = $periodo; 
            $sql = "SELECT 
                        DATE_FORMAT(fecha_hora, '%H:00') AS etiqueta, 
                        SUM(total) AS total_ventas,
                        COUNT(id_pedido) AS total_pedidos
                    FROM pedidos 
                    WHERE DATE(fecha_hora) = ? AND estado = 'Entregado'
                    GROUP BY etiqueta
                    ORDER BY etiqueta ASC";
            $parametros = [$fecha_inicio];
            break;

        case 'semana':
            // Calculamos el inicio de la semana (Lunes) a partir de la fecha dada
            $timestamp = strtotime($periodo);
            $diaSemana = date('w', $timestamp); // 0 (Domingo) a 6 (Sábado)
            $inicioSemana = date('Y-m-d', strtotime('-' . (($diaSemana == 0 ? 7 : $diaSemana) - 1) . ' days', $timestamp));
            $finSemana = date('Y-m-d', strtotime('+6 days', strtotime($inicioSemana)));

            $sql = "SELECT 
                        (CASE DAYOFWEEK(fecha_hora) 
                            WHEN 2 THEN 'Lunes'
                            WHEN 3 THEN 'Martes'
                            WHEN 4 THEN 'Miércoles'
                            WHEN 5 THEN 'Jueves'
                            WHEN 6 THEN 'Viernes'
                            WHEN 7 THEN 'Sábado'
                            WHEN 1 THEN 'Domingo'
                        END) AS etiqueta, 
                        SUM(total) AS total_ventas,
                        COUNT(id_pedido) AS total_pedidos
                    FROM pedidos 
                    WHERE DATE(fecha_hora) BETWEEN ? AND ? AND estado = 'Entregado'
                    -- Se agrupa por la etiqueta (CASE) y el número del día para el orden.
                    GROUP BY etiqueta, DAYOFWEEK(fecha_hora)
                    ORDER BY DAYOFWEEK(fecha_hora) ASC"; 

            $parametros = [$inicioSemana, $finSemana];
            break;

        case 'mes':
            // Reporte por día del mes
            $mes_anio = $periodo; // Formato YYYY-MM
            $sql = "SELECT 
                        DAY(fecha_hora) AS etiqueta, 
                        SUM(total) AS total_ventas,
                        COUNT(id_pedido) AS total_pedidos
                    FROM pedidos 
                    WHERE DATE_FORMAT(fecha_hora, '%Y-%m') = ? AND estado = 'Entregado'
                    GROUP BY etiqueta
                    ORDER BY etiqueta ASC";
            $parametros = [$mes_anio];
            break;

        case 'anio':
            // Reporte por mes del año
            $anio = $periodo; // Formato YYYY
            $sql = "SELECT 
                        MONTHNAME(fecha_hora) AS etiqueta, 
                        SUM(total) AS total_ventas,
                        COUNT(id_pedido) AS total_pedidos
                    FROM pedidos 
                    WHERE YEAR(fecha_hora) = ? AND estado = 'Entregado'
                    -- Se agrupa por el nombre del mes y el número del mes para el orden.
                    GROUP BY MONTHNAME(fecha_hora), MONTH(fecha_hora)
                    ORDER BY MONTH(fecha_hora) ASC"; 
            $parametros = [$anio];
            break;
            
        default:
            echo json_encode(['ok' => false, 'error' => 'Rango no válido']);
            exit;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($parametros);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TRADUCCIÓN: Esto es necesario si tu servidor MySQL devuelve los nombres de los meses en inglés.
    if ($rango === 'anio' && !empty($datos)) {
         $traducciones = [
             'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
             'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
             'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
             'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
         ];
         $datos = array_map(function($item) use ($traducciones) {
             $item['etiqueta'] = $traducciones[$item['etiqueta']] ?? $item['etiqueta'];
             return $item;
         }, $datos);
    }

    echo json_encode(['ok' => true, 'datos' => $datos]);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}

?>