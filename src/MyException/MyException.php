<?php 
namespace MyException;

use ErrorException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MyException extends \Exception
{
	function __construct($Message = null)
	{
		if (is_null($Message)) {
			// Afficher les erreurs et les avertissements
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			set_error_handler( array( MyException::class, "log_error" ) ); 
			set_exception_handler( array( MyException::class, "log_exception" ) );
			register_shutdown_function( array( MyException::class, "check_for_fatal" ) );
		}
		else {
			self::log_exception($this,$Message);
		}
	}
	
	//Error handler, passes flow over the exception logger with new ErrorException.
	public static function log_error($num, $str, $file, $line, $context = null)
	{
		$Exception = new ErrorException($str, 0, $num, $file, $line);
		self::log_exception($Exception);
	}
	//Uncaught exception handler.
	public static function log_exception($e, $StringError = null)
	{
		global $MyExceptionLogError;
		global $MyExceptionScreenError;
		global $MyExceptionMailError;
		global $MyExceptionMailFatalError;
		global $MyExceptionEmail;
		global $MyExceptionProjectName;
		global $MyExceptionPHPMailer;
		global $MyExceptionPHPMailSMTPAuth;
		global $MyExceptionPHPMailSMTPSecure;
		global $MyExceptionPHPMailHost;
		global $MyExceptionPHPMailPort;
		global $MyExceptionPHPMailUsername;
		global $MyExceptionPHPMailPassword;

		$ValueErreur = 0;

		if (method_exists($e, 'getSeverity')) {
			$ValueErreur = $e->getSeverity();
			$NiveauErreur = self::friendly_severity($ValueErreur);
		} else {
            if (is_null($StringError)) {
				$NiveauErreur = "Exception (Fatal Error)";
				$ValueErreur = 1;
            }
			else {
				$NiveauErreur = "Exception (Custom Error)";
				$ValueErreur = 0;
            }
		}

		if (true) {
			$erreurhtml = "<div style='text-align: left;'>";
			$erreurhtml .= "<p style='color: rgb(190, 50, 50);'>Une exception a été levée :</p>";
			$erreurcli = "\r\nUne exception a été levée :\r\n";
			$erreurhtml .= "<table style='display: inline-block;'>";
			$erreurhtml .= "<tr style='background-color:rgb(240,240,240);'><th style='width: 80px;'>Type</th><td style='background-color:rgb(230,230,230);'>".$NiveauErreur." (".$ValueErreur.")</td></tr>";
			$erreurcli .= "Type : ".$NiveauErreur." (".$ValueErreur.")\r\n";
			$erreurhtml .= "<tr style='background-color:rgb(240,240,240);'><th>Date Heure</th><td style='background-color:rgb(230,230,230);'>".date("Y-m-d H:i:s")."</td></tr>";
			$erreurcli .= "Date Heure : ".date("Y-m-d H:i:s")."\r\n";
			$erreurhtml .= "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td style='background-color:rgb(230,230,230);'>";
			$erreurcli .= "Message : \r\n";
			if ($StringError != "") {
				$erreurhtml .= htmlentities($StringError)."<br>";
				$erreurcli .= $StringError."\r\n";
			}
			if (method_exists($e, 'getMessage')) {
				$erreurhtml .= $e->getMessage();
				$erreurcli .= $e->getMessage()."\r\n";
			}
			$erreurhtml  .= "</td></tr>";
			$erreurhtml  .= "<tr style='background-color:rgb(240,240,240);'><th>Fichier</th><td style='background-color:rgb(230,230,230);'>";
			$tableau = $e->getTrace();
			$pile   = '';
			$count  = 0;
			$count2 = 0;
			
			$erreurhtml  .=  "{$e->getFile()}:{$e->getLine()}</td></tr>";
			$erreurcli .= "Fichier : {$e->getFile()}:{$e->getLine()}\r\n";
			if (count($tableau)>0) {
				foreach ($tableau as $key => $value) {
					if (($value["function"] != "check_for_fatal") && ($value["function"] != "log_error") && ($value["function"] != "error_log") && ($value["function"] != "log_exception")) {
						$count++;
						$pile .= "#".$count." ->";
						if (isset($value["file"])) {
							$pile .= " <b>Fichier</b> : ".$value["file"];
						}
						if (isset($value["line"])) {
							$pile .= " <b>Ligne</b> : ".$value["line"]."\r\n";
						}
						if (isset($value["function"]) && isset($value["class"])) {
								$pile .= " <b>Method</b> ".$value["class"].$value["type"].$value["function"]."\r\n";
						}
						$compteur = 0;
						if (isset($value["args"])) {
							foreach ($value["args"] as $key2 => $arg) {
								$compteur ++;
								$pile .= " <b>Argument ".$compteur."</b> ";
								$pile .= MyException::printargs($arg);
								$pile .= "\r\n";
							}
						}
					}
				}
			}
			
			if ($count>0)
			{
				$erreurhtml .= "<tr><td>Pile</td><td>";
				$erreurcli .= "Pile :\r\n";
			}
			
			$erreurhtml .= str_replace("\r\n", "<br>", $pile);
			$erreurcli .= $pile;
			$erreurhtml .= "</td></tr>";
			$erreurhtml .= "</table></div>";

			if ($MyExceptionLogError) {
				ini_set('log_errors_max_len', 0);
				error_log(str_replace("<b>", "",str_replace("</b>", "",$erreurcli)));
			}
                        
			if ($MyExceptionScreenError) {
				if (self::is_cli())
				{
					echo $erreurcli;
				}
				else
				{
					echo $erreurhtml;
				}
			}
                        
			if ( ($MyExceptionMailError) || ($MyExceptionMailFatalError && $ValueErreur == 1) ) {
				if ($MyExceptionPHPMailer)
				{
					$mail = new PHPMailer(false); // the true param means it will throw exceptions on errors, which we need to catch

					$mail->IsSMTP(); // telling the class to use SMTP

					try {
						$mail->CharSet = "UTF-8";
						$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
						$mail->SMTPAuth   = $MyExceptionPHPMailSMTPAuth;                  // enable SMTP authentication
						$mail->SMTPSecure = $MyExceptionPHPMailSMTPSecure;                 // sets the prefix to the servier
						$mail->Host       = $MyExceptionPHPMailHost;      // sets SMTP server
						$mail->Port       = $MyExceptionPHPMailPort;                   // set the SMTP port
						$mail->Username   = $MyExceptionPHPMailUsername;  //  username
						$mail->Password   = $MyExceptionPHPMailPassword;            //  password
						$mail->AddAddress($MyExceptionEmail, $MyExceptionProjectName);
						$mail->SetFrom($MyExceptionEmail, $MyExceptionProjectName);
						$mail->Subject = "Erreur ".$MyExceptionProjectName;
						$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
						$mail->MsgHTML($erreurhtml);
						$mail->Send();
					} catch (phpmailerException $e) {
						echo $e->errorMessage(); //Pretty error messages from PHPMailer
					} catch (Exception $e) {
						echo $e->getMessage(); //Boring error messages from anything else!
					}
				}
				else
				{
					// To send HTML mail, the Content-type header must be set
					$headers[] = 'MIME-Version: 1.0';
					$headers[] = 'Content-type: text/html; charset=UTF-8';
					// Additional headers
					$headers[] = 'To: '.$MyExceptionProjectName.' <'.$MyExceptionEmail.'>';
					$headers[] = 'From: '.$MyExceptionProjectName.' <'.$MyExceptionEmail.'>';
					if (!@mail($MyExceptionEmail, "Erreur ".$MyExceptionProjectName, $erreurhtml, implode("\r\n", $headers)))
					{
						$Exception = new \Exception();
						if ("Erreur fonction mail" != $StringError)
						{
							self::log_exception($Exception,"Erreur fonction mail");
						}
					}
				}
			}
		}
	}
	//Checks for a fatal error, work around for set_error_handler not working on fatal errors.
	public static function check_for_fatal()
	{
		$error = error_get_last();
		if ($error["type"] == E_ERROR) self::log_error($error["type"], $error["message"], $error["file"], $error["line"]);
	}
        
	//Traduit le binvalue severity d'une exception.
	//@param $severity = $e->getSeverity() ou $e est une exception
	public static function friendly_severity($severity)
	{
		$names = [];
		$consts = array_flip(
			array_slice(
				get_defined_constants(true)['Core'], 0, 15, true));
		foreach ($consts as $code => $name) {
			if ($severity & $code) $names [] = $name;
		}
		return join(' | ', $names);
	}
	
	public static function is_cli()
	{
		if( defined('STDIN') )
		{
			return true;
		}

		if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) 
		{
			return true;
		} 

		return false;
	}
		
	public static function printargs($arg) {
		$pile = "";
		if (is_array($arg)) {
			$pile .= "[
";
			foreach ($arg as $key => $value) {
				$pile .= $key." => ".MyException::printargs($value).",";
			}
			$pile .= "],
";
		} elseif (is_object($arg)) {
			$pile .= "{
";
			foreach ($arg as $key => $value) {
				$pile .= $key." => ".MyException::printargs($value).",";
			}
			$pile .= "},
";
		} 
		else {
			$pile .= '"'.$arg.'"
';
		}
		return $pile;
	}
}