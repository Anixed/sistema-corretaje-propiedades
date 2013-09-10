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
    
    //Carga el formulario de propiedades
    $('#content-page').on('click', '#agregar-propiedad, .editar, .delete', function(event) { //$("#agregar-propiedad").click(function() {
		var page = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: page,
            //data: 'page='+page,
            success: function(data) {
            	//$.getScript("propiedades_script.js")
                $('#content-propiedades').fadeOut("normal", function() {
					$('#content-page').html(data);
				});
            }
        });
        return false;
    });
    
	//Carga la página para enviar la carta de oferta con las propiedades seleccionadas
	$('#content-page').on('click', '#enviar-propiedad', function(event) {
		
		var i = 0;
		var propiedades = new Array();
		$(".checkpropiedades").each(function() {
			if ( $(this).is(':checked') ) {
				propiedades[i] = $(this).val();
				i++;
			}
		});
		
		if ( propiedades != "" ) {
			$('#form-prop-carta-oferta').submit();
			//var page = $(this).attr('href');
	        /*$.ajax({
	            type: 'POST',
	            url: page,
	            data: { propiedades : propiedades },
	            success: function(data) {
	            	//$.getScript("ckeditor/ckeditor.js");
	            	//$.getScript("ckfinder/ckfinder.js");
	                $('#content-propiedades').fadeOut("normal", function() {
						$('#content-page').html(data);
					});
	            }
	        });*/
		} else {
			alert("No ha seleccionado ninguna propiedad.");
		}
        event.preventDefault();
        
	});
    
    //Cancela el ingreso de una propiedad y carga la grilla de propiedades
    $('#content-page').on('click', '#cancelar-ingreso', function(event) { //$("#cancelar-ingreso").click(function() {
    	var url_referer = $('#url_referer').val();
    	var url_varsget = $('#url_varsget').val();
        $.ajax({
            type: 'POST',
            url: url_referer+'.php'+url_varsget, //'propiedades-grilla.php',
            //data: 'page='+page,
            success: function(data) {
            	//$.getScript("propiedades_script.js")
                $('#content-propiedades').fadeOut("normal", function() {
					$('#content-page').html(data);
				});
            }
        });
        return false;
    });
	
	//Envía el formulario
	$('#content-page').on('submit', '#propiedades-formulario', function(event) { //$('#propiedades-formulario').submit(function() {
		$.ajax({
			type: 'POST',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				$('#content-propiedades').fadeOut("normal", function() {
					$('#content-page').html(data);	
				});
			}
		});
		return false;
	});
	
	//Recarga la página para guardar los datos de la propiedad en una variable sesion, y despues redirigue (por javascript) al ingreso de los clientes
	$('#content-page').on('click', '#ingresar_propietario', function(event) {
		var page = $(this).attr('href');
		$.ajax({
			type: 'POST',
			url: page,
			data: $('#propiedades-formulario').serialize(),
			success: function(data) {
				//Permanent Redirect with HTTP 301
				window.location.href = 'clientes.php?ingresando_propiedad=si';
			}
		});
		return false;
	});
	
	//Carga la grilla de clientes para buscar al propietario o la pantalla para gestionar los sectores
    $('#content-page').on('click', '#buscar_propietario, #gestionar_sector', function(event) {
		var page = $(this).attr('href');
		if ( $(this).attr('id') == 'gestionar_sector' ) {
			$('#box-popup').css("width","435px");
		} else {
			$('#box-popup').css("width","820px");
		}
		
        $.ajax({
            type: 'GET',
            url: page,
            //data: 'page='+page,
            success: function(data) {
                $('#box-popup').fadeIn("normal", function() {
					$('.content-popup').html(data);
				});
            }
        });
        return false;
    });
	//Carga el módulo de gestión de fotografias de la propiedad
    $('#content-page').on('click', '#gestion-fotos', function(event) {
		var id_propiedad = $('#id_propiedad').val();
		var tipo_propiedad = $('#tipo_propiedad').val();
		
		//window.open('propiedades-upload-fotos.php?id_propiedad='+id_propiedad+'&tipo_propiedad='+tipo_propiedad, "uploads-fotos" , "width=820,height=400,location=NO,scrollbars=YES,resizable=NO");
		TINY.box.show({
			iframe: 'propiedades-upload-fotos.php?id_propiedad='+id_propiedad+'&tipo_propiedad='+tipo_propiedad,
			boxid: 'uploads-fotos',
			width: 820, height: 400,
			fixed: true,
			maskopacity: 40,
			closejs: function(){
				$.post("propiedades-lista-fotos.php", { "id_propiedad": id_propiedad }, function(data) {
					$('fieldset .fotos-propiedades').html(data);
				});
			}
		});
        return false;
    });
	//Abre la fotografía en el lightbox
    $('#content-page').on('click', '.fotos-propiedades .open-foto', function(event) {
    	var url_foto = $(this).attr('href');
    	TINY.box.show({
    		image: url_foto,
    		boxid: 'box-open-foto',
    		animate: true
    	});
        return false;
    });
	//Carga la pantalla para gestionar la calefacción
    $('#content-page').on('change', '#calefaccion', function(event) {
		var option = $(this).val();
		if ( option == 'Otra' ) {
			$('#box-popup').css("width","435px");
	        $.ajax({
	            type: 'GET',
	            url: 'gestion-calefaccion.php',
	            //data: 'page='+page,
	            success: function(data) {
	                $('#box-popup').fadeIn("normal", function() {
						$('.content-popup').html(data);
					});
	            }
	        });
		}
        return false;
    });
    $('#content-page').on('click', '.cerrar', function(event) {
		$('#box-popup').fadeOut("normal", function() {
			$('.content-popup').empty();
		});
        return false;
    });
    
	//Carga el mapa de google maps en el popup de tinybox2 con el id de la propiedad
    $('#content-page').on('click', '.google-maps, #google-maps-consultar', function(event) {
		var page = $(this).attr('href');
		TINY.box.show({
			iframe: page,
			boxid: 'frameless',
			width: 750,height: 450,
			fixed: true,
			maskopacity: 40
			/*closejs: function(){
				closeJS()
			}*/
		});
        return false;
    });
	//Carga el mapa de google maps en el popup de tinybox2 con la dirección y comuna de la propiedad
    $('#content-page').on('click', '#google-maps-search', function(event) {
		var sector = $('#sector').val();
		var direccion = $('#direccion').val().replace(/#/g,'').trim();
		var numero = $('#num_direccion').val().replace(/#/g,'').trim();
		var comuna = $('#comuna').val();
		if ( direccion != '' && numero != '' && comuna != '' ) {
			var address = direccion+', '+numero+', '+comuna+', Chile';
		} else if ( direccion != '' && comuna != '' ) {
			var address = direccion+', '+comuna+', Chile';
		} else {
			var address = '';
		}
		
		var cod_propiedad = $('#cod_propiedad').val();
		var page = $(this).attr('href');
		TINY.box.show({
			iframe: page+'&cod_propiedad='+cod_propiedad+'&address='+address,
			boxid: 'frameless',
			width: 750,height: 450,
			fixed: true,
			maskopacity: 40
			/*closejs: function(){
				closeJS()
			}*/
		});
        return false;
    });
    
    //Selecciona al propietario y envia los datos al formulario de la propiedad
    $('#content-page').on('click', '.select-propietario', function(event) {
    	var page = $(this).attr('href');
		var id_cliente = getUrlVar("id_cliente",page);
		var rut_cliente = getUrlVar("rut_cliente",page);
		var nombre_cliente = getUrlVar("nombre_cliente",page);
		
		$('#id_propietario').val(id_cliente);
    	$('#nombre_propietario').val('('+rut_cliente+') '+nombre_cliente);
    	
    	$('#box-popup').hide();
		$('.content-popup').empty();
        return false;
    });
    
    //Envía la consulta de la grilla por AJAX
	$('#content-page').on('submit', '#sectores-grilla, #calefaccion-grilla', function(event) {
		$.ajax({
			type: 'GET',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				$('.content-popup').html(data);
			}
		});
		return false;
	});
	
    //Selecciona el sector y envia los datos al formulario de la propiedad
    $('#content-page').on('click', '.seleccionar-sector, .seleccionar-calefaccion', function(event) {
    	var page = $(this).attr('href');
    	var select_id = $(this).attr('class').split("-")[1];
		//var id_sector = getUrlVar("id_sector",page);
		var nombre = getUrlVar(select_id+"_nombre",page);
		
		$.post("options-"+select_id+".php", function(data) {
			$('#'+select_id).html(data);
			$('#'+select_id).val(nombre);
		});
		
    	$('#box-popup').hide();
		$('.content-popup').empty();
        return false;
    });
    //Recarga la pagina con AJAX para eliminar un sector
    $('#content-page').on('click', '.eliminar-sector, .eliminar-calefaccion', function(event) {
		var page = $(this).attr('href');
		var select_id = $(this).attr('class').split("-")[1];
		var nombre = getUrlVar(select_id+"_nombre",page);
		
        $.ajax({
            type: 'GET',
            url: page,
            //data: 'page='+page,
            success: function(data) {
				$('.content-popup').html(data);
				$('#'+select_id+' option[value="'+nombre+'"]').remove();
            }
        });
        return false;
    });
    
});