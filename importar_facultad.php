<?php
// importar_facultad.php
// IMPORTANTE: Este script se ejecuta una vez para cargar los datos.
// Luego, se elimina o se desactiva por seguridad.

require_once 'conexion.php';
require_once 'funciones.php';

// Incluir PhpSpreadsheet
require_once 'lib/PhpSpreadsheet/src/PhpSpreadsheet/IOFactory.php';

// CONFIGURACIÓN: cambia estos valores por cada facultad
$archivo_excel = 'F. ACUICULTURA Y CIENCIAS DEL MAR.xlsx';
$nombre_facultad = 'Acuicultura y Ciencias del Mar';

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo_excel);
    $hoja = $spreadsheet->getActiveSheet();
    $filas = $hoja->toArray();

    $contador_insertados = 0;

    // Recorremos desde la fila 6 (índice 5), porque antes están los encabezados
    for ($i = 5; $i < count($filas); $i++) {
        $fila = $filas[$i];
        if (empty($fila[1]) || empty($fila[2])) continue; // Saltar filas vacías

        $nombre_completo = trim($fila[1]); // Columna B: APELLIDOS NOMBRES
        $cedula_raw = trim($fila[2]);      // Columna C: Cédula

        // Asegurar que la cédula sea de 10 dígitos (incluye ceros iniciales)
        $cedula = str_pad($cedula_raw, 10, '0', STR_PAD_LEFT);
        if (strlen($cedula) !== 10 || !ctype_digit($cedula)) {
            echo "Cédula inválida en fila " . ($i + 1) . ": $cedula_raw<br>";
            continue;
        }

        // Validar cédula real
        if (!validarCedula($cedula)) {
            echo "Cédula no válida en fila " . ($i + 1) . ": $cedula<br>";
            continue;
        }

        // Generar correo
        $correo = generarCorreoInstitucional($nombre_completo, $cedula);
        if (!$correo) {
            echo "Error al generar correo para: $nombre_completo<br>";
            continue;
        }

        // Insertar en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO estudiantes (nombre_completo, cedula, facultad, correo_institucional)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo)
        ");
        $stmt->execute([$nombre_completo, $cedula, $nombre_facultad, $correo]);
        $contador_insertados++;
    }

    echo "<h3>¡Importación completada!</h3>";
    echo "<p>Se insertaron o actualizaron $contador_insertados registros de la facultad: $nombre_facultad</p>";

} catch (Exception $e) {
    die("Error al procesar el archivo: " . $e->getMessage());
}
?>