<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * show the user the available data sorted by key
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
 * @param string $db	    	an open connection to the DBMS
 * @param string $where    	the field to search for
 * @param string $what    	what to find
 * @param string $how	    	how to sort the results
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
 *  The header allows re-submitting the search request sorting by this
 *  field. In practice, the user sees it as a way to sort the results
 *  table by the field selected.
 *
 *  In order to be able to repeat the same query with a different sorting
 *  order we need what the original qeury parameters where.
 *
 *  @param string $field	The field name
 *  @param string $description	The field description
 *  @param string $where    	The search field
 *  @param string $what     	The search term
 */
function show_field_header($field, $description, $where, $what)
{
    echo "\t<th " .
	    "onMouseOver=\"showtip(this,event,'<b>Click here to sort by this field</b>')\" " .
	    "onMouseOut=\"hidetip()\"><a href=\"sorted_query.php?field=$where&query=$what&sort=$field\">$description</a>" .
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
             "onMouseOut=\"hidetip()\">".$data."&nbsp;" .
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
 * @param string   $where    	The search field
 * @param string   $what     	The search term
 * @param string   $how		How are the results sorted.
 */
function show_result($result, $where, $what, $how)
{
    // did we find anything?
    $count = @ mysql_num_rows($result);
    if ( $count != 0)
    {
    	// yes, show results as a table and allow to print the results
    	echo "<p>Your query for \"$where\"=\"$what\" returned $count results. " .
	     "<a href=\"sorted_query_pdf.php?field=$where&query=$what&sort=$how\">Save as PDF</a> " .
	     "<a href=\"sorted_query_export.php?field=$where&query=$what&sort=$how&format=csv\">Export as CSV</a> " .
	     "<a href=\"sorted_query_export.php?field=$where&query=$what&sort=$how&format=tab\">Export as Tab-delimited text</a></p>";
	
	// create table headings
    	echo "\n<table border=\"1\" width=\"100%\">\n";
	echo "<tr>\n";
    	    show_field_header('location', 'Location', $where, $what);
	    show_field_header('genomic', 'Genomic', $where, $what);
	    show_field_header('cdna', 'cDNA', $where, $what);
	    show_field_header('protein', 'Protein', $where, $what);
	    show_field_header('consequence', 'Consequence', $where, $what);
	    show_field_header('type', 'Type', $where, $what);
	    show_field_header('origin', 'Origin', $where, $what);
	    show_field_header('sample', 'Sample', $where, $what);
	    show_field_header('phenotype', 'Phenotype', $where, $what);
	    show_field_header('sex', 'Sex', $where, $what);
	    show_field_header('age_months', 'Age (months)', $where, $what);
	    show_field_header('country', 'Country', $where, $what);
	    show_field_header('reference', 'Reference', $where, $what);
	    show_field_header('pm_id', 'PubMed ID', $where, $what);
	    show_field_header('patient_id', 'Patient ID', $where, $what);
	    show_field_header('l_db', 'L-DB', $where, $what);
	    show_field_header('remarks', 'Remarks', $where, $what);
	    echo "<td><b>Edit?</b></td>\n";	// for the edit icon
	    echo "<td><b>Delete?</b></td>\n";	// for the delete icon
    	echo"</tr>\n";
	
    	// fetch each database table row of the results
	while ($row = mysql_fetch_array($result))
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
	echo "<p><center><table><tr>" .
	    "<td " .
	    "onMouseOver=\"showtip(this,event,'Click to generate a PDF report')\" " .
	    "onMouseOut=\"hidetip()\">" .
	    	"<a href=\"sorted_query_pdf.php?field=$where&query=$what&sort=$how\"><img src=\"images/pdf_logo.png\" alt=\"Save as PDF\"></a>" .
	    "</td><td " .
	    "onMouseOver=\"showtip(this,event,'Click to generate a CSV report')\" " .
	    "onMouseOut=\"hidetip()\">" .
	    "<a href=\"sorted_query_export.php?field=$where&query=$what&sort=$how&format=csv\"><img src=\"images/csv.png\" alt=\"CSV export\"></a>" .
	    "</td><td " .
	    "onMouseOver=\"showtip(this,event,'Click to generate a Tab-delimited report')\" " .
	    "onMouseOut=\"hidetip()\">" .
	    "<a href=\"sorted_query_export.php?field=$where&query=$what&sort=$how&format=tab\"><img src=\"images/tab.png\" alt=\"TAB export\"></a>" .
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

$field = escapeshellcmd($_GET["field"]);
$query = escapeshellcmd($_GET["query"]);
$sort  = escapeshellcmd($_GET["sort"]);

echo "<html>\n";
show_header();

echo "<body bgcolor=\"#ccccff\">\n";
echo "<div id=\"tooltip\" style=\"position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px\"></div>\n";
echo "<H1>Here is the result of your query</H1>\n";

echo "<h2>You asked for entries where \"$field\" contains \"$query\" sorted by \"$sort\"</h2>";

# echo "<p>db_open($db_host, $db_user, $db_password, $db_name)\n";
$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_query_sort($db, $field, $query, $sort);

show_result($res, $field, $query, $sort);

show_copyright();

show_footer();

?>
