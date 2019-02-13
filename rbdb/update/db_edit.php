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

function db_edit_entry($db, $contents)
{
    global $debug;
    
    // $contents is an associative array "field" = "value"
    $query = "UPDATE mut_nt SET ";
    $query = $query . 
    	"location=\"" . addslashes($contents["location"]) . "\"" .
	", genomic=\"" . addslashes($contents["genomic"]) . "\"" .
	", cdna=\"" . addslashes($contents["cdna"]) . "\"" .
	", protein=\"" . addslashes($contents["protein"]) . "\"" .
	", consequence=\"" . addslashes($contents["consequence"]) . "\"" .
	", type=\"" . addslashes($contents["type"]) . "\"" .
	", origin=\"" . addslashes($contents["origin"]) . "\"" .
	", sample=\"" . addslashes($contents["sample"]) . "\"" .
	", phenotype=\"" . addslashes($contents["phenotype"]) . "\"" .
	", sex=\"" . addslashes($contents["sex"]) . "\"" .
	", age_months=\"" . addslashes($contents["age_months"]) . "\"" .
	", country=\"" . addslashes($contents["country"]) . "\"" .
	", reference=\"" . addslashes($contents["reference"]) . "\"" .
	", pmid=\"" . addslashes($contents["pm_id"]) . "\"" .
	", patient_id=\"" . addslashes($contents["patient_id"]) . "\"" .
	", l_db=\"" . addslashes($contents["l_db"]) . "\"" .
	", remarks=\"" . addslashes($contents["remarks"]) . "\"" .
	" where rbdb_acc='".$contents["id"]."'";   	// rbdb_accno
	
    
    echo $query."\n";
    if ($debug == TRUE) exit;
    // run the query
    if ( ! ($result = @mysql_query($query, $db)) )
    	show_error();
    
    // create a message to tell the user
    if (mysql_affected_rows() == 1)
    	echo "DB update succeeded.";
    else
    	echo "There was a problem updating. Please contact the administrator.";
}


function err_invalid_request($id)
{
    echo "<h3><font color=\"red\">You supplied an invalid record ID ($id) for deletion</font></h3>";
    exit;
}

function my_is_int($x) {
   return (is_numeric($x) ? intval($x) == $x : false);
}

// Here we go
// Check Record ID

$debug=TRUE;

$id = escapeshellcmd($_GET["id"]);

if (($id == "") || (! my_is_int($id))) {
    err_invalid_request($id);
}

echo "<html>\n<head>\n\t<title>RBDB edit results</title>\n</head>\n";
echo "<body bgcolor=\"#ddddff\">\n";
echo "<H1>Modifying RBDB record $id</H1>";

if ($debug == TRUE) print_r($_GET);

$db = db_open($db_host, $db_user, $db_password, $db_name);

//db_lock($db);

db_edit_entry($db, $_GET);

db_unlock($db);

?>
