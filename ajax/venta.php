<?php 
require_once "../modelos/Venta.php";
if (strlen(session_id())<1) 
	session_start();

$venta = new Venta();

$idventa=isset($_POST["idventa"])? limpiarCadena($_POST["idventa"]):""; 
$idcliente=isset($_POST["idcliente"])? limpiarCadena($_POST["idcliente"]):"";
$idusuario=$_SESSION["idusuario"];
$tipo_comprobante=isset($_POST["tipo_comprobante"])? limpiarCadena($_POST["tipo_comprobante"]):"";
$serie_comprobante=isset($_POST["serie_comprobante"])? limpiarCadena($_POST["serie_comprobante"]):"";
$num_comprobante=isset($_POST["num_comprobante"])? limpiarCadena($_POST["num_comprobante"]):"";
$n_operacion=isset($_POST["n_operacion"])? limpiarCadena($_POST["n_operacion"]):"";
$tipo_pago=isset($_POST["tipo_pago"])? limpiarCadena($_POST["tipo_pago"]):"";
$fecha_hora=isset($_POST["fecha_hora"])? limpiarCadena($_POST["fecha_hora"]):"";
$impuesto=isset($_POST["impuesto"])? limpiarCadena($_POST["impuesto"]):"";
$total_venta=isset($_POST["total_venta"])? limpiarCadena($_POST["total_venta"]):"";
 
switch ($_GET["op"]) {
	case 'guardaryeditar':
	if (empty($idventa)) {
		$rspta=$venta->insertar($idcliente,$idusuario,$tipo_comprobante,$serie_comprobante,$num_comprobante, $n_operacion, $tipo_pago,$fecha_hora,$impuesto,$total_venta,$_POST["idarticulo"],$_POST["cantidad"],$_POST["precio_venta"],$_POST["descuento"]); 
		echo $rspta ? "Datos registrados correctamente" : "No se pudo registrar los datos";
	}else{
        
	}
		break; 
	case 'anular':
		$rspta=$venta->anular($idventa);
		echo $rspta ? "Ingreso anulado correctamente" : "No se pudo anular el ingreso";
		break;
	
	case 'mostrar':
		$rspta=$venta->mostrar($idventa);
		echo json_encode($rspta);
		break;

	case 'listarDetalle':
		//recibimos el idventa
		$id=$_GET['id']; 
		$rspta=$venta->listarDetalle($id);
		$total=0; $subtotal=0; $igv=0;
		echo ' <thead style="background-color:#A9D0F5">
        <th>Opciones</th>
        <th>Articulo</th>
        <th>Cantidad</th>
        <th>Precio Venta</th>
        <th>Descuento</th>
        <th>Subtotal</th>
       </thead>';
	   echo '<tbody>';
		while ($reg=$rspta->fetch_object()) {
			echo '<tr class="filas">
			<td></td>
			<td>'.$reg->nombre.'</td>
			<td>'.$reg->cantidad.'</td>
			<td>'.$reg->precio_venta.'</td>
			<td>'.$reg->descuento.'</td>
			<td>'.$reg->subtotal.'</td></tr>';
			$total=$total+($reg->precio_venta*$reg->cantidad-$reg->descuento);
		} 
		echo '</tbody>';
		echo '<tfoot>
		<th>
		<ul style="list-style:none">
			<li>Sub Total</li>
			<li id="inpuesto_name2">Impuesto</li>
			<li>TOTAL</li>
		</ul>
		</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th> 
		<ul style="list-style:none"> 
			<li id="_subtotal2">S/. 0.00</li>
			<li id="_impuesto2">S/. 0.00</li>
			<li id="total2">S/. 0.00</li>
		</ul> 
		 <input type="hidden" name="total_venta" id="total_venta"></th>
       </tfoot>';
		break;

    case 'listar':
		$rspta=$venta->listar();
		$data=Array(); 
		while ($reg=$rspta->fetch_object()) {
                 if ($reg->tipo_comprobante=='Ticket') {
                 	$url='../reportes/exTicket.php?id=';
                 }else{
                    $url='../reportes/exFactura.php?id=';
                 } 
			$data[]=array(
            "0"=>(($reg->estado=='Aceptado')?'<button class="btn btn-warning btn-xs" onclick="mostrar('.$reg->idventa. ')"><i class="fa fa-eye"></i></button>'.' '.'<button class="btn btn-danger btn-xs" onclick="anular('.$reg->idventa.')"><i class="fa fa-close"></i></button>':'<button class="btn btn-warning btn-xs" onclick="mostrar('.$reg->idventa.')"><i class="fa fa-eye"></i></button>').
            '<a target="_blank" href="'.$url.$reg->idventa.'"> <button class="btn btn-info btn-xs"><i class="fa fa-file"></i></button></a>',
            "1"=>$reg->fecha,
            "2"=>$reg->cliente,
            "3"=>$reg->usuario,
            "4"=>$reg->tipo_comprobante,
            "5"=>$reg->serie_comprobante. '-' .$reg->num_comprobante,
            "6"=>$reg->total_venta,
            "7"=>($reg->estado=='Aceptado')?'<span class="label bg-green">Aceptado</span>':'<span class="label bg-red">Anulado</span>'
              );
		}
		$results=array(
             "sEcho"=>1,//info para datatables
             "iTotalRecords"=>count($data),//enviamos el total de registros al datatable
             "iTotalDisplayRecords"=>count($data),//enviamos el total de registros a visualizar
             "aaData"=>$data); 
		echo json_encode($results);
		break;

		case 'selectCliente':
			require_once "../modelos/Persona.php";
			$persona = new Persona(); 
			$rspta = $persona->listarc(); 
			while ($reg = $rspta->fetch_object()) {
				echo '<option value='.$reg->idpersona.'>'.$reg->nombre.'</option>';
			}
			break; 
			case 'listarArticulos':
			require_once "../modelos/Articulo.php";
			$articulo=new Articulo(); 
			$rspta=$articulo->listarActivosVenta();
			$data=Array();
			while ($reg=$rspta->fetch_object()) {
				$data[]=array(
				"0"=>($reg->stock>0)? '<button id="btnadd'.$reg->idarticulo.'" class="btn btn-success" onclick="agregarDetalle('.$reg->idarticulo.',\''.$reg->nombre. '\',' . $reg->stock . ','.$reg->precio_venta.')"><span class="fa fa-plus"></span></button>': '<button class="btn btn-success" disabled onclick="agregarDetalle(' . $reg->idarticulo . ',\'' . $reg->nombre . '\',' . $reg->precio_venta . ')"><span class="fa fa-plus"></span></button>',
				"1"=>$reg->nombre,
				"2"=>$reg->categoria,
				"3"=>$reg->codigo,
				"4"=>($reg->stock > 0) ? '<span>'.$reg->stock.'</span>':'<span class="text-danger">'.$reg->stock.'</span>',
				"5"=>$reg->precio_venta,
				"6"=>"<img src='../files/articulos/".$reg->imagen."' height='50px' width='50px'>" 
				);
			}
			$results=array(
				"sEcho"=>1,//info para datatables
				"iTotalRecords"=>count($data),//enviamos el total de registros al datatable
				"iTotalDisplayRecords"=>count($data),//enviamos el total de registros a visualizar
				"aaData"=>$data); 
			echo json_encode($results); 
				break;
}
 ?>