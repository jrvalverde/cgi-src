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

function db_add_entry($db, $contents)
{
    // $contents is an associative array "field" = "value"
    $query = "INSERT mut_nt VALUES ";
    $query = $query . 
    	"(\"" . addslashes($contents["location"]) . "\"" .
	",\"" . addslashes($contents["genomic"]) . "\"" .
	",\"" . addslashes($contents["cdna"]) . "\"" .
	",\"" . addslashes($contents["protein"]) . "\"" .
	",\"" . addslashes($contents["consequence"]) . "\"" .
	",\"" . addslashes($contents["type"]) . "\"" .
	",\"" . addslashes($contents["origin"]) . "\"" .
	",\"" . addslashes($contents["sample"]) . "\"" .
	",\"" . addslashes($contents["phenotype"]) . "\"" .
	",\"" . addslashes($contents["sex"]) . "\"" .
	",\"" . addslashes($contents["age_months"]) . "\"" .
	",\"" . addslashes($contents["country"]) . "\"" .
	",\"" . addslashes($contents["reference"]) . "\"" .
	",\"" . addslashes($contents["pm_id"]) . "\"" .
	",\"" . addslashes($contents["patient_id"]) . "\"" .
	",\"" . addslashes($contents["l_db"]) . "\"" .
	",\"" . addslashes($contents["remarks"]) . "\"" .
	",\"\")";   	// rbdb_accno
	
    
    echo $query."\n";

    // run the query
    if ( ! ($result = @mysql_query($query, $db)) )
    	show_error();
    
    // create a message to tell the user
    if (mysql_affected_rows() == 1)
    	echo "DB update succeeded.";
    else
    	echo "There was a problem updating. Please contact the administrator.";
}

// OK, here we go.

$debug = TRUE;

echo "<html>\n<head>\n\t<title>RBDB Update results</title>\n</head>\n";
echo "<body bgcolor=\"#ddddff\">\n";
echo "<H1>Updating RBDB</H1>";

if ($debug == TRUE) print_r($_POST);

$db = db_open($db_host, $db_user, $db_password, $db_name);

//db_lock($db);

db_add_entry($db, $_POST);

db_unlock($db);

?>
