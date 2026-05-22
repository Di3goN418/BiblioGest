<?php
// calcular_multas.php
// Se llama desde dashboard.php que ya incluyó conexion.php
// Recorre préstamos activos vencidos y crea/actualiza multas

$qp = $conexion->query("SELECT * FROM prestamos WHERE estado='Activo'");

if ($qp) {
    while ($p = $qp->fetch_assoc()) {
        $hoy = date("Y-m-d");
        if ($hoy <= $p['fecha_devolucion']) continue;

        $dias  = (int)((strtotime($hoy) - strtotime($p['fecha_devolucion'])) / 86400);
        $monto = $dias * 5;

        $check = $conexion->prepare("SELECT id FROM multas WHERE id_prestamo=?");
        $check->bind_param("i", $p['id']);
        $check->execute();
        $existe = $check->get_result();

        if ($existe->num_rows === 0) {
            // Crear multa nueva
            $ins = $conexion->prepare("INSERT INTO multas (id_prestamo, dias, monto, estado) VALUES (?, ?, ?, 'Pendiente')");
            $ins->bind_param("iid", $p['id'], $dias, $monto);
            $ins->execute();
        } else {
            // Actualizar días y monto si la multa sigue pendiente
            $multaId = $existe->fetch_assoc()['id'];
            $upd = $conexion->prepare("UPDATE multas SET dias=?, monto=? WHERE id=? AND estado='Pendiente'");
            $upd->bind_param("idi", $dias, $monto, $multaId);
            $upd->execute();
        }
    }
}
