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
    
    //Carga el formulario de usuarios
    $('#content-page').on('click', '#agregar-usuario, .editar', function(event) {
		var page = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: page,
            //data: 'page='+page,
            success: function(data) {
                //$('#left-form').fadeOut("normal", function() {
                	$("#msgbox2").removeClass().hide();
					$('#left-form').html(data); //.show()
					$('#rut_usuario').focus();
				//});
            }
        });
        return false;
    });
    
    //Cancela el ingreso del usuario y envía el foco a la grilla
    $('#content-page').on('click', '#cancelar-ingreso', function(event) {
        $.ajax({
            type: 'POST',
            url: 'usuarios-form.php',
            //data: 'page='+page,
            success: function(data) {
                $('#left-form').fadeOut("normal", function() {
                	$("#msgbox2").removeClass().hide();
					$('#left-form').html(data).show();
				});
            }
        });
        return false;
    });
    
    //Eliminar usuarios
    $('#content-page').on('click', '.delete', function(event) {
		var page = $(this).attr('href');
		var id_usuario = getUrlVar("id_usuario",page);
		var rut_usuario = getUrlVar("rut_usuario",page);
		var nombre_usuario = getUrlVar("nombre_usuario",page);
		var eliminar = confirm('¿Está seguro que desea eliminar al usuario "'+nombre_usuario+'"?');
		
		if ( eliminar == true ) {
			$.post("usuarios-form.php", function(data) {
				$("#msgbox2").removeClass();
				$('#left-form').html(data);
			});
			$.get(page, function(data) {
				$("#usuario-"+id_usuario).fadeOut("normal", function() {
					//$("#usuario-"+id_usuario).remove();
					$('#right-grilla').html(data);
					//setTimeout(function() { $('#msgbox2').fadeOut('normal'); }, 5000);
				});
			});
		}
        return false;
    });
	
	//Envía el formulario
	$('#content-page').on('submit', '#usuarios-formulario', function(event) {
		var rut = $('input[type="text"]#rut_usuario').val();
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
					
					$.post("usuarios-grilla.php", function(data) {
						$('#right-grilla').html(data);
						if ( $('#msgbox2').hasClass('fin-ingreso') ) {
							$("table.grilla tbody tr:first").css("background-color","#B5D4FE");
							$("table.grilla tbody tr:first td input.checkusuarios").prop('checked', true);
						}
						//setTimeout(function() { $('#msgbox2').fadeOut('normal'); }, 3000);
					});
				//});
			}
		});
		
		} else {
			$('input[type="text"]#rut_usuario').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
			$('#digito_verificador').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
		}
		
		return false;
	});
	
	//Envía la consulta de la grilla
	$('#content-page').on('submit', '#usuarios-grilla', function(event) {
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
	
    //Recarga la pagina con AJAX para el paginador y para el orden según encabezado de la grilla
    $('#content-page').on('click', '#paginator #pages a, table.grilla thead tr th a', function(event) {
		var page = $(this).attr('href');
		var data_GET = page.split("?")[1]; //var nombre_script = page.match(/\/([^/]+)$/)[1]; obtiene sólo el nombre del script
		//alert(data_GET);
        $.ajax({
            type: 'GET',
            url: 'usuarios-grilla.php?'+data_GET,
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