<?php
// verificar_tabla.php
include 'conexion.php';

echo "<h2>Estructura de la tabla 'usuario':</h2>";

// Mostrar estructura de la tabla
$result = $conexion->query("DESCRIBE usuario");
if ($result) {
    echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Valor por defecto</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar datos de ejemplo
    echo "<h3>Usuarios en la tabla:</h3>";
    $result = $conexion->query("SELECT * FROM usuario");
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr>";
        // Encabezados de columnas
        while ($field = $result->fetch_field()) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Datos
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay usuarios en la tabla.</p>";
    }
} else {
    echo "<p>Error: La tabla 'usuario' no existe o no se puede acceder a ella.</p>";
}

echo '<p><a href="login.php">Volver al login</a></p>';

$conexion->close();
?>