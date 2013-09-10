$(document).ready(function() {
	
    $(document).ajaxStart(function() {
    	$('#loading').fadeIn("normal");
        $.blockUI({ message: null, overlayCSS: { backgroundColor: 'transparent' } });
    }).ajaxStop(function() {
    	$.unblockUI();
        $('#loading').fadeOut("normal");
    });
    
    // Read URL GET variables with JavaScript
	function getUrlVar(key,page) {
		var result = new RegExp(key + "=([^&]*)", "i").exec(page);
		return result && unescape(result[1]) || "";
	}
    
    //Carga el formulario de clientes
    $('#content-page').on('click', '#agregar-cliente, .editar', function(event) {
		var page = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: page,
            //data: 'page='+page,
            success: function(data) {
            	//$.getScript("clientes_script.js")
                //$('#left-form').fadeOut("normal", function() {
                	$("#msgbox2").removeClass().hide();
					$('#left-form').html(data); //.show()
					$('#rut_cliente').focus();
				//});
            }
        });
        return false;
    });
    
    //Cancela el ingreso del cliente y envía el foco a la grilla
    $('#content-page').on('click', '#cancelar-ingreso', function(event) {
        $.ajax({
            type: 'POST',
            url: 'clientes-form.php',
            //data: 'page='+page,
            success: function(data) {
            	//$.getScript("clientes_script.js")
                $('#left-form').fadeOut("normal", function() {
                	$("#msgbox2").removeClass().hide();
					$('#left-form').html(data).show();
				});
            }
        });
        return false;
    });
    
    //Eliminar clientes
    $('#content-page').on('click', '.delete', function(event) {
		var page = $(this).attr('href');
		var id_cliente = getUrlVar("id_cliente",page);
		var rut_cliente = getUrlVar("rut_cliente",page);
		var nombre_cliente = getUrlVar("nombre_cliente",page);
		var eliminar = confirm('¿Está seguro que desea eliminar al cliente "'+nombre_cliente+'"?');
		
		if ( eliminar == true ) {
			$.post("clientes-form.php", function(data) {
				$("#msgbox2").removeClass();
				$('#left-form').html(data);
			});
			$.get(page, function(data) {
				//$("#cliente-"+id_cliente).fadeOut("normal", function() {
					//$("#cliente-"+id_cliente).remove();
					$('#right-grilla').html(data);
					//setTimeout(function() { $('#msgbox2').fadeOut('normal'); }, 5000);
				//});
			});
		}
        return false;
    });
	
	//Envía el formulario
	$('#content-page').on('submit', '#clientes-formulario', function(event) {
		var rut = $('input[type="text"]#rut_cliente').val();
		var digito = $('#digito_verificador').val();
		if ( $.Rut.validar(rut+'-'+digito) ) {
		
		$.ajax({
			type: 'POST',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				//$('#left-form').fadeOut("normal", function() {
					$(window).scrollTop(0);
					$("#msgbox2").removeClass().hide();
					$('#left-form').html(data); //.show()
					/*if ( $('#msgbox2').hasClass('modificado') ) {
						setTimeout(function() { $('#msgbox2').fadeOut('normal'); }, 3000);
					}*/
					
					$.post("clientes-grilla.php", function(data) {
						$('#right-grilla').html(data);
						if ( $('#msgbox2').hasClass('fin-ingreso') ) {
							$("table.grilla tbody tr:first").css("background-color","#B5D4FE");
							$("table.grilla tbody tr:first td input.checkclientes").prop('checked', true);
						}
						//setTimeout(function() { $('#msgbox2').fadeOut('normal'); }, 3000);
					});
				//});
			}
		});
		
		} else {
			$('input[type="text"]#rut_cliente').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
			$('#digito_verificador').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
		}
		
		return false;
	});
	
	//Envía la consulta de la grilla
	$('#content-page').on('submit', '#clientes-grilla', function(event) {
		$.ajax({
			type: 'GET',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				//$('#right-grilla').fadeOut("normal", function() {
					$('#right-grilla').html(data); //.show()
				//});
			}
		});
		return false;
	});
	
	//Vuelve al ingreso de la propiedad y muestra los datos que se guardaron en la variable sesion
	/*$('#content-page').on('click', '#volver_propiedad', function(event) {
		var page = $(this).attr('href');
		window.location.href = page;
		return false;
	});*/
	
    //Recarga la pagina con AJAX para el paginador y para el orden según encabezado de la grilla
    $('#content-page').on('click', '#paginator #pages a, table.grilla thead tr th a', function(event) {
		var page = $(this).attr('href');
		var data_GET = page.split("?")[1]; //var nombre_script = page.match(/\/([^/]+)$/)[1]; obtiene sólo el nombre del script
		//alert(data_GET);
        $.ajax({
            type: 'GET',
            url: 'clientes-grilla.php?'+data_GET,
            //data: 'page='+page,
            success: function(data) {
                //$('#right-grilla').fadeOut("normal", function() {
					$('#right-grilla').html(data);
				//});
            }
        });
        return false;
    });
    
});