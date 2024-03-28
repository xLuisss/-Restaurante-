<?php
session_start();

$precios_productos = array(
    'tacos' => 3000,
    'bebidas' => 5000
);

function calcular_valor_pedido($pedido) {
    global $precios_productos;
    $total = 0;
    foreach ($pedido['productos'] as $producto => $cantidad) {
        $total += $precios_productos[$producto] * $cantidad;
    }
    return $total;
}

function generar_numero_pedido() {
    return uniqid();
}

function eliminar_pedido($numero_pedido) {
    if(isset($_SESSION['usuarios'][$numero_pedido])){
        unset($_SESSION['usuarios'][$numero_pedido]);
        return true;
    } else {
        return false;
    }
}

if(isset($_POST['upd'])){
    $_SESSION['usuarios'][$_POST['key']] = array(
        'cedula' => $_POST['cedula'],
        'tacos' => $_POST['tacos'],
        'bebidas' => $_POST['bebidas']
    ); 
}

if(isset($_POST['del'])){
    unset($_SESSION['usuarios'][$_POST['key']]);
}

if(isset($_POST['add'])){

    $numero_pedido = generar_numero_pedido();
    $productos_pedido = array();
    
    if ($_POST['tacos'] > 0) {
        $productos_pedido['tacos'] = $_POST['tacos'];
    }
    if ($_POST['bebidas'] > 0) {
        $productos_pedido['bebidas'] = $_POST['bebidas'];
    }
    
    if ($_FILES['imagen_cliente']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = $_FILES['imagen_cliente']['name'];
        move_uploaded_file($_FILES['imagen_cliente']['tmp_name'], 'img/' . $nombre_imagen);
    } else {
        $nombre_imagen = null;
    }
    
    $_SESSION['usuarios'][$numero_pedido] = array(
        'numero_pedido' => $numero_pedido,
        'cedula' => $_POST['cedula'],
        'productos' => $productos_pedido,
        'imagen_cliente' => $nombre_imagen
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Cañagüatero - Pedidos</title>
    <style>
        img {
            width: 5cm;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>El Cañagüatero - Pedidos</h1>
    <div>
        <h3>Crear Pedido</h3>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="text" name="cedula" placeholder="Cedula"><br><br>
            <label for="tacos">Cantidad de Tacos:</label><br>
            <input type="number" id="tacos" name="tacos" value="0"><br><br>
            <label for="bebidas">Cantidad de Bebidas:</label><br>
            <input type="number" id="bebidas" name="bebidas" value="0"><br><br>
            <input type="file" name="imagen_cliente" accept="image/*"><br><br>
            <button name="add">Realizar pedido</button>
        </form>
    </div>

    <div>
        <h3>Eliminar Pedido</h3>
        <form method="post" action="">
            <input type="text" name="key" placeholder="Número de Pedido a Eliminar">
            <button name="del">Eliminar</button>
        </form>
    </div>

    <?php
    if (!empty($_SESSION['usuarios'])) {
        echo "<h2>Listado de Pedidos del Mismo Cliente</h2>";
        $cedula_cliente = reset($_SESSION['usuarios'])['cedula'];
        listar_pedidos_cliente($cedula_cliente);
    }
    ?>

    <?php
    if (!empty($_SESSION['usuarios'])) {
        echo "<h2>Listado de Pedidos Ordenados por Valor Final</h2>";
        listar_pedidos_ordenados();
    }
    ?>
</body>
</html>

<?php
function listar_pedidos_cliente($cedula) {
    $total_pedidos_cliente = 0;
    echo "<table border='1'>";
    echo "<tr><th>Número de Pedido</th><th>Cédula del Cliente</th><th>Productos</th><th>Valor Total</th><th>Imagen del Cliente</th></tr>";
    foreach ($_SESSION['usuarios'] as $pedido) {
        if ($pedido['cedula'] === $cedula) {
            echo "<tr>";
            echo "<td>" . $pedido['numero_pedido'] . "</td>";
            echo "<td>" . $pedido['cedula'] . "</td>";
            echo "<td>";
            foreach ($pedido['productos'] as $producto => $cantidad) {
                echo "$cantidad $producto, ";
            }
            echo "</td>";
            echo "<td>$" . calcular_valor_pedido($pedido) . "</td>";
            echo "<td>";
            if ($pedido['imagen_cliente']) {
                echo "<img src='img/" . $pedido['imagen_cliente'] . "' alt='Imagen del Cliente' width='200'>";
            } else {
                echo "No disponible";
            }
            echo "</td>";
            echo "</tr>";
            $total_pedidos_cliente += calcular_valor_pedido($pedido);
        }
    }
    echo "</table>";
    echo "<p>Total de Pedidos del Cliente: $" . $total_pedidos_cliente . "</p>";
}

function listar_pedidos_ordenados() {
    if (!empty($_SESSION['usuarios'])) {
        $pedidos_ordenados = $_SESSION['usuarios'];
        usort($pedidos_ordenados, function($a, $b) {
            return calcular_valor_pedido($b) - calcular_valor_pedido($a);
        });

        echo "<table border='1'>";
        echo "<tr><th>Número de Pedido</th><th>Cédula del Cliente</th><th>Productos</th><th>Valor Total</th><th>Imagen del Cliente</th></tr>";
        foreach ($pedidos_ordenados as $pedido) {
            echo "<tr>";
            echo "<td>" . $pedido['numero_pedido'] . "</td>";
            echo "<td>" . $pedido['cedula'] . "</td>";
            echo "<td>";
            foreach ($pedido['productos'] as $producto => $cantidad) {
                echo "$cantidad $producto, ";
            }
            echo "</td>";
            echo "<td>$" . calcular_valor_pedido($pedido) . "</td>";
            echo "<td>";
            if ($pedido['imagen_cliente']) {
                echo "<img src='img/" . $pedido['imagen_cliente'] . "' alt='Imagen del Cliente' width='200'>";
            } else {
                echo "No disponible";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>
