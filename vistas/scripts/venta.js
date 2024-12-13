var tabla;

//funcion que se ejecuta al inicio
function init(){
   mostrarform(false);
   listar();
	$('#operaciones').hide();
   $("#formulario").on("submit",function(e){
   	guardaryeditar(e);
   });

   //cargamos los items al select cliente
   $.post("../ajax/venta.php?op=selectCliente", function(r){
   	$("#idcliente").html(r);
   	$('#idcliente').selectpicker('refresh');
   });
}

//funcion limpiar
function limpiar(){ 
	$("#idcliente").val("");
	$("#cliente").val("");
	$("#serie_comprobante").val("");
	$("#num_comprobante").val("");
	$("#impuesto").val(18);
	$("#total_venta").val("");
	$(".filas").remove();
	$("#_subtotal").html('S/. 0.00');
	$("#_impuesto").html('S/. 0.00 ');
	$("#total").html("S/. 0.00");

	//obtenemos la fecha actual
	var now = new Date();
	var day =("0"+now.getDate()).slice(-2);
	var month=("0"+(now.getMonth()+1)).slice(-2);
	var today=now.getFullYear()+"-"+(month)+"-"+(day);
	$("#fecha_hora").val(today);

	//marcamos el primer tipo_documento
	$("#tipo_comprobante").val("Boleta");
	$("#tipo_comprobante").selectpicker('refresh');
	$("#tipo_pago").val("Efectivo");
	$("#tipo_pago").selectpicker('refresh');
	$('#operaciones').hide();

}

