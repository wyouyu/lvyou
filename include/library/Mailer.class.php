<?php
/**
 * @author: shwdai@gmail.com
 */
class Mailer {

	static private $from = null;
	function __construct()
	{
	}

	static private function EscapeHead($string, $encoding='GB2312')
	{
		$string	= mb_convert_encoding($string, $encoding, "UTF-8");
		return '=?' . $encoding . '?B?'. base64_Encode($string) .'?=';
	}

	static private function EscapePart($string, $encoding='GB2312')
	{
		$string = mb_convert_encoding($string, $encoding, 'UTF-8');
		return preg_replace_callback( '/([\x80-\xFF]+.*[\x80-\xFF]+)/' ,create_function ( '$m' ,"return \"=?$encoding?B?\".base64_encode(\$m[1]).\"?=\";") ,$string);
	}
	
	/*
	 *	发送邮件的最后一步
	 *	@param options	
			contentType		= true
			messageId		= null
			encoding		= 'UTF-8'
	 */
	static function SendMail($from, $to, $subject, $message, $options=null, $bcc=array())
	{
		global $INI; $from = "{$INI['system']['sitename']} <{$from}>";
		if ( !isset($options['encoding']) )
			$options['encoding'] 	= 'UTF-8';

		if ( !isset($options['contentType']) )
			$options['contentType'] = 'text/plain';

		if ( 'UTF-8'!=$options['encoding'] )
			$message = mb_convert_encoding($message, $options['encoding'], 'UTF-8');

		$message = chunk_split(base64_encode($message));
		$subject = self::EscapeHead($subject, $options['encoding']);
		$from = self::EscapePart($from, $options['encoding']);
		$to = self::EscapePart($to, $options['encoding']);

		$headers = array(
				"Mime-Version: 1.0",
				"Content-Type: {$options['contentType']}; charset={$options['encoding']}",
				"Content-Transfer-Encoding: base64",
				"X-Mailer: ZTMailer/1.0",
				"From: {$from}",
				"Reply-To: {$from}",
				);
		if ($bcc) { 
			$bcc = join(', ', $bcc);
			$headers[] = "Bcc: {$bcc}";
		}
		$headers = join("\r\n", $headers);

		if ( isset($options['messageId']) )
			$headers["Message-Id"] = "<$options[messageId]>";

		return mail($to, $subject, $message, $headers);
	}

	static function SmtpMail($from, $to, $subject, $message, $options=null, $bcc=array())
	{
		/* settings */
		if ( !isset($options['encoding']) )
			$options['encoding'] 	= 'UTF-8';

		if ( !isset($options['contentType']) )
			$options['contentType'] = 'text/plain';

		if ( 'UTF-8'!=$options['encoding'] )
			$message = mb_convert_encoding($message, $options['encoding'], 'UTF-8');
		global $INI;
		/* get from ini */
		$host = $INI['mail']['host'];
		$port = $INI['mail']['port'];
		$ssl = $INI['mail']['ssl'];
		$user = $INI['mail']['user'];
		$pass = $INI['mail']['pass'];
		$from = $INI['mail']['from'];
		$reply = $INI['mail']['reply'];
		$site = $INI['system']['sitename'];
		//exit(print_r($host.$port.$ssl.$user.$pass.$from.$reply.$site));
		$subject = self::EscapeHead($subject, $options['encoding']);
		$site = self::EscapeHead($site, $options['encoding']);
		$body = $message;

		$ishtml = ($options['contentType']=='text/html');
		//begin
		$mail = new PHPMailer();
		$mail->IsSMTP(); 
		$mail->CharSet = $options['encoding'];
		$mail->SMTPAuth   = true; 
		$mail->Host = $host;
		$mail->Port = $port;
		if ( $ssl=='ssl' ) {
			$mail->SMTPSecure = "ssl"; 
		} else if ( $ssl == 'tls' ) {
			$mail->SMTPSecure = "tls"; 
		}
		$mail->Username = $user;
		$mail->Password = $pass;
		//exit(print_r($reply));
		$mail->SetFrom($from, $site);
		$mail->AddReplyTo($reply, $site);
		foreach($bcc AS $bo) {
			$mail->AddBCC($bo);
		}
		$mail->Subject = $subject;
		if ( $ishtml ) {
			$mail->MsgHTML($body);
		} else {
			$mail->Body = $body;
		}
		$mail->AddAddress($to);
		return $mail->Send();
	}
}
?>
