<?php
// show the user the available data in PDF format

// include fPDF tools
include('mysql_report.php');

// include DBMS credentials
include 'config.inc';
include 'utils.php';

/** retrieve the data
 *
 * @param $db	    	an open connection to the DBMS
 * @param $where    	the field to search for
 * @param $what    	what to find
 * @return $result  	Query result 	
 */
function db_query_pdf($db_host, $db_user, $db_password, $db_name, $where, $what)
{
    // Set up a query to show what we were requested
    if ( ($where == "") || ($what == ""))
    	// show all
    	$query = "SELECT * from mut_nt";
    else if ( $what == "NULL")
    	// NULL requires a special syntax
	$query = "SELECT * from mut_nt where $where IS NULL";
    else
    	// find entries CONTAINING requested text
    	$query = "SELECT * from mut_nt where $where like '%$what%'";
    
    // run the query
    $pdf = new PDF('L','pt','A3');
    $pdf->SetFont('Arial','',10);
    $pdf->AliasNbPages();
    $pdf->connect($db_host, $db_user, $db_password, $db_name);
    $attr=array('titleFontSize'=>18,'titleText'=> 'RBDB results for query \''.$what.'\' in \''.$where.'\'');
    $pdf->mysql_report($query, false, $attr);
    
    return;
}

// Here we go

$field = $_POST["field"];
$query = $_POST["query"];

// show all
db_query_pdf($db_host, $db_user, $db_password, $db_name, $field, $query);

?>
