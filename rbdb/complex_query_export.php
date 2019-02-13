<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * show the user the available data sorted by key
 *
 * I know this approach is ugly and that I should rather use an associative
 * array to pass the parameters, but...
 *
 *  PHP version 4 and up
 *
 * LICENSE:
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package	RBGMDB
 * @author 	José R. Valverde <jrvalverde@acm.org>
 * @copyright 	José R. Valverde <jrvalverde@acm.org>
 * @license	c/lgpl.txt
 * @version	$Id$
 * @link	http://www.es.embnet.org/Services/MolBio/rbgmdb/
 * @see		utils.php config.inc
 * @since	File available since Release 1.0
 */

/**
 * include DBMS credentials
 */
include 'config.inc';
/**
 * include utility functions
 */
include 'utils.php';

/**
 * Open a connection to a MySQL database
 *
 * @param string $host	The host serving the MySQL databases
 * @param string $user	The user name to connect as
 * @param string $passowrd The password associated to the user
 * @param string $db	The name of the database to open
 */
function db_open($host, $user, $password, $db)
{
    if (! ($connection = @ mysql_connect($host, $user, $password)))
    	show_error();
	
    if (! mysql_select_db($db, $connection))
    	show_error();
    
    return $connection;
}

/** 
 * Retrieve the data
 *
 * Build a complex query and submit it to the database, requesting that
 * results be sorted by a given field.
 *
 * @param string $db	    	an open connection to the DBMS
 * @param array  $query    	the query issued
 * @param string $how   	how to sort results
 *
 * @return $result  	Query result 	
 */
function db_complex_query_sort($db, $request, $how)
{
    // Set up a query to show what we were requested
    $query = "SELECT * FROM mut_nt";
    
    $first_cond = 0;
    foreach ($request as $cond) {
    	// check if this condition states something to search
    	if (($cond['fld'] != "") && ($cond['qry'] != "")) {
	    // first valid condition is special
	    if ($first_cond == 0) {
		$first_cond = 1;
	    	$query .= " WHERE ";
		if ($cond['op'] == "NOT")
		    // ignore AND and OR on the first valid condition
		    $query .= " {$cond['op']} ";
	    }
	    else
	    	$query .= " {$cond['op']} ";
	    
	    if ($cond['qry'] == "NULL")
	    	// NULL requires a special syntax
	        $query .= " {$cond['fld']} IS NULL ";
	    else
	        $query .= " {$cond['fld']} like '%{$cond['qry']}%'";
	}
    }
    if ($how != "")
    	$query .= " ORDER BY $how";

#    echo "<h3>$query</h3>";
    
    // run the query
    if (! ($result = @ mysql_query($query, $db)) )
    	show_error();

    return $result;
}

/** 
 *  show header for a given field
 *
 *  The header produces the field name in a form suitable for
 * export. In other words, it outputs the field name and appends
 * a delimiter according to the format specified.
 *
 *  @param string $field	The field name
 *  @param string $format	The format to be used for output
 */
function show_field_header($field, $format)
{
    if ($format == "csv")
    	echo "\"$field\",";
    else if ($format == "tab")
    	echo "$field\t";
    else
    	return;
}

/**
 * show an item as a table cell
 *
 * The field contents are output as text and formatted according
 * to the export format specified (i.e. the appropriate delimiter
 * is added around the field contents).
 *
 *  @param $data    contents to display
 *  @param $format  the format to use for output data
 */
function show_field($data, $format)
{
    if ($format == "csv")
    	echo "\"$data\",";
    else if ($format == "tab")
    	echo str_replace("\t", "\\\t", $data) ."\t";
    else
    	return;
}

/**
 * output the result of the query in the specified format
 *
 *  Generates a report with the query results formatted according
 * to an export format specified.
 *
 * @param resource $result
 * @param resource $format
 */
function show_result($result, $format)
{
    // did we find anything?
    $count = @ mysql_num_rows($result);
    if ( $count != 0)
    {
    	show_field_header('location', $format);
	show_field_header('genomic', $format);
	show_field_header('cdna', $format);
	show_field_header('protein', $format);
	show_field_header('consequence', $format);
	show_field_header('type', $format);
	show_field_header('origin', $format);
	show_field_header('sample', $format);
	show_field_header('phenotype', $format);
	show_field_header('sex', $format);
	show_field_header('age_months', $format);
	show_field_header('country', $format);
	show_field_header('reference', $format);
	show_field_header('pm_id', $format);
	show_field_header('patient_id', $format);
	show_field_header('l_db', $format);
	show_field_header('remarks', $format);
    	echo"\n";
	
    	// fetch each database table row of the results
	while ($row = mysql_fetch_array($result))
	{
	    // display the data as a table row
	    show_field($row["location"], $format);
	    show_field($row["genomic"], $format);
	    show_field($row["cdna"], $format);
    	    show_field($row["protein"], $format);
	    show_field($row["consequence"], $format);
	    show_field($row["type"], $format);
	    show_field($row["origin"], $format);
	    show_field($row["sample"], $format);
	    show_field($row["phenotype"], $format);
	    show_field($row["sex"], $format);
	    show_field($row["age_months"], $format);
	    show_field($row["country"], $format);
	    show_field($row["reference"], $format);
	    show_field($row["pm_id"], $format);
	    show_field($row["patient_id"], $format);
	    show_field($row["l_db"], $format);
	    show_field($row["remarks"], $format);
	    echo "\n";
	}
    }
    else 
    {
    	// no data was returned by the query
	// show an appropriate message
	echo "Your query returned no results\n";
    }
}

// Here we go

$combine1 = escapeshellcmd($_GET["combine1"]);
$field1 = escapeshellcmd($_GET["field1"]);
$query1 = escapeshellcmd($_GET["query1"]);

$combine2 = escapeshellcmd($_GET["combine2"]);
$field2 = escapeshellcmd($_GET["field2"]);
$query2 = escapeshellcmd($_GET["query2"]);

$combine3 = escapeshellcmd($_GET["combine3"]);
$field3 = escapeshellcmd($_GET["field3"]);
$query3 = escapeshellcmd($_GET["query3"]);

$combine4 = escapeshellcmd($_GET["combine4"]);
$field4 = escapeshellcmd($_GET["field4"]);
$query4 = escapeshellcmd($_GET["query4"]);

// bundle all together
//	might be done directly, but would be less evident.
$q = array(
	array('op' => $combine1, 'fld' => $field1, 'qry' => $query1),
	array('op' => $combine2, 'fld' => $field2, 'qry' => $query2),
	array('op' => $combine3, 'fld' => $field3, 'qry' => $query3),
	array('op' => $combine4, 'fld' => $field4, 'qry' => $query4)
);

$sort  = escapeshellcmd($_GET["sort"]);

$format  = escapeshellcmd($_GET["format"]);

show_header($format);

$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_complex_query_sort($db, $q, $sort);

show_result($res, $format);

?>
