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
					$('.content-popup').html(data);
				//});
            }
        });
        return false;
    });
    $('#content-page').on('click', '.cerrar', function(event) {
		$('#box-popup').fadeOut("normal", function() {
			$('.content-popup').empty();
		});
        return false;
    });
    
    //Cancela el envio de la carta oferta y carga la grilla de propiedades
    $('#content-page').on('click', '#cancelar-envio', function(event) {
    	var url_varsget = $('#url_varsget').val();
        window.location.href = 'propiedades.php'+url_varsget;
        event.preventDefault(); //return false;
    });
    
    //Selecciona al cliente y envia los datos a la carta de oferta
    $('#content-page').on('click', '.select-propietario', function(event) {
    	var page = $(this).attr('href');
		var id_cliente = getUrlVar("id_cliente",page);
		
		$('#id_cliente').val(id_cliente);
    	$('#box-popup').hide();
		$('.content-popup').empty();
        event.preventDefault(); //return false;
    });
    
});