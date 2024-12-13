<?php
// Activamos almacenamiento en el buffer
ob_start();
session_start();

// Comprobamos si el usuario está logueado
if (!isset($_SESSION['nombre'])) {
    echo "Debe ingresar al sistema correctamente para visualizar el reporte.";
    exit;
}

// Comprobamos si el usuario tiene permisos de ventas
if ($_SESSION['ventas'] != 1) {
    echo "No tiene permiso para visualizar el reporte.";
    exit;
}

// Incluimos el archivo factura y la clase Venta
require('Factura.php');
require_once "../modelos/Venta.php";

// Establecemos los datos de la empresa
$logo = "tarea.png";
$ext_logo = "png";
$empresa = "StarkMetal S.A.C.";
$documento = "76754576";
$direccion = "Av. Circunvalación Juliaca - Perú";
$telefono = "927564564";
$email = "starkmetal@gmail.com";

// Obtenemos los datos de la cabecera de la venta actual
$venta = new Venta();
$rsptav = $venta->ventacabecera($_GET["id"]);

if ($rsptav->num_rows == 0) {
    echo "No se encontraron los detalles de la venta.";
    exit;
}

// Recibimos los datos de la venta
$regv = $rsptav->fetch_object();

// Configuración de la factura
$pdf = new PDF_Invoice('p', 'mm', 'A4');
$pdf->AddPage();

// Enviamos datos de la empresa al método addSociete de la clase factura
$pdf->addSociete(utf8_decode($empresa),
    $documento . "\n" .
    utf8_decode("Dirección: ") . utf8_decode($direccion) . "\n" .
    utf8_decode("Teléfono: ") . $telefono . "\n" .
    "Email: " . $email, $logo, $ext_logo);

// Datos de la venta
$pdf->fact_dev("$regv->tipo_comprobante", "$regv->serie_comprobante- $regv->num_comprobante");
$pdf->temporaire("");
$pdf->addDate($regv->fecha);

// Enviamos los datos del cliente
$pdf->addClientAdresse(
    utf8_decode($regv->cliente),
    "Domicilio: " . utf8_decode($regv->direccion),
    $regv->tipo_documento . ": " . $regv->num_documento,
    "Email: " . $regv->email,
    utf8_decode("Teléfono: "). $regv->telefono
);

// Establecemos las columnas de la sección de detalles de la venta
$cols = array(
    "CODIGO" => 23,
    "DESCRIPCION" => 78,
    "CANTIDAD" => 22,
    "P.U." => 25,
    "DSCTO" => 20,
    "SUBTOTAL" => 22
);
$pdf->addCols($cols);

$cols = array(
    "CODIGO" => "L",
    "DESCRIPCION" => "L",
    "CANTIDAD" => "C",
    "P.U." => "R",
    "DSCTO" => "R",
    "SUBTOTAL" => "C"
);
$pdf->addLineFormat($cols);

// Actualizamos el valor de la coordenada "y" desde donde empezamos a mostrar los datos
$y = 85;

// Obtenemos los detalles de la venta
$rsptad = $venta->ventadetalles($_GET["id"]);
if ($rsptad->num_rows == 0) {
    echo "No se encontraron los detalles de los productos de la venta.";
    exit;
}

while ($regd = $rsptad->fetch_object()) {
    $line = array(
        "CODIGO" => "$regd->codigo",
        "DESCRIPCION" => utf8_decode("$regd->articulo"),
        "CANTIDAD" => "$regd->cantidad",
        "P.U." => "$regd->precio_venta",
        "DSCTO" => "$regd->descuento",
        "SUBTOTAL" => "$regd->subtotal"
    );
    $size = $pdf->addLine($y, $line);
    $y += $size + 2;
}

// Generación de monto en letras
require_once "Letras.php";
$V = new Letras();
$total = $regv->total_venta;
$V->substituir_un_mil_por_mil = true;

$con_letra = strtoupper($V->ValorEnLetras($total, "soles"));
$pdf->addCadreTVAs($con_letra);

// Mostramos el impuesto
$pdf->addTVAs($regv->impuesto, $regv->total_venta, "S/ ");
$pdf->addCadreEurosFrancs("IGV" . " $regv->impuesto %");

// Generamos el archivo PDF
$pdf->Output('Reporte de Venta', 'I');

// Finalizamos el buffer
ob_end_flush();
?>
