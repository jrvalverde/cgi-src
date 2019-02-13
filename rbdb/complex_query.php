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
 * retrieve the data
 *
 * Build a complex query and submit it to the database, requesting that
 * results be sorted by a given field.
 *
 * @param resource $db	    	an open connection to the DBMS
 * @param array  $request    	the query to run against the database
 * @param string $how		how to sort results
 * @return mixed $result  	Query result 	
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
 *  Show header for a given field
 *
 *  The header allows re-submitting the search request sorting by this
 *  field. In practice, the user sees it as a way to sort the results
 *  table by the field selected.
 *
 *  @param string $field	The field name
 *  @param string $description	The field description
 *  @param array  $q		An associative array containing the query
 */
function show_field_header($field, $description, $q)
{
    echo "\t<th " .
	    "onMouseOver=\"showtip(this,event,'<b>Click here to sort by this field</b>')\" " .
	    "onMouseOut=\"hidetip()\"><a href=\"complex_query.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$field\">$description</a>" .
	 "</th>\n";
}

/** 
 * show an item as a table cell
 *
 * The field contents are formatted as a table cell, with provision for
 * display of additional information as Javascript content.
 *
 *  @param $data    contents to display
 *  @param $tip     tip to associate to the contents
 */
function show_field($data, $tip)
{
echo "\t<td " .
         "onMouseOver=\"showtip(this,event,'$tip')\" " .
         "onMouseOut=\"hidetip()\">". $data ."&nbsp;" .
     "</td>\n";
}

/**
 * show an item as a table cell embedded in a URL
 *
 * The field contents are formatted as a table cell, with provision for
 * display of additional information as Javascript content. Contents are
 * formatted as a hyperlink, embedding the same contents in the specified
 * URL.
 *
 * i.e.
 *  <td><a href="$preurl$data$posturl">$data</a></td>
 * e.g.
 *  <td><a href="http://somesite/get?MyData">MyData</a></td>
 *
 *  @param $data    contents to display
 *  @param $tip     tip to associate to the contents
 *  @param $preurl  URL to embed the data in (prologue before $data)
 *  @param $posturl URL to embed the data in (epilogue after $data)
 */
function show_field_url($data, $tip, $preurl, $posturl)
{
echo "\t<td " .
    	"onMouseOver=\"showtip(this,event,'$tip')\" " .
    	"onMouseOut=\"hidetip()\">" .
    	"<a href=\"".$preurl.$data.$posturl."\">" .
    	"$data</a>&nbsp;" .
     "</td>\n";

}

/**
 * Show results of the query
 *
 *	This function will tell the user how many results did we get, and
 * build a nice table containing appropriate headers (allowing to sort by
 * that field) and the data (with adequate tips or additional information).
 *
 * @param resource $result	The result obtained after running our query
 * @param array $q		An associative array containing the original
 *				query
 * @param string $how		How are the results sorted.
 */
