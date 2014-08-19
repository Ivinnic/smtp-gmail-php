<?php


function smtp_mailer($from, $from_name, $to, $subject, $message, $reply_to = null)
{

	$output = null;
	$smtp_username = '';
	$smtp_password = '';

	$connect = @fsockopen('tcp://smtp.gmail.com', 587, $errno, $errstr, 30);

	// for debugging purposes
	$output[] = ($connect) ? 'Successfully connected to SMTP server.' : '<b>Fatal Error</b>: Connection to SMTP server failed'; 
	
	if ($connect)
	{	
		$rcv = fgets($connect, 1024); 
	
		// HELO server
		fputs($connect, "HELO {$_SERVER['SERVER_NAME']}\r\n"); 

		$output[] = '<b>Command</b>: HELO {' . $_SERVER['SERVER_NAME'] . '} => <b>Result</b>: ' . fgets($connect, 1024); 

 		fputs($connect, "STARTTLS\r\n"); 
		$output[] = '<b>Command</b>: STARTTLS => <b>Result</b>: ' . fgets($connect, 1024); 
		stream_socket_enable_crypto($connect, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

	
		if ($smtp_username && $smtp_password)
		{
			// authentication 
			fputs($connect, "auth login\r\n"); 
			$output[] = '<b>Command</b>: auth login => <b>Result</b>: ' . fgets($connect, 256); 
		   
			// set username
			fputs($connect, base64_encode($smtp_username)."\r\n"); 
			$output[] = '<b>Command</b>: get username => <b>Result</b>: ' . fgets($connect, 256);       
		       
			// set password
			fputs($connect, base64_encode($smtp_password)."\r\n"); 
			$output[] = '<b>Command</b>: get password => <b>Result</b>: ' . fgets($connect, 256);  	  
		}
		
		// set mail from
		fputs($connect, "MAIL FROM:<$from>\r\n");
		$output[] = '<b>Command</b>: MAIL FROM:' . $from . ' => <b>Result</b>: ' . fgets($connect, 1024);
	
		// set recipient(s)
		fputs($connect, "RCPT TO:<$to>\r\n");
		$output[] = '<b>Command</b>: RCPT TO:' . $to . ' => <b>Result</b>: ' . fgets($connect, 1024);
	
		// now set email data (additional headers and mail content)
		fputs($connect, "DATA\r\n");
		$output[] = '<b>Command</b>: DATA => <b>Result</b>: ' . fgets($connect, 1024);
	
		fputs($connect, "Subject: $subject\r\n");
		fputs($connect, "From: $from_name <$from>\r\n");
		fputs($connect, "To: $to \r\n");
		if (!empty($reply_to))
		{
			fputs($connect, "Reply-to: $reply_to \r\n");			
		}
		fputs($connect, "X-Sender: <$from>\r\n");
		fputs($connect, "Return-Path: <$from>\r\n");
		fputs($connect, "Errors-To: <$from>\r\n");
		fputs($connect, "X-Mailer: PHP Ivinnic SMTP\r\n");
		fputs($connect, "X-Priority: 3\r\n");
		fputs($connect, "Content-Type: text/html; charset=iso-8859-1\r\n");
		fputs($connect, "\r\n");
		fputs($connect, stripslashes($message)." \r\n");
		fputs($connect, ".\r\n");
	
		fputs($connect, "RSET\r\n");
		$output[] = '<b>Command</b>: RSET => <b>Result</b>: ' . fgets($connect, 1024);
	
		fputs ($connect, "QUIT\r\n");
		$output[] = '<b>Command</b>: QUIT => <b>Result</b>: ' . fgets ($connect, 1024);
	
		fclose($connect);
	}
	
	return $output;
}

?>
