<?php
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;
 use PHPMailer\PHPMailer\SMTP;
 use League\OAuth2\Client\Provider\Google;

 session_start();

/*
* Get credential of user's email on database OCSWEB
*/
function get_credentials() {
	require_once(__DIR__ . '/../../var.php');
	require_once(CONF_MYSQL);
	require_once(__DIR__ . '/../function_commun.php');

	$_SESSION['OCS']["readServer"] = dbconnect(SERVER_READ, COMPTE_BASE, PSWD_BASE, DB_NAME, SSL_KEY, SSL_CERT, CA_CERT, SERVER_PORT);
		
	$sql = "SELECT * FROM notification_config";
	$result_query = mysqli_query($_SESSION["OCS"]["readServer"], $sql);
	$all_credentials = array();
	while ($credentials = mysqli_fetch_array($result_query)) {
		$all_credentials[$credentials['NAME']]['TVALUE'] = $credentials['TVALUE'];
	}

	return $all_credentials;	
}

	
function Send_Email($html) {
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/Exception.php';
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
	require __DIR__.'/../../vendor/phpmailer/phpmailer/src/SMTP.php';

	$all_credentials = array();
	$all_credentials = get_credentials();
	$mail = new PHPMailer();
  //$smtp = new SMTP(); 

	/* #########################
	 * 	Base Config
	 * #########################
	 */
	$assunto = 'CTIC/CSIC - Monitoramento de Ativos';
	$message = $html;
	$usermail = $all_credentials['NOTIF_MAIL_ADMIN']['TVALUE'];
	$username = $all_credentials['NOTIF_NAME_ADMIN']['TVALUE'];
	$password = $all_credentials['NOTIF_PASSWD_SMTP']['TVALUE'];

	/* Define a SMTP Host */
	$host_do_email = $all_credentials['NOTIF_SMTP_HOST']['TVALUE'];

	/* Configura os destinatários  */
	$mail->AddAddress($all_credentials['NOTIF_MAIL_ADMIN']['TVALUE'], $all_credentials['NOTIF_NAME_ADMIN']['TVALUE']);
	// $mail->AddAddress('email@email.com');
	// $mail->AddCC('email@email.com', 'Nome da pessoa'); // Copia
	// $mail->AddBCC('email@email.com', 'Nome da pessoa'); // Cópia Oculta

	/* ###########################
	 * # 	USER CONFIG          # 
	 * ###########################
	 */

  //$smtp->hello("rt2.csic.ufpa.br");
	/* Define a SMTP conection */
	$mail->IsSMTP();
  /* Define a SMTP HELO - FQDN */
  $mail->Helo = 'ocs.csic.ufpa.br';
	/* Define an address of host */
	$mail->Host = $host_do_email;
	/* Use SMTP autentication */ 
	$mail->SMTPAuth = true;
	/* Connection Protocol */
	$mail->SMTPSecure = $all_credentials['NOTIF_SEND_MODE']['TVALUE'];
	/* connection Port */
	$mail->Port = $all_credentials['NOTIF_PORT_SMTP']['TVALUE'];
	/* Username */
	$mail->Username = $usermail;
	/* User's password */
	$mail->Password = $password;
	$mail->SMTPDebug = SMTP::DEBUG_SERVER;

	/* Configura os dados do remetente do email */
	$mail->From = $usermail; // Seu e-mail
	$mail->FromName = $username; // Seu nome

	/* Message Configuring */
	$mail->IsHTML(true); 

	/*   
	 * Se tiver problemas com acentos, modifique o charset
	 * para ISO-8859-1  
	 */
	$mail->CharSet = 'ISO-8859-1'; 

	/* Define a text and subject*/
	$mail->Subject  = $assunto; 
	$mail->Body = $message; // HTML message
	$mail->AltBody = trim(strip_tags($message)); // Alternative message if necessary

	/* Configura o anexo a ser enviado (se tiver um) */
	//$mail->AddAttachment("foto.jpg", "foto.jpg");  // Insere um anexo

	/* Send the email */
	if ($all_credentials['NOTIF_FOLLOW']['TVALUE'] == 'ON')
		$sended_mail = $mail->Send();

	/* Clean everything associated with email */
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();

	// Generate a log file of sending of email
	date_default_timezone_set("America/Belem");
	$log_file = fopen(__DIR__ . '/log_file', 'a') or die('Unable to open the log file');
	if ($sended_mail) {
		fwrite($log_file, date('d/m/Y -- H:i:s') . " | The email has been send sucessfully.\n");
	} else {
		fwrite($log_file, date('d/m/Y == H:i:s') . " | The email hasn't been send with sucess. Please contact the suport\n");
		echo "<b>Error logs:</b> <br />" . $mail->ErrorInfo . "\n";
	}
	fclose($log_file);
}
