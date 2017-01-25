<?php
                function custom_mail($to, $subject, $message, $additional_headers, $additional_parameters){
		    $headers = explode("\n",$additional_headers);
#		    $to = 'andrey@fmf.ru';
		    if(LOCAL_MAIL_DISK_ENABLE===true){
			disk_custom_mail($to, $subject, $message, $additional_headers);
		    }
		    if(LOCAL_MAIL_SMTP_ENABLE===false)return false;

                    require_once(realpath(dirname(__FILE__)."/phpmailer/PHPMailerAutoload.php"));
                    $mail = new PHPMailer;


		    if(LOCAL_MAIL_SMPT_LOG_ENABLE){
                	$mail->SMTPDebug = 2;
    			$sMonth = date("m");    $sDay = date("d");  $sYear = date("Y"); 
    			$sHour = date("Y");     $sMin = date("i");  $sSec = date("s");
    			$sBaseDir = LOCAL_MAIL_SMTP_LOG_BASEDIR;
    			$arPath = array(
        		    "$sYear-$sMonth",
			    "$sYear-$sMonth-$sDay",
        		    "$sTo"
    			);
    			$sFilename = "$sYear-$sMonth-$sDay-$sHour-$sMin-$sSec-$sTo.txt";
    			$sRelPath = '';
    			foreach($arPath as $sFolder){
        		    $sRelPath .="/".$sFolder;
        		    $sFullPath = $sBaseDir.$sRelPath;
        		    if(!is_dir($sFullPath)){
            			if(!mkdir($sFullPath))return false;
        		    }
    			}
                define("LOCAL_MAIL_SMTP_LOG_FILENAME",$sFullPath."/".$sFilename);
                $mail->Debugoutput = function($str,$level){
                    $fd = fopen(LOCAL_MAIL_SMTP_LOG_FILENAME,"a");
                    fwrite($fd,"$str");
                    fclose($fd);
                };
		    }
		
		    $contentType = "";
		    foreach($headers as $k=>$v){
			$v = trim($v);
			if(preg_match("/^FROM/i",$v)){
			    unset($headers[$k]);
			    continue;
			}
			list($name,$value) = explode(":",$v);
			$name = trim($name);
			$value= trim($value);

			if(preg_match("/^content\-type/i",$name)){
			    $mail->ContentType = $value;
			    continue;
			}
			$mail->addCustomHeader($name,$value);
		    }


                    $mail->isSMTP();
                    $mail->CharSet  = 'UTF-8';
                    $mail->setLanguage('ru');
                      // Set mailer to use SMTP
                    $mail->Host = LOCAL_MAIL_SMTP_HOST;  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = LOCAL_MAIL_SMTP_AUTH; // Enable SMTP authentication
                    $mail->Username = LOCAL_MAIL_SMTP_USER; // SMTP username
                    $mail->Password = LOCAL_MAIL_SMTP_PASS; // SMTP password
                    $mail->SMTPSecure = LOCAL_MAIL_SMTP_SECU; // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = LOCAL_MAIL_SMTP_PORT;
                    $mail->From = LOCAL_MAIL_SMTP_FROM;
                    $mail->FromName = LOCAL_MAIL_SMTP_FROM_NAME;
                    $mail->Subject = $subject;
                    $mail->Body    = $message;
                    $mail->addAddress($to);
                    if(!$mail->send()) {
                        echo $mail->ErrorInfo;
			return false;
                    }
                    $mail->clearAddresses();
                    $mail->ClearCustomHeaders();
                    return true;
                }