function show_result($result, $q, $how)
{
    // did we find anything?
    $count = @ mysql_num_rows($result);
    if ( $count != 0)
    {
    	// yes, show results as a table and allow to print the results
    	echo "<p>Your query returned $count results. " .
	     "<a href=\"complex_query_pdf.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$how\">Save as PDF</a> " .
	    "<a href=\"complex_query_export.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$how&format=csv\">Save as CSV</a> " .
	    "<a href=\"complex_query_export.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$how&format=tab\">Save as Tab-delimited text</a></p>";
	
	// create table headings
    	echo "\n<table border=\"1\" width=\"100%\">\n";
	echo "<tr>\n";
    	    show_field_header('location', 'Location', $q);
	    show_field_header('genomic', 'Genomic', $q);
	    show_field_header('cdna', 'cDNA', $q);
	    show_field_header('protein', 'Protein', $q);
	    show_field_header('consequence', 'Consequence', $q);
	    show_field_header('type', 'Type', $q);
	    show_field_header('origin', 'Origin', $q);
	    show_field_header('sample', 'Sample', $q);
	    show_field_header('phenotype', 'Phenotype', $q);
	    show_field_header('sex', 'Sex', $q);
	    show_field_header('age_months', 'Age (months)', $q);
	    show_field_header('country', 'Country', $q);
	    show_field_header('reference', 'Reference', $q);
	    show_field_header('pm_id', 'PubMed ID', $q);
	    show_field_header('patient_id', 'Patient ID', $q);
	    show_field_header('l_db', 'L-DB', $q);
	    show_field_header('remarks', 'Remarks', $q);
	    echo "<td><b>Edit?</b></td>\n";	// for the edit icon
	    echo "<td><b>Delete?</b></td>\n";	// for the delete icon
    	echo"</tr>\n";
	
    	// fetch each database table row of the results
	while ($row = @ mysql_fetch_array($result))
	{
	    // display the data as a table row
	    echo "<tr>\n";
		show_field($row["location"], '<b>Location:</b><br />Exon (E) and intron (I) number<br />according to cDNA sequence<br />NCBI (NM_000321.1)');
		show_field($row["genomic"], '<b>Genomic:</b><br />Description follows the recommendations<br />published by Donnan and Antonarakis (2000)<br />using the genomic sequence<br />GenBank: L11910.1');
		show_field($row["cdna"],'<b>cDNA:</b><br />changes as in Donnen and Antonarakis (2000),<br />using the cDNA sequence NCBI: NM_000321.1.');
    		show_field($row["protein"], '<b>Protein:</b><br />Deduced changes at the protein level<br />follow the recommendations of Dunnen and Antonarakis (2000)<br />using the protein sequence NCBI: NP_000312.1.');
		show_field($row["consequence"], '<b>Consequence:</b><br />predicted consequences are as follows:<br /><ul><li>regulation (promoter)</li><li>FS (trunckating frameshift)</li><li>IF (non-truncating inframe changes)</li><li>MS (missense changes)</li><li>NS (non-sense trunckating mutations)</li><li>SP (trunckating mutations affecting splicing sites)</li><li>SP-IF (in frame exon deletion due to splicing mutations)</li><li>SP-MS (mutations affecting the last two nucleotides in exon can either be considered as  MS or splicing mutations).</li></ul>');
		show_field($row["type"], '<b>Type of mutation:</b><ul><li>DUP (duplication)</li><li>IN (insertion)</li><li>DE (deletion)</li><li>I_D (complex insertion and deletion)</li><li>PM (point mutation)</li></ul>');
		show_field($row["origin"], '<b>Origin:</b><br />Germline or somatic.');
		show_field($row["sample"], '<b>Sample:</b><ul><li>PB (peripheral blood for germline)</li><li>retino (retinoblastoma)</li><li>other (other  tumors)</li></ul>');
		show_field($row["phenotype"], '<b>Phenotype:</b><ul><li>B (sporadic bilateral)</li><li>BF (bilateral familiar)</li><li>U (sporadic unilateral)</li><li>UF (unilateral familiar)</li><li>UMF (unilateral multifocal)</li><li>LP (familiar with low penetrance)</li></ul>');
		show_field($row["sex"], '<b>Sex:</b><ul><li>F (female)</li><li>M (male)</li></ul>');
		show_field($row["age_months"], '<b>Age (months):</b><br />at diagnosis or treatment in months.');
		show_field($row["country"], '<b>Country:</b><br />of origin of probands or <br />of the main research group<br />in publications.');
		show_field($row["reference"], '<b>Reference</b>');
		show_field_url($row["pm_id"], '<b>PubMed ID:</b><br />Click to retrieve the abstract',
	    	    	       "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=PubMed&list_uids=",
			       "&dopt=Abstract");
		show_field($row["patient_id"], '<b>Patient ID:</b><br />as reported in publication.');
		show_field($row["l_db"], '<b>L-DB</b>');
		show_field($row["remarks"], '<b>Remarks:</b><br />any observation which can be useful in the context of a given mutation');
	    	echo "\t<td " .
            	    "onMouseOver=\"showtip(this,event,'Click to edit this record')\" " .
            	    "onMouseOut=\"hidetip()\">
		      <a href=\"update/edit_record.php?id=".$row['rbdb_acc']."\">
		        <img src=\"images/edit.png\">" .
		      "</a>" .
     	    	    "</td>\n";
	    	echo "\t<td " .
            	    "onMouseOver=\"showtip(this,event,'Click to delete this record')\" " .
            	    "onMouseOut=\"hidetip()\">
		      <a href=\"update/delete_record.php?id=".$row['rbdb_acc']."\">
		        <img src=\"images/trash.png\">" .
		      "</a>" .
     	    	    "</td>\n";

	    echo "</tr>\n";
	}
	echo "</table>\n";
	echo "<p><center><table><tr>".
	    "<td " .
	    "onMouseOver=\"showtip(this,event,'Click to generate a PDF report')\" " .
	    "onMouseOut=\"hidetip()\">" .
	    "<a href=\"sorted_query_pdf.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$how\"><img src=\"images/pdf_logo.png\" alt=\"Save as PDF\"></a></center></p>" .
	    "</td><td " .
	    "onMouseOver=\"showtip(this,event,'Click to generate a CSV report')\" " .
	    "onMouseOut=\"hidetip()\">" .
	    "<a href=\"sorted_query_export.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$how&format=csv\"><img src=\"images/csv.png\" alt=\"Save as PDF\"></a></center></p>" .
	    "</td><td " .
	    "onMouseOver=\"showtip(this,event,'Click to generate a Tab-delimited report')\" " .
	    "onMouseOut=\"hidetip()\">" .
	    "<a href=\"sorted_query_export.php?" .
	    "combine1={$q[0]['op']}&field1={$q[0]['fld']}&query1={$q[0]['qry']}&" . 
	    "combine2={$q[1]['op']}&field2={$q[1]['fld']}&query2={$q[1]['qry']}&" . 
	    "combine3={$q[2]['op']}&field3={$q[2]['fld']}&query3={$q[2]['qry']}&" . 
	    "combine4={$q[3]['op']}&field4={$q[3]['fld']}&query4={$q[3]['qry']}&" . 
	    "sort=$how&format=tab\"><img src=\"images/tab.png\" alt=\"Save as PDF\"></a></center></p>" .
	    "</td></tr></table></center></p>";
    }
    else 
    {
    	// no data was returned by the query
	// show an appropriate message
	echo "<h3><font color=\"red\">Your query returned no results</font></h3>";
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

echo "<html>\n";
show_header();
echo "<body bgcolor=\"#ccccff\">\n";
echo "<div id=\"tooltip\" style=\"position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px\"></div>\n";
echo "<H1>Here is the result of your query</H1>\n";

echo "<h2>Your query was</h2>";
echo "<table border=\"1\">" .
     "<tr><th>Combine</th><th>field</th><th>contains</th></tr>" .
     "<tr><td>$combine1&nbsp;</td><td>$field1</td><td>$query1&nbsp;</td></tr>" .
     "<tr><td>$combine2</td><td>$field2</td><td>$query2&nbsp;</td></tr>" .
     "<tr><td>$combine3</td><td>$field3</td><td>$query3&nbsp;</td></tr>" .
     "<tr><td>$combine4</td><td>$field4</td><td>$query4&nbsp;</td></tr>" .
     "</table>";

$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_complex_query_sort($db, $q, $sort);

show_result($res, $q, $sort);

show_copyright();

show_footer();
?>
