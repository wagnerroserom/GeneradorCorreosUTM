<?php
// funciones.php
// Contiene funciones reutilizables: validación de cédula, generación de correo, etc.

/**
 * Valida si una cédula ecuatoriana es real (algoritmo del Registro Civil)
 * @param string $cedula Cadena de 10 dígitos
 * @return bool
 */
function validarCedula($cedula) {
    // Aseguramos que tenga 10 dígitos (incluye ceros iniciales)
    if (strlen($cedula) != 10 || !ctype_digit($cedula)) {
        return false;
    }

    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $digito_verificador = (int)$cedula[9];
    $suma = 0;

    for ($i = 0; $i < 9; $i++) {
        $producto = (int)$cedula[$i] * $coeficientes[$i];
        if ($producto > 9) {
            $producto -= 9;
        }
        $suma += $producto;
    }

    $digito_calculado = ($suma % 10 == 0) ? 0 : 10 - ($suma % 10);
    return $digito_verificador == $digito_calculado;
}

/**
 * Elimina tildes y caracteres especiales de un texto
 * @param string $texto
 * @return string
 */
function quitarTildes($texto) {
    $texto = str_replace(
        ['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'],
        ['a','e','i','o','u','n','A','E','I','O','U','N'],
        $texto
    );
    return preg_replace('/[^a-zA-Z0-9]/', '', $texto); // Solo letras y números
}

/**
 * Genera el correo institucional según el formato:
 * inicial_del_primer_nombre + primer_apellido + últimos_4_cédula @utm.edu.ec
 * 
 * Nota: El nombre viene como "APELLIDOS NOMBRES"
 * 
 * @param string $nombreCompleto
 * @param string $cedula (10 dígitos)
 * @return string|null
 */
function generarCorreoInstitucional($nombreCompleto, $cedula) {
    $nombreCompleto = trim($nombreCompleto);
    if (empty($nombreCompleto) || strlen($cedula) != 10) {
        return null;
    }

    $partes = explode(' ', $nombreCompleto);
    if (count($partes) < 2) {
        return null;
    }

    // El primer apellido es la primera palabra
    $primerApellido = quitarTildes(strtolower($partes[0]));

    // Los nombres están al final; tomamos el primer nombre real
    // Ej: "ACEBO ZAMBRANO CHRISTIAN ENRIQUE" → nombres = ["CHRISTIAN", "ENRIQUE"]
    // Pero si hay solo 2 partes: ["PÉREZ", "MARÍA"] → nombre = "MARÍA"
    $nombres = [];
    for ($i = count($partes) - 1; $i >= 0; $i--) {
        // Si la palabra tiene más de 2 letras, asumimos que es nombre (no inicial)
        if (strlen($partes[$i]) > 2) {
            $nombres[] = $partes[$i];
        }
    }
    $nombres = array_reverse($nombres); // Restauramos orden

    if (empty($nombres)) {
        // Caso fallback: tomamos la última palabra
        $primerNombre = end($partes);
    } else {
        $primerNombre = $nombres[0];
    }

    $inicial = strtolower(substr($primerNombre, 0, 1));
    $ultimos4 = substr($cedula, -4);

    return $inicial . $primerApellido . $ultimos4 . '@utm.edu.ec';
}
?>