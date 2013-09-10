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
    
    //Cargar agenda de visitas
    $('#content-page').on('click', '.add-evento', function(event) { //$(".add-event").click(function(event) {
		var page = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: page,
            //data: 'page='+page,
            success: function(data) {
            	//$.getScript("agenda_visitas_script.js")
            	$(window).scrollTop(0);
                $('#agenda-calendario').fadeOut("normal", function() {
					$('#content-page').html(data);
				});
            }
        });
        return false;
    });
    //Cargar horario y detalles de eventos de la agenda
    $('#content-page').on('click', '.ver-evento', function(event) { //$(".add-evento").click(function(event) {
		var page = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: page,
            //data: 'page='+page,
            success: function(data) {
            	//$.getScript("agenda_visitas_script.js")
            	//$(window).scrollTop(0);
                $('#box-popup').fadeIn("normal", function() {
					$('.content-popup').html(data);
				});
            }
        });
        return false;
    });
    $('#content-page').on('click', '.cerrar, .cerrar-ventana', function(event) {
		$('#box-popup').fadeOut("normal", function() {
			$('.content-popup').empty();
		});
        return false;
    });
    
    //Envia los datos de la grilla para finalizar la orden de visita
    $('#content-page').on('click', '.finalizar-orden, .eliminar-orden', function(event) {
		var accion = $(this).attr('name');
		$('#accion').val(accion);
		var id_visita = $(this).val();
		$('#id_visita').val(id_visita);
		
		if ( accion == 'finalizar-orden' ) {
			var finalizar = confirm('¿Está seguro que desea dar por finalizada la orden de visita?');
			if ( finalizar == true ) {
				$.ajax({
					type: 'GET',
					url: $('#visitas-eventos-formulario').attr('action'),
					data: $('#visitas-eventos-formulario').serialize(),
					success: function(data) {
						//$('#content-eventos').fadeOut("normal", function() {
							$('.content-popup').html(data);
						//});
					}
				});
			}
		} else if ( accion == 'eliminar-orden' ) {
			var eliminar = confirm('¿Está seguro que desea eliminar la orden de visita?');
			if ( eliminar == true ) {
				$.ajax({
					type: 'GET',
					url: $('#visitas-eventos-formulario').attr('action'),
					data: $('#visitas-eventos-formulario').serialize(),
					success: function(data) {
						$("#evento-detalles-"+id_visita).fadeOut("normal", function() { $("#evento-detalles-"+id_visita).remove(); });
						$("#evento-form-"+id_visita).fadeOut("normal", function() { $("#evento-form-"+id_visita).remove(); });
					}
				});
			}
		}
		return false;
    });
    
    //Envia los datos de la grilla para modificar la orden de visita
    $('#content-page').on('click', '.editar-orden', function(event) {
		var accion = $(this).attr('name');
		$('#accion').val(accion);
		var id_visita = $(this).val();
		$('#id_visita').val(id_visita);
		
		$.ajax({
			type: 'GET',
			url: 'agenda_visitas-registros_edicion.php',
			data: $('#visitas-eventos-formulario').serialize(),
			success: function(data) {
				//$('#content-eventos').fadeOut("normal", function() {
					$('.content-popup').html(data);
				//});
			}
		});
		return false;
    });
    
    //Cancela el ingreso en la agenda de visitas y carga el calendario de eventos
    $('#content-page').on('click', '#cancelar-ingreso', function(event) { //$("#cancelar-ingreso").click(function() {
    	//var url_referer = $('#url_referer').val();
    	var url_varsget = $('#url_varsget').val();
        $.ajax({
            type: 'POST',
            url: 'agenda_visitas-calendario.php'+url_varsget,
            //data: 'page='+page,
            success: function(data) {
                $('#content-eventos').fadeOut("normal", function() {
					$('#content-page').html(data);
				});
            }
        });
        return false;
    });
    
	//Envía el formulario de busqueda de propiedades
	$('#content-page').on('submit', '#visitas-eventos-formulario', function(event) { //$('#visitas-eventos-formulario').submit(function() {
		var propiedades = '';
		$(".checkpropiedades").each(function() {
			if ( !$(this).is(':checked') ) {
				propiedades = $(this).val();
				$('#horario-'+propiedades).val('');
			} else {
				propiedades = $(this).val();
				if ( $('#horario-'+propiedades).val() == '' ) {
					$(this).prop('checked', false);
				}
			}
		});
		
		$.ajax({
			type: 'GET',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				$('#content-eventos').fadeOut("normal", function() {
					$('#content-page').html(data);	
				});
			}
		});
		return false;
	});
	
    //Recarga la pagina con AJAX para el paginador y para el orden según encabezado de la grilla
    $('#content-page').on('click', '#paginator #pages a, table.grilla thead tr th a', function(event) {
		var page = $(this).attr('href');
        $.ajax({
            type: 'GET',
            url: page,
            //data: 'page='+page,
            success: function(data) {
                //$('#content-eventos').fadeOut("normal", function() {
					$('#content-page').html(data);
				//});
            }
        });
        return false;
    });
	
	//Recarga la página según el cliente o vendedor seleccionado, para mostrar actualizado los datos de los horarios
	$('#content-page').on('change', '#id_cliente, #id_vendedor', function(event) {
		var propiedades = '';
		$(".checkpropiedades").each(function() {
			if ( !$(this).is(':checked') ) {
				propiedades = $(this).val();
				$('#horario-'+propiedades).val('');
			} else {
				propiedades = $(this).val();
				if ( $('#horario-'+propiedades).val() == '' ) {
					$(this).prop('checked', false);
				}
			}
		});
		
		$.ajax({
			type: 'GET',
			url: $('#visitas-eventos-formulario').attr('action'),
			data: $('#visitas-eventos-formulario').serialize(),
			success: function(data) {
				//$('#content-eventos').fadeOut("normal", function() {
					$('#content-page').html(data);	
				//});
			}
		});
		return false;
	});
	
	//Recarga la página según el cliente o vendedor seleccionado, para mostrar actualizado los datos de los horarios
	$('#content-page').on('change', '#cliente, #vendedor', function(event) {	
		$.ajax({
			type: 'GET',
			url: 'agenda_visitas-horarios.php',
			data: $('#visitas-eventos-formulario').serialize(),
			success: function(data) {
				//$('#content-eventos').fadeOut("normal", function() {
					$('#horario').empty();
					$('#horario').html(data);	
				//});
			}
		});
		return false;
	});
	
	//Carga la grilla de clientes para seleccionar más facilmente un cliente
    $('#content-page').on('click', '#buscar_cliente', function(event) {
		var page = $(this).attr('href');
		//$('#box-popup').css("width","820px");
        $.ajax({
            type: 'GET',
            url: page,
            //data: 'page='+page,
            success: function(data) {
                //$('#box-popup').fadeIn("normal", function() {
                	$('#box-popup').show();
					$('.content-popup').html(data).show();
				//});
            }
        });
        return false;
    });
    //Selecciona al cliente y envia los datos al formulario de la orden de visita
    $('#content-page').on('click', '.select-propietario', function(event) {
    	var page = $(this).attr('href');
		var id_cliente = getUrlVar("id_cliente",page);
		
		$('#id_cliente').val(id_cliente);
    	$('#box-popup').hide();
		$('.content-popup').empty();
        return false;
    });
	
	$('#content-page').on('click', '#guardar-orden', function(event) {
		var id_cliente = $('#id_cliente').val();
		var id_vendedor = $('#id_vendedor').val();
		var propiedades = '';
		var enviar_ok = true;
		
		$(".checkpropiedades").each(function() {
			if ( !$(this).is(':checked') ) {
				propiedades = $(this).val();
				$('#horario-'+propiedades).val('');
			} else {
				propiedades = $(this).val();
				if ( $('#horario-'+propiedades).val() == '' ) {
					$(this).prop('checked', false);
					
					var indice = $(".checkpropiedades").index(this);
					if ( indice%2 == 0 ) { //si es par
						$(this).closest("tr").css("background-color","#fbfcfc");
					} else {
						$(this).closest("tr").css("background-color","#f0f0f0");
					}
					
				}
			}
		});
		
		$(".checkpropiedades").each(function() { //$(".checkpropiedades:checked").each(function() {
			propiedades = $(this).val();
			if ( $(this).is(':checked') && $('#horario-'+propiedades).val() != '' ) {
				enviar_ok = true;
				return false;
			} else {
				enviar_ok = false;
			}
		});
		
		if ( id_cliente != '' && id_vendedor != '' && enviar_ok == true ) {
			$('#accion').val('guardar-orden');
			$.ajax({
				type: 'GET',
				url: $('#visitas-eventos-formulario').attr('action'),
				data: $('#visitas-eventos-formulario').serialize(),
				success: function(data) {
					$('#content-eventos').fadeOut("normal", function() {
						$('#content-page').html(data);	
					});
				}
			});
		}
		return false;
	});
	
    //Agregar elemento a la agenda (no se usa, sólo lo dejo como referencia)
    var number = 1;
    $('#add-field').click(function() {
    	++number;
		$('#agenda-visitas tbody').append('<tr id="evento-'+(number)+'"><td><input type="text" name="cliente[]" id="cliente-'+(number)+'" /></td><td><input type="text" name="propiedad[]" id="propiedad-'+(number)+'" /></td><td><input type="text" name="vendedor[]" id="vendedor-'+(number)+'" /></td><td><select id="hora-'+(number)+'" name="hora[]"></select></td><td><a href="javascript:void(0);" class="delete-field" id="'+(number)+'">Eliminar</a></td></tr>');
		
		var cliente = $('cliente-'+(number)).attr("value");
		var propiedad = $('propiedad-'+(number)).attr("value");
		var vendedor = $('vendedor-'+(number)).attr("value");
		$.post("agenda_visitas-horarios.php", { name: "John", time: "2pm" }, function(data) {
			//alert("Data Loaded: " + data);
			$('select#hora-'+(number)).html(data);
		});
	});
	//----------------------------------------------------------------------
    
});