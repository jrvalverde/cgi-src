<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * export to the user the available data sorted by key in a
 *  selected interchange format.
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
 * retrieve the data
 *
 *  This function will retrieve the user data from the database using
 * a simple search criterium (i.e. only one field is searched) and
 * sorted by a given field.
 *
 * @param $db	    	an open connection to the DBMS
 * @param $where    	the field to search for
 * @param $what    	what to find
 * @param $how	    	how to sort the results
 *
 * @return $result  	Query result 
 */
function db_query_sort($db, $where, $what, $how)
{
    // Set up a query to show what we were requested
    if ( ($where == "") || ($what == ""))
    	// show all
	$query = "SELECT * FROM mut_nt";
    else if ( $what == "NULL")
    	// NULL requires a special syntax
	$query = "SELECT * FROM mut_nt WHERE $where IS NULL";
    else
    	// find entries CONTAINING requested text
    	$query = "SELECT * FROM mut_nt WHERE $where like '%$what%'";
    
    if ($how != "")
    	$query .= " ORDER BY $how";

#    echo "<h3>$query</h3>";

    // run the query
    if (! ($result = @ mysql_query($query, $db)) )
    	show_error();

    return $result;
}

/**
 * show header for a given field
 *
 *  The header produces the field name in a form suitable for
 * export. In other words, it outputs the field name and appends
 * a delimiter according to the format specified.
 *
 *  @param string $field	The field name
 *  @param string $format   	The format to use
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
 *  @param string $data    contents to display
 *  @param string $format     the format to use
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
 *  Show query results.
 *
 *  Generate a report with the query results formatted according
 * to an export format specified.
 *
 *  @param mixed $result      the results of the query
 *  @param string $format     the format to use
 */
function show_result($result, $format)
{
    // did we find anything?
    $count = @ mysql_num_rows($result);
    // we want to produce the report in a specific field order,
    //	  hence the explicit enumeration of fields. Otherwise, we
    //	  might as well use foreach ($row as $field => $value)
    //    loops.
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

$field = escapeshellcmd($_GET["field"]);
$query = escapeshellcmd($_GET["query"]);
$sort  = escapeshellcmd($_GET["sort"]);
$format  = escapeshellcmd($_GET["format"]);

show_export_header($format);

$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_query_sort($db, $field, $query, $sort);

show_result($res, $format);

?>
