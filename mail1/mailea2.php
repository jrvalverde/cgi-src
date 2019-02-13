<?
include("Mail.php");

$recipients = "jrvalverde@cnb.uam.es";

$headers["From"]    = "www@eris.cnb.uam.es";
$headers["To"]      = "txomsy@cnb.uam.es";
$headers["Subject"] = "Test message";

$body = "TEST MESSAGE!!!";

$params["host"] = "cnb.uam.es";
$params["port"] = "25";
$params["auth"] = true;
//$params["username"] = "user";
//$params["password"] = "password";

// Create the mail object using the Mail::factory method
$mail_object =& Mail::factory("smtp", $params);

$mail_object->send($recipients, $headers, $body);
?>
