<HTML>
<BODY>
<H1>Enviando..</H1>
<?
// $result =  mail("jrvalverde@cnb.uam.es", "the subject", $message,
//		     "Cc: jr@cnb.uam.es\r\n"
//                    ."Reply-To: root@eris.cnb.uam.es\r\n"
//                    ."X-Mailer: PHP/" . phpversion());

  $result = mail("jr@embnet.cnb.uam.es", "php WWW", "mensaje");
  
 
  if ($result) {
	echo "OK";
  } else {
	echo "not OK";
  }
?>
