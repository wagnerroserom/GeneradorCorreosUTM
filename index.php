<?php
// index.php
// Interfaz principal: formulario de búsqueda y resultados

require_once 'conexion.php';
require_once 'funciones.php';

$resultados = [];
$busqueda_realizada = false;

if ($_POST) {
    $nombre_apellido = trim($_POST['nombre_apellido'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $facultad = trim($_POST['facultad'] ?? '');

    // Validar cédula si se ingresó
    if (!empty($cedula)) {
        $cedula = str_pad($cedula, 10, '0', STR_PAD_LEFT);
        if (strlen($cedula) !== 10 || !ctype_digit($cedula)) {
            $error = "La cédula debe tener 10 dígitos.";
        } elseif (!validarCedula($cedula)) {
            $error = "La cédula ingresada no es válida.";
        }
    }

    if (!isset($error)) {
        $sql = "SELECT * FROM estudiantes WHERE 1=1";
        $params = [];

        if (!empty($nombre_apellido)) {
            $sql .= " AND nombre_completo LIKE ?";
            $params[] = '%' . $nombre_apellido . '%';
        }

        if (!empty($cedula)) {
            $sql .= " AND cedula = ?";
            $params[] = $cedula;
        }

        if (!empty($facultad) && $facultad !== 'todas') {
            $sql .= " AND facultad = ?";
            $params[] = $facultad;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $busqueda_realizada = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de Correos - UTM</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="contenedor">
        <h1>🔍 Buscar Estudiantes - Universidad Técnica de Manabí</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Nombre o Apellido:</label>
            <input type="text" name="nombre_apellido" value="<?= htmlspecialchars($_POST['nombre_apellido'] ?? '') ?>">

            <label>Cédula (10 dígitos):</label>
            <input type="text" name="cedula" value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>" placeholder="Ej: 1310904345 o 0106158397">

            <label>Facultad:</label>
            <select name="facultad">
                <option value="todas">Todas las facultades</option>
                <?php
                // Obtener lista de facultades únicas
                $stmt = $pdo->query("SELECT DISTINCT facultad FROM estudiantes ORDER BY facultad");
                while ($fila = $stmt->fetch()) {
                    $selected = (($_POST['facultad'] ?? '') === $fila['facultad']) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($fila['facultad']) . "\" $selected>" . htmlspecialchars($fila['facultad']) . "</option>";
                }
                ?>
            </select>

            <div class="botones">
                <button type="submit">🔍 Buscar</button>
                <a href="index.php" class="btn-limpiar">🗑️ Limpiar resultados</a>
            </div>
        </form>

        <?php if ($busqueda_realizada): ?>
            <h2>Resultados (<?= count($resultados) ?> encontrados)</h2>
            <?php if (empty($resultados)): ?>
                <p>No se encontraron estudiantes con esos criterios.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Correo Institucional</th>
                            <th>Facultad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $est): ?>
                            <tr>
                                <td><?= htmlspecialchars($est['nombre_completo']) ?></td>
                                <td><?= htmlspecialchars($est['cedula']) ?></td>
                                <td><?= htmlspecialchars($est['correo_institucional']) ?></td>
                                <td><?= htmlspecialchars($est['facultad']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>