<?php
require_once "../reportes/pdf/code128.php";
require_once "../modelos/Venta.php";

ob_start();
if (strlen(session_id()) < 1) session_start();

if (!isset($_SESSION['nombre'])) {
    echo "Debe ingresar al sistema correctamente para visualizar el reporte";
} else {
    if ($_SESSION['ventas'] == 1) {
        $venta = new Venta();
        $rspta = $venta->ventacabecera($_GET["id"]);
        $reg = $rspta->fetch_object();

        $empresa = "STARK METAL S.A.C.";
        $documento = "76754576";
        $direccion = "Av. Circunvalación Juliaca Perú";
        $telefono = "927564564";
        $email = "starkmetal@gmail.com";

        $pdf = new PDF_Code128('P', 'mm', array(80, 258));
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetFont('Arial', '', 10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, utf8_decode($empresa), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, utf8_decode($direccion), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode($telefono), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->Cell(0, utf8_decode("Fecha: " . $reg->fecha), 0, 1, 'L');
        $pdf->Cell(0, 5, utf8_decode("Cliente: " . $reg->cliente), 0, 1, 'L');
        $pdf->Cell(0, 5, utf8_decode($reg->tipo_documento . ": " . $reg->num_documento), 0, 1, 'L');
        $pdf->Cell(0, 5, utf8_decode("N° de venta: " . $reg->serie_comprobante . " - " . $reg->num_comprobante), 0, 1, 'L');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, utf8_decode("CANT."), 0, 0, 'L');
        $pdf->Cell(90, 5, utf8_decode("DESCRIPCION"), 0, 0, 'L');
        $pdf->Cell(30, 5, utf8_decode("IMPORTE"), 0, 1, 'R');
        $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
        $pdf->Ln(1);

        $pdf->SetFont('Arial', '', 10);
        $rsptad = $venta->ventadetalles($_GET["id"]);
        $cantidad = 0;

        while ($regd = $rsptad->fetch_object()) {
            $pdf->Cell(20, 5, utf8_decode($regd->cantidad), 0, 0, 'L');
            $pdf->Cell(90, 5, utf8_decode($regd->articulo), 0, 0, 'L');
            $pdf->Cell(30, 5, utf8_decode("S/. " . number_format($regd->subtotal, 2)), 0, 1, 'R');
            $cantidad += $regd->cantidad;
        }

        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(110, 5, utf8_decode("TOTAL: "), 0, 0, 'R');
        $pdf->Cell(30, 5, utf8_decode("S/. " . number_format($reg->total_venta, 2)), 0, 1, 'R');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, utf8_decode("N° de artículos: " . $cantidad), 0, 1, 'L');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 5, utf8_decode("¡Gracias por su compra!"), 0, 1, 'C');
        $pdf->Ln(5);

        $code = $reg->serie_comprobante . $reg->num_comprobante;

        $pdf->Code128(20, $pdf->GetY(), $code, 40, 10);
        $pdf->Ln(10);

        $pdf->SetX(5);
        $pdf->Cell(0, 10, strtoupper($code), 0, 1, 'C');

        $pdf->Output("I", "ticket.pdf");
    } else {
        echo "No tiene permiso para visualizar el reporte";
    }
}

ob_end_flush();
?>