//funcion mostrar formulario
function mostrarform(flag){
	limpiar();
	if(flag){
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		//$("#btnGuardar").prop("disabled",false);
		$("#btnagregar").hide();
		listarArticulos();

		$("#btnGuardar").hide();
		$("#btnCancelar").show();
		detalles=0;
		$("#btnAgregarArt").show(); 
	}else{
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

//cancelar form
function cancelarform(){
	limpiar();
	mostrarform(false);
	location.reload();
}

//funcion listar
function listar(){
	tabla=$('#tbllistado').dataTable({
		"aProcessing": true,//activamos el procedimiento del datatable
		"aServerSide": true,//paginacion y filrado realizados por el server
		dom: 'Bfrtip',//definimos los elementos del control de la tabla
		buttons: [
                  'copyHtml5',
                  'excelHtml5',
                  'csvHtml5',
                  'pdf'
		],
		"ajax":
		{
			url:'../ajax/venta.php?op=listar',
			type: "get",
			dataType : "json",
			error:function(e){
				console.log(e.responseText);
			}
		},
		"bDestroy":true,
		"iDisplayLength":5,//paginacion
		"order":[[0,"desc"]]//ordenar (columna, orden)
	}).DataTable();
}

function listarArticulos(){
	tabla=$('#tblarticulos').dataTable({
		"aProcessing": true,//activamos el procedimiento del datatable
		"aServerSide": true,//paginacion y filrado realizados por el server
		dom: 'Bfrtip',//definimos los elementos del control de la tabla
		buttons: [

		],
		"ajax":
		{
			url:'../ajax/venta.php?op=listarArticulos',
			type: "get",
			dataType : "json",
			error:function(e){
				console.log(e.responseText);
			}
		},
		"bDestroy":true,
		"iDisplayLength":5,//paginacion
		"order":[[0,"desc"]]//ordenar (columna, orden)
	}).DataTable();
}
//funcion para guardaryeditar
function guardaryeditar(e){
     e.preventDefault();//no se activara la accion predeterminada 
     //$("#btnGuardar").prop("disabled",true);
     var formData=new FormData($("#formulario")[0]); 
     $.ajax({
     	url: "../ajax/venta.php?op=guardaryeditar",
     	type: "POST",
     	data: formData,
     	contentType: false,
     	processData: false, 
     	success: function(datos){
     		bootbox.alert(datos);
     		mostrarform(false);
     		listar();
     	}
     });

     limpiar();
}
function detalle_list(id) {
	$.post("../ajax/venta.php?op=listarDetalle&id=" + id, function (r) {
		$("#detalles").html(r);
	});
}
 

function mostrar(idventa) {
	let igv = 0, subtotal=0; 
	$.post("../ajax/venta.php?op=mostrar", { idventa: idventa},
	function(data)
	{
		data=JSON.parse(data);
		mostrarform(true); 
		detalle_list(idventa);
		$("#idcliente").val(data.idcliente);
		$("#idcliente").selectpicker('refresh');
		$("#tipo_comprobante").val(data.tipo_comprobante);
		$("#tipo_comprobante").selectpicker('refresh');
		$("#serie_comprobante").val(data.serie_comprobante);
		$("#num_comprobante").val(data.num_comprobante);
		$("#fecha_hora").val(data.fecha);
		$("#impuesto").val(data.impuesto);
		$("#n_operacion").val(data.n_operacion);
		$("#tipo_pago").val(data.tipo_pago);
		$("#tipo_pago").selectpicker('refresh'); 
		$("#idventa").val(data.idventa); 
		//ocultar y mostrar los botones
		setTimeout(function () {  
			$("#inpuesto_name2").html('Impuesto(' + (data.impuesto) + '%)');
			subtotal = data.total_venta / (1 + data.impuesto / 100)
			igv = data.total_venta - subtotal;
			$("#_subtotal2").text('S/. ' + subtotal.toFixed(2));
			$("#_impuesto2").html('S/. ' + igv.toFixed(2));
			$("#total2").html('S/. ' + data.total_venta);
		},1000);
		$('#operaciones').show();
		$("#btnGuardar").hide();
		$("#btnCancelar").show();
		$("#btnAgregarArt").hide(); 
	}); 
}

//funcion para desactivar
function anular(idventa){
	bootbox.confirm("¿Esta seguro de desactivar este dato?", function(result){
		if (result) {
			$.post("../ajax/venta.php?op=anular", {idventa : idventa}, function(e){
				bootbox.alert(e);
				tabla.ajax.reload();
			});
		}
	})
}

//declaramos variables necesarias para trabajar con las compras y sus detalles
var impuesto=18;
var cont=0;
var detalles=0;

$("#btnGuardar").hide();
$("#tipo_comprobante").change(marcarImpuesto);

function marcarImpuesto(){
	var tipo_comprobante=$("#tipo_comprobante option:selected").text();
	if (tipo_comprobante=='Factura') {
		$("#impuesto").val(impuesto);
	}else{
		$("#impuesto").val("0");
	}
}

function agregarDetalle(idarticulo,articulo,stock,precio_venta){
	var cantidad = 1; 
	if (stock>0) { 
		var descuento=0; 
		if (idarticulo != "") {
			$(`#btnadd${idarticulo}`).attr('disabled', true);
			var subtotal=cantidad*precio_venta;
			var fila='<tr class="filas" id="fila'+cont+'">'+
				'<td><button type="button" class="btn btn-danger" onclick="eliminarDetalle(' + cont + ',' + idarticulo +')">X</button></td>'+
			'<td><input type="hidden" name="idarticulo[]" value="'+idarticulo+'">'+articulo+'</td>'+
			'<td><input type="hidden" name="stock[]" value="' + stock + '">' + stock +'</td>'+
			'<td><input type="number" name="cantidad[]" id="cantidad[]" value="'+cantidad+'"></td>'+
			'<td><input type="number" name="precio_venta[]" id="precio_venta[]" value="'+precio_venta+'"></td>'+
			'<td><input type="number" name="descuento[]" value="'+descuento+'"></td>'+ 
				'<td><span id="subtotal' + cont + '" name="subtotal">' + subtotal + '</span><input type="hidden" id="subar'+cont+'" name="subar[]" value="' + subtotal +'"></td>' +
			'<td><button type="button" onclick="modificarSubtotales(1)" class="btn btn-info"><i class="fa fa-refresh"></i></button></td>'+
			'</tr>';
			cont++;
			detalles++;
			$('#detalles').append(fila);
			modificarSubtotales(0);
	
		}else{
			alert("error al ingresar el detalle, revisar las datos del articulo ");
		}
	}else{alert("stock insuficiente")}
}
function calcularTotales() { 
	var subt = document.getElementsByName("subar[]");

	var total = 0;
	for (var i = 0; i < subt.length; i++) { 
		total += parseInt(subt[i].value);
	}
	var imp = $('#impuesto').val();
	$('#inpuesto_name').html(`Impuesto(${imp}%)`);
	var subtotal = total / (1 + imp / 100);
	$('#_subtotal').html("S/." + subtotal.toFixed(2));
	$('#_impuesto').html("S/." + (total - subtotal).toFixed(2));
	$("#total").html("S/." + total);
	$("#total_venta").val(total);
	evaluar();
}

function modificarSubtotales(val){
	var cant=document.getElementsByName("cantidad[]");
	var stocks = document.getElementsByName("stock[]"); 
	var prev=document.getElementsByName("precio_venta[]");
	var desc=document.getElementsByName("descuento[]");
	var sub = document.getElementsByName("subtotal"); 
	
	for (var i = 0; i < cant.length; i++) {
		var inpV = cant[i].value;
		var inpP = prev[i].value;
		var inpS = sub[i].value;
		var stockA = stocks[i].value; 
		var des = desc[i].value;  
		inpS=(inpV*inpP)-des;
		if (val==0) { 
			document.getElementsByName("subtotal")[i].innerHTML = inpS; 
			$(`#subar${i}`).val(inpS);
		} else { 
			if ((inpV*1) > parseInt(stockA)) { 
				alert("stock insuficiente");
				break
			} else { 
				document.getElementsByName("subtotal")[i].innerHTML = inpS;
				$(`#subar${i}`).val(inpS);
			}
		} 
	} 
	setTimeout(function () { 
		calcularTotales();
		console.log('sdf');
	}, 1000);
}



function evaluar(){ 
	if (detalles>0) 
	{
		$("#btnGuardar").show();
	}
	else
	{
		$("#btnGuardar").hide();
		cont=0;
	}
}

function eliminarDetalle(indice, idarticulo){
	$("#fila"+indice).remove();
	calcularTotales();
	detalles=detalles-1;
	$(`#btnadd${idarticulo}`).attr('disabled', false);
}

$('#impuesto').change(function () {
	calcularTotales();
});

function verocultar() {
	let tipo = $('#tipo_pago').val();
	if (tipo=='Efectivo') {
		$('#operaciones').hide();
	} else {
		$('#operaciones').show();
	}
		
}


init();