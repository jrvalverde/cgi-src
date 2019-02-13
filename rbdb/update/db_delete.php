<?php
// update database contents
    
// include DBMS credentials
include '../config.inc';
include '../utils.php';

function db_open($host, $user, $password, $db)
{
    if (! ($connection = @ mysql_connect($host, $user, $password)))
    	show_error();
	
    if (! mysql_select_db($db, $connection))
    	show_error();
    
    return $connection;
}

function db_lock($db)
{
    $query = "LOCK TABLES mut_nt WRITE";
    if ( ! ($result = mysql_query($query, $db)) )
    	show_error();
}

function db_unlock($db)
{
    $query = "UNLOCK TABLES";
    if ( ! ($result = mysql_query($query, $db)) )
    	show_error();
}

function db_delete_entry($db, $id)
{
    if (($id == "") || (! my_is_int($id))) {
    	err_invalid_request($id);
    }
    // $contents is an associative array "field" = "value"
    $query = "DELETE FROM mut_nt WHERE rbdb_acc='$id'";
	    
    echo $query."\n";

    // run the query
    if ( ! ($result = @mysql_query($query, $db)) )
    	show_error();
    
    // create a message to tell the user
    if (mysql_affected_rows() == 1)
    	echo "<h3>DB delete succeeded.</h3>";
    else
    	echo "<h3><font color=\"red\">There was a problem deleting this record. ".
	     "Please contact the database administrator.</font>";
}

function err_invalid_request($id)
{
    echo "<h3><font color=\"red\">You supplied an invalid record ID ($id) for deletion</font></h3>";
    exit;
}

function my_is_int($x) {
   return (is_numeric($x) ? intval($x) == $x : false);
}

$id = escapeshellcmd($_GET["id"]);

if (($id == "") || (! my_is_int($id))) {
    err_invalid_request($id);
}

echo "<html>\n<head>\n\t<title>RBDB Delete results</title>\n</head>\n";
echo "<body bgcolor=\"#ddddff\">\n";
echo "<H1>Deleting record $id from RBDB</H1>";

$db = db_open($db_host, $db_user, $db_password, $db_name);

//db_lock($db);

db_delete_entry($db, $id);

db_unlock($db);

?>
