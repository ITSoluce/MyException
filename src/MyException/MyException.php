<?php 
namespace MyException;

use ErrorException;

class MyException
{
	const LogError = false;
	const ScreenError = true;
	const MailError = false;
	const FatalErrorMail = "";
	const ProjetName = "";
	
	function __construct()
	{
		// Afficher les erreurs et les avertissements
		//error_reporting(E_ALL);
		set_error_handler( array( MyException::class, "log_error" ) ); 
		set_exception_handler( array( MyException::class, "log_exception" ) );
		register_shutdown_function( array( MyException::class, "check_for_fatal" ) );
		
		global $MyExceptionLogError;
		global $MyExceptionScreenError;
		global $MyExceptionMailError;
		global $MyExceptionFatalErrorMail;
		global $MyExceptionProjetName;
		
		if (!empty($MyExceptionLogError))
		{
			define(MyException::LogError, $MyExceptionLogError);
		}
		if (!empty($MyExceptionScreenError))
		{
			echo "ICI";
			define(self::ScreenError, $MyExceptionScreenError);
		}
		if (!empty($MyExceptionMailError))
		{
			define(MyException::MailError, $MyExceptionMailError);
		}
		if (!empty($MyExceptionFatalErrorMail))
		{
			define(MyException::FatalErrorMail, $MyExceptionFatalErrorMail);
		}
		if (!empty($MyExceptionProjetName))
		{
			define(MyException::ProjetName, $MyExceptionProjetName);
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
		$Value = 0;
		// a remplacer en fonction
		
		if (method_exists($e, 'getSeverity')) {
			$Value        = $e->getSeverity();
			$NiveauErreur = self::friendly_severity($Value);

		} else {
			$NiveauErreur = "SQL_ERROR";
			$Value        = -1;
		}
		//On ne traite pas les notices :
		if (true || (($Value != 8) && ($Value != 8192))) {
			$erreur = "<div style='text-align: left;'>";
			$erreur .= "<p style='color: rgb(190, 50, 50);'>Probléme informatique - Une exception a été levée :</p>";
			$erreur .= "<table style='display: inline-block;'>";
			$erreur .= "<tr style='background-color:rgb(240,240,240);'><th style='width: 80px;'>Type</th><td style='background-color:rgb(230,230,230);'>".$NiveauErreur." (".$Value.")</td></tr>";
			$erreur .= "<tr style='background-color:rgb(240,240,240);'><th>Date Heure</th><td style='background-color:rgb(230,230,230);'>".date("Y-m-d H:i:s")."</td></tr>";
			$erreur .= "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td style='background-color:rgb(230,230,230);'>";
			if ($StringError != "") {
				$erreur .= htmlentities($StringError)."<br>";
			}
			$erreur  .= $e->getMessage();
			$erreur  .= "</td></tr>";
			$erreur  .= "<tr style='background-color:rgb(240,240,240);'><th>Fichier</th><td style='background-color:rgb(230,230,230);'>";
			$tableau = $e->getTrace();
			//debug($tableau);
			$pile   = '';
			$count  = 0;
			$count2 = 0;
			$erreur  .=  "{$e->getFile()}:{$e->getLine()}</td></tr><tr><td>Pile</td><td>\n";
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
					/*
					  if () {
					  $pile .= " <b>Classe</b> ".$value["class"]."\r\n";
					  } */
					$compteur = 0;
					if (isset($value["args"])) {
						foreach ($value["args"] as $key2 => $value2) {
							$compteur ++;
							$pile .= " <b>Argument ".$compteur."</b> ";
							if (is_array($value2)) {
								foreach ($value2 as $key3 => $value3) {
									$pile .= "[".$key3."] => ";
									if (is_array($value3)) {
										foreach ($value3 as $key4 => $value4) {
											$pile .= "[".$key4."] => ".$value4." / ";
										}
									} else {
										if (!is_object($value3)) {
											$pile .= $value3;
										} else {
											$pile .= json_encode($value3);
										}
									}
									$pile .= " / ";
								}
							} else {
								if (!is_object($value2)) {
									$pile .= $value2;
								} else {
									$pile .= json_encode($value2);
								}
							}
							$pile .= "\r\n";
						}
					}
					
				}
			}
			ini_set('log_errors_max_len', 0);
			if (MyException::LogError) {
				error_log($pile);
				error_log("\n");
			}
			$erreur .= str_replace("\n", "<br>", $pile);
			$erreur .= "</td></tr>";
			$erreur .= "</table></div>";

			if (MyException::ScreenError) {
				echo $erreur;
			}
			if (MyException::MailError) {
	// To send HTML mail, the Content-type header must be set
				$headers[] = 'MIME-Version: 1.0';
				$headers[] = 'Content-type: text/html; charset=UTF-8';
	// Additional headers
				$headers[] = 'To: Renaud PLATEL <'.MyException::FatalErrorMail.'>';
				$headers[] = 'From: '.MyException::ProjetName.' <'.MyException::FatalErrorMail.'>';
				mail(MyException::FatalErrorMail, "Erreur ".MyException::ProjetName, $erreur, implode("\r\n", $headers));
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
	
	/*
	function World() {
		echo "hello";
	}
	*/
}