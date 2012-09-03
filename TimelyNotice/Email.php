<?php
/**
 * @package TimelyNotice
 * @author WiFeng
 * @link http://521-wf.com
 */

class TimelyNotice_Email {
	public $smtpServer = '';
	public $smtpPort = 25;
	public $timeout = 30;
	public $fromEmail = '';
	public $fromName = '';
	public $fromEmailPass = '';

	public function send($toEmail, $toName, $subject, $message) {
		$fromEmail     = $this->fromEmail; 
		$fromName      = $this->fromName;
		$fromEmailPass = $this->fromEmailPass; 
		
		/*  your configuration here  */
		$smtpServer = $this->smtpServer; //ip accepted as well
		$smtpPort = $this->smtpPort;  // should be 25 by default
		$timeout = $this->timeout; //typical timeout. try 45 for slow servers
		$username = $fromEmail;		// "noreply@521-wf.com"; the login for your smtp
		$password = $fromEmailPass ; //the pass for your smtp
		$hello = "Hello World!"; //this seems to work always
		$newLine = "\r\n"; //var just for nelines in MS
		$secure = 0; //change to 1 if you need a secure connect

		/*  you shouldn't need to mod anything else */

		//connect to the host and port
		$smtpConnect = fsockopen($smtpServer, $smtpPort, $errno, $errstr, $timeout);
		$smtpResponse = fgets($smtpConnect, 4096);
		if(empty($smtpConnect))
		{
			$output = "Failed to connect: $smtpResponse";
			return $output;
		}
		else
		{
			$logArray['connection'] = "Connected to: $smtpResponse";
		}

		//say HELO to our little friend
		fputs($smtpConnect, "HELO $hello". $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['heloresponse'] = "$smtpResponse";

		//start a tls session if needed
		if($secure)
		{
			fputs($smtpConnect, "STARTTLS". $newLine);
			$smtpResponse = fgets($smtpConnect, 4096);
			$logArray['tlsresponse'] = "$smtpResponse";

			//you have to say HELO again after TLS is started
			fputs($smtpConnect, "HELO $hello". $newLine);
			$smtpResponse = fgets($smtpConnect, 4096);
			$logArray['heloresponse2'] = "$smtpResponse";
		}

		//request for auth login
		fputs($smtpConnect,"AUTH LOGIN" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['authrequest'] = "$smtpResponse";

		//send the username
		fputs($smtpConnect, base64_encode($username) . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['authusername'] = "$smtpResponse";

		//send the password
		fputs($smtpConnect, base64_encode($password) . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['authpassword'] = "$smtpResponse";

		
		//email from
		fputs($smtpConnect, "MAIL FROM: $fromEmail" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['mailfromresponse'] = "$smtpResponse";

		//email to
		fputs($smtpConnect, "RCPT TO: $toEmail" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['mailtoresponse'] = "$smtpResponse";
		

		//the email
		fputs($smtpConnect, "DATA" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['data1response'] = "$smtpResponse";

		//construct headers
		$headers = "MIME-Version: 1.0" . $newLine;
		$headers .= "Content-type: text/html; charset=utf-8" . $newLine;    //iso-8859-1
		$headers .= "To: {$toName}<$toEmail>" . $newLine;
		$headers .= "From: {$fromName}<$fromEmail>" . $newLine;

		//observe the . after the newline, it signals the end of message
		fputs($smtpConnect, "Subject: {$subject}{$newLine}{$headers}{$newLine}{$newLine}{$message}{$newLine}.{$newLine}");
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['data2response'] = "$smtpResponse";

		// say goodbye
		fputs($smtpConnect,"QUIT" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['quitresponse'] = "$smtpResponse";
		$logArray['quitcode'] = substr($smtpResponse,0,3);
		fclose($smtpConnect);
		//a return value of 221 in $retVal["quitcode"] is a success
		return $logArray;
	}

}

?>