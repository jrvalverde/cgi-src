<?php

    // Connect to db
    // Check the specified record exists (fecth it)
    // Display record to the user
    //	if confirmed, delete it by calling db_delete.php

// show the user the available data sorted by key
    
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

/** retrieve the data
 *
 * @param $db	    	an open connection to the DBMS
 * @param $where    	the field to search for
 * @param $what    	what to find
 * @return $result  	Query result 	
 */
function db_query_accno($db, $id)
{
    global $debug;
    
    // Set up a query to show what we were requested
    if (($id == "") || (! my_is_int($id)))
    	return FALSE;
    else
    	// show all
	$query = "SELECT * FROM mut_nt WHERE rbdb_acc='$id'";

#    if ($debug) echo "<h3>$query</h3>";

    // run the query
    if (! ($result = @ mysql_query($query, $db)) )
    	show_error();

    return $result;
}

/** show header for a given field
 *
 *  The header allows re-submitting the search request sorting by this
 *  field. In practice, the user sees it as a way to sort the results
 *  table by the field selected.
 */
function show_field_header($field, $description)
{
    echo "\t<th " .
	    "onMouseOver=\"showtip(this,event,'<b>$field</b>')\" " .
	    "onMouseOut=\"hidetip()\">$description" .
	 "</th>\n";
}

/** show an item as a table cell
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

/** show an item as a table cell embedded in a URL
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

function show_result($id, $result)
{
    // did we find anything?
    $count = @ mysql_num_rows($result);
    if ( $count != 0)
    {
    	// yes, show results as a table and allow to print the results
    	echo "<center><h1>This is the record you selected</h1>\n";
    	echo "<h2>Are you sure you want to delete this record?</h2></center>\n";
    	echo "<p>This is the record that you selected for deletion.</p>\n";
	echo "<p>Please, review it carefully before confirming its removal.</p>\n";
	
	// create table headings
    	echo "\n<table border=\"1\" width=\"100%\">\n";
	echo "<tr>\n";
    	    show_field_header('location', 'Location');
	    show_field_header('genomic', 'Genomic');
	    show_field_header('cdna', 'cDNA');
	    show_field_header('protein', 'Protein');
	    show_field_header('consequence', 'Consequence');
	    show_field_header('type', 'Type');
	    show_field_header('origin', 'Origin');
	    show_field_header('sample', 'Sample');
	    show_field_header('phenotype', 'Phenotype');
	    show_field_header('sex', 'Sex');
	    show_field_header('age_months', 'Age (months)');
	    show_field_header('country', 'Country');
	    show_field_header('reference', 'Reference');
	    show_field_header('pm_id', 'PubMed ID');
	    show_field_header('patient_id', 'Patient ID');
	    show_field_header('l_db', 'L-DB');
	    show_field_header('remarks', 'Remarks');
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
	    echo "</tr>\n";
	}
	echo "</table>\n";
    }
    else 
    {
    	// no data was returned by the query
	// show an appropriate message
    	err_invalid_request($id);
    }
}

function prompt_for_deletion($id)
{
    echo "<form method=\"GET\" action=\"db_delete.php?id=$id\">\n";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";
    echo "<table width=\"100%\" bgcolor=\"lightblue\" align=\"center\">\n";
    echo "<tr>\n    <td align=\"center\"><input type=\"submit\" ".
         " value=\"Definitely destroy\">&nbsp;</td>\n" .
	 "    <td align=\"center\"><input type=\"button\" name=\"Cancel\" ".
	 " value=\"Cancel\" onClick=\"history.go(-1)\"></td>\n</tr>\n";
    echo "</table></form>\n";
}

// We use a special header with the style and JavaScrip code needed to
// enhance the user experience.
function show_header()
{
    echo "<head>\n";
    echo "\t<title>RBDB Query results</title>\n";
    $showtips = file_get_contents("../js/showtips_inline.js");
    echo $showtips;
    echo "</head>\n";
}

function show_copyright()
{
    echo "\n<hr>\n" .
    	 "<table>\n<tr>\n" .
	 "<td align=\"left\">Data compilation &copy; Angel Pesta&ntilde;a, 2005</td>\n" .
	 "<td align=\"right\">Please cite (manuscript in preparation) to reference this data</td>\n" .
    	"</tr>\n</table>\n";
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

$id = escapeshellcmd($_GET["id"]);

echo "<html>\n";
show_header();
echo "<body bgcolor=\"#ccccff\">\n";
echo "<div id=\"tooltip\" style=\"position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px\"></div>\n";

if (($id == "") || (! my_is_int($id))) {
    err_invalid_request($id);
    exit;
}

$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_query_accno($db, $id);

show_result($id, $res);

prompt_for_deletion($id);

show_copyright();

// this might be show_footer()
echo "\n</body>\n</html>";
?>
