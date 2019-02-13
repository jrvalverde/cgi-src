<?php
// show the user the available data
    
// include DBMS credentials
include 'config.inc';
include 'utils.php';


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
function db_query($db, $where, $what)
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
function show_field_header($field, $description, $where, $what)
{
    echo "\t<th " .
	    "onMouseOver=\"showtip(this,event,'<b>Click here to sort by this field</b>')\" " .
	    "onMouseOut=\"hidetip()\"><a href=\"sorted_query.php?field=$where&query=$what&sort=$field\">$description</a>" .
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
         "onMouseOut=\"hidetip()\">$data&nbsp;" .
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


function show_result($result, $where, $what)
{

    // did we find anything?
    $count = @ mysql_num_rows($result);
    echo "<p>Your query for \"$where\"=\"$what\" returned $count results</p>\n";
    echo "\n<table border=\"1\" width=\"100%\">\n";
    if ( $count != 0)
    {
    	// yes, show results as a table
	
	// create table headings
	echo "<tr>\n";
    	    show_field_header('site', 'Location', $where, $what);
	    show_field_header('genomic', 'Genomic', $where, $what);
	    show_field_header('no', 'No.', $where, $what);
	    show_field_header('cdna', 'cDNA', $where, $what);
	    show_field_header('protein', 'Protein', $where, $what);
	    show_field_header('consequence', 'Consequence', $where, $what);
	    show_field_header('type', 'Type', $where, $what);
	    show_field_header('origin', 'Origin', $where, $what);
	    show_field_header('sample', 'Sample', $where, $what);
	    show_field_header('pheno', 'Phenotype', $where, $what);
	    show_field_header('sex', 'Sex', $where, $what);
	    show_field_header('lp','LP', $where, $what);
	    show_field_header('aged_mo', 'Aged (mo)', $where, $what);
	    show_field_header('country', 'Country', $where, $what);
	    show_field_header('reference', 'Reference', $where, $what);
	    show_field_header('pm_id', 'PubMed ID', $where, $what);
	    show_field_header('patient_id', 'Patient ID', $where, $what);
	    show_field_header('l_db', 'L-DB', $where, $what);
	    show_field_header('remarks', 'Remarks', $where, $what);
    	echo"</tr>\n";

    	// fetch each database table row of the results
	while ($row = @ mysql_fetch_array($result))
	{
	    // display the data as a table row
	    echo "<tr>\n";
		show_field($row["site"], '<b>Location:</b><br />Exon (E) and intron (I) number<br />according to cDNA sequence<br />NCBI (NM_000321.1)');
		show_field($row["genomic"], '<b>Genomic:</b><br />Description follows the recommendations<br />published by Donnan and Antonarakis (2000)<br />using the genomic sequence<br />GenBank: L11910.1');
		show_field($row["no"], '<b>No.</b>');
		show_field($row["cdna"],'<b>cDNA:</b><br />changes as in Donnen and Antonarakis (2000),<br />using the cDNA sequence NCBI: NM_000321.1.');
    		show_field($row["protein"], '<b>Protein:</b><br />Deduced changes at the protein level<br />follow the recommendations of Dunnen and Antonarakis (2000)<br />using the protein sequence NCBI: NP_000312.1.');
		show_field($row["consequence"], '<b>Consequence:</b><br />predicted consequences are as follows:<br /><ul><li>regulation (promoter)</li><li>FS (trunckating frameshift)</li><li>IF (non-truncating inframe changes)</li><li>MS (missense changes)</li><li>NS (non-sense trunckating mutations)</li><li>SP (trunckating mutations affecting splicing sites)</li><li>SP-IF (in frame exon deletion due to splicing mutations)</li><li>SP-MS (mutations affecting the last two nucleotides in exon can either be considered as  MS or splicing mutations).</li></ul>');
		show_field($row["type"], '<b>Type of mutation:</b><ul><li>DUP (duplication)</li><li>IN (insertion)</li><li>DE (deletion)</li><li>I_D (complex insertion and deletion)</li><li>PM (point mutation)</li></ul>');
		show_field($row["origin"], '<b>Origin:</b><br />Germline or somatic.');
		show_field($row["sample"], '<b>Sample:</b><ul><li>PB (peripheral blood for germline)</li><li>retino (retinoblastoma)</li><li>other (other  tumors)</li></ul>');
		show_field($row["pheno"], '<b>Phenotype:</b><ul><li>B (sporadic bilateral)</li><li>BF (bilateral familiar)</li><li>U (sporadic unilateral)</li><li>UF (unilateral familiar)</li><li>UMF (unilateral multifocal)</li><li>LP (familiar with low penetrance)</li></ul>');
		show_field($row["sex"], '<b>Sex:</b><ul><li>F (female)</li><li>M (male)</li></ul>');
		show_field($row["lp"], '<b>LP</b>');
		show_field($row["aged_mo"], '<b>Aged (mo):</b><br />at diagnosis or treatment in months.');
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
	echo "<h3><font color=\"red\">Your query returned no results</font></h3>";
    }
}

// We use a special header with the style and JavaScrip code needed to
// enhance the user experience.
function show_header()
{
    echo "<head>\n";
    echo "\t<title>RBDB Query results</title>\n";
    $showtips = file_get_contents("showtips_inline.js");
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

// Here we go

$field = $_POST["field"];
$query = $_POST["query"];

echo "<html>\n";
show_header();
echo "<body bgcolor=\"#ddddff\">\n";
echo "<div id=\"tooltip\" style=\"position:absolute;visibility:hidden;border:1px solid black;font-size:12px;layer-background-color:lightyellow;background-color:lightyellow;padding:1px\"></div>\n";
echo "<H1>Here is the result of your query</H1>\n";

$db = db_open($db_host, $db_user, $db_password, $db_name);

// show all
$res = db_query($db, $field, $query);

show_result($res, $field, $query);

show_copyright();

// this might be show_footer()
echo "\n</body>\n</html>";
?>
