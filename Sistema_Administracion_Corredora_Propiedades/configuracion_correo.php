<?php
	
  	require('PHPMailer_v5.1/class.phpmailer.php');
	//require('sistema/PHPMailer_v5.1/class.smtp.php');
	
	$mail = new PHPMailer();
	$mail->Mailer = 'smtp';
	$mail->IsSMTP(); //Envia el correo via SMTP
	//$mail->SMTPDebug = 1;  // debugging: 1 = errores y mensajes, 2 = mensajes solamente
	$mail->SMTPSecure = "tls"; //$mail->SMTPSecure = 'ssl';
	$mail->Host = 'smtp.gmail.com'; //Servidor SMPT de GOOGLE APPS y de GMAIL
	$mail->Port = 587; //$mail->Port = 465; //Puerto: 465, Activar SSL o bien Puerto: 587, Activar TLS/STARTTLS
	#$mail->WordWrap = 50; //set word wrap to 50 characters
	$mail->SMTPAuth = true; //Activa la autenticacion SMTP
	$mail->Username = 'no-reply@admin.com';
	$mail->Password = 'contrasena';
	$mail->CharSet = 'utf-8'; 
	$mail->IsHTML(true); //Enviar como HTML
	//$mail->From = 'no-reply@admin.com';
	//$mail->FromName = 'No responder';
	$mail->SetFrom('no-reply@admin.com', 'No responder');
	$mail->AddReplyTo('contacto@admin.com', 'Contacto');
	
	/*
	$mail->Subject = 'Aquí el asunto del correo';
	$mail->AddAddress($mail_usuario);
	$mail->Body = $contenidoHTML;
	//$mail->Timeout = 50;
	if ( !$mail->Send() ) {
		$continuar = false;
	} else {
		$continuar = true;
	}
	$mail->ClearAddresses(); //Borra todos los destinatarios del campo ‘TO’, es decir, todos los destinatarios que se han añadido con la función AddAddress
	//$mail->ClearAllRecipients(); //Borra todos los destinatarios TO, CC y BCC
	$mail->SmtpClose(); //Lo mas importante Cerrar la conexiom =]
	*/