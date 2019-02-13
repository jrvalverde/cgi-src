<?
	// Retrieve URL from params
	// If not, return a Web page with usage description
	// Use CURL to get PDB file contents
	// if not, output error
	//
	// (C) EMBnet/CNB 13/05/2003
	//	See http://www.es.embnet.org/Copyright-CSIC.html
	//
	// AUTHOR: José R. Valverde (EMBnet/CNB) jrvalverde@es.embnet.org
	//
	// $Id: babel.php,v 1.1.1.3 2005/01/21 14:35:22 netadmin Exp $
	// $Log: babel.php,v $
	// Revision 1.1.1.3  2005/01/21 14:35:22  netadmin
	// Added WS to pdb2vrml1
	//
	// Revision 1.1.1.2  2005/01/20 16:56:44  netadmin
	// Added support for Web Services using nuSOAP
	//
	// Revision 1.1  2003/05/13 14:04:20  root
	// Initial revision
	//
	
/////////////////////// GLOBAL VARIABLES //////////////////////////////////
	
// Location of the user data and result files:
// they should be WWW accessible, therefore we need
// to specify their location relative to the system /
// and to the www root (for the URLs):
$system_tmproot="/data/www/EMBnet/tmp";
$http_tmproot="/tmp";

// program locations
$babel_home="/opt/structure/babel";

// set to 0 for no debug output, or select a debug level
$debug=0;


////////////////////////////////// START /////////////////////////////////

    if ( $debug == 1 ) echo "<HTML><BODY><H1>babel</H1>";

    // be nice: provide our own source code to others
    $dnld=$_GET["download"];
    if (isset($dnld)) {
	header("Content-type: text/plain");
	header("Content-Disposition: inline; filename=babel.php");
	readfile("./babel.php");
	exit;
    }

    // select a random name for the tmp dir and cd to it
    $random_str = rand(1000000, 9999999);
    set_tmp_dir( $random_str, $system_tmproot );

    // get format options
    $iformat=$_GET["iformat"];
    $oformat=$_GET["oformat"];
    if ( (! isset($iformat)) || (! isset($oformat))) {
    	usage();
	echo "<P>Please specify format options</P></BODY></HTML>";
	exit;
    }

    // get URL to convert into "./structure.in"
    if ( $debug == 1 ) echo "<P>Getting remote URL</P>";
    get_URL($debug);
    
    if ( do_conversion($babel_home, $iformat, $oformat) ) 
    {
    	// Change content-type to x-world/x-vrml and send world
	header("Content-type: chemical/x-$oformat");
	header("Content-Disposition: inline; filename=structure.$oformat");
	readfile("./structure.out");
    }
    else 
    {
    	// produce an error message
	echo "<h1>Error... Babel could not carry out the conversion</h1>";
    }
    
    //cleanup($random_str, $debug);
    
    exit;
    	
/////////////////////////////// SUBROUTINES ////////////////////////////////

//
//Creating the tmp directory
//
function set_tmp_dir( $random_str, $system_tmproot )
{   	
	mkdir ("$system_tmproot/babel$random_str", 0755);
	chdir( "$system_tmproot/babel$random_str" ); 
}

//
// Get remote URL to process
//  	It comes in a WWW variable url=remote_url
//
function get_URL($debug)
{   
    // Retrieve URL value
    $url = $_GET["url"];
        
    if ( ! isset($url) ) 
    {
    	// if not set print usage notice
	usage();
	echo "<BR><P>Please, submit the URL of the PDB file to process.</P>\n</BODY>\n</HTML>";
    	exit;
    }
    if ( $debug == 1 ) echo "<H1>Called with URL=$url</H1>";
    
    // Retrieve URL contents into "structure.pdb" in current dir
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_URL, $url);

    $fp = fopen("./structure.in", "w");
    
    curl_setopt ($ch, CURLOPT_FILE, $fp);
    curl_setopt ($ch, CURLOPT_HEADER, 0);

    curl_exec ($ch);
    curl_close ($ch);
    fclose ($fp);

    if ($error = curl_error($ch)) 
    {
    	usage();
    	echo "<BR><P>Error ($error): can't retrieve the PDB file</P>";
	exit;
    }
    
    if ( $debug == 1 ) 
    {
    	// Show what we got
    	echo "<PRE>";
	readfile("structure.out");
	echo "</PRE>";
    }
    
}

function usage()
{
    echo "<CENTER><H1>Babel</H1></CENTER>";
    echo "<P><STRONG>Babel</STRONG>: convert a molecular structure file between formats</P>";
    echo "<P><STRONG>Usage:</STRONG> babel.php?url=url/path/to/file.pdb&iformat=input_format&oformat=output_format</P>";
    echo "<P>Valid formats are:</P>";
    echo "<CENTER><TABLE CELLSPACING=\"4\">";
    echo "<TR><TD ALIGN=\"top\"><TABLE BORDER=\"2\"<TR><TD COLSPAN=\"2\"><CENTER><STRONG>Input formats</STRONG></CENTER></TD></TR>";
    echo "<TR><TD>prep</TD><TD>AMBER prep file</TD></TR>";
    echo "<TR><TD>bs</TD><TD>Ball and Stick file</TD></TR>";
    echo "<TR><TD>bgf</TD><TD>MSI BGF file</TD></TR>";
    echo "<TR><TD>car</TD><TD>Biosym .CAR file</TD></TR>";
    echo "<TR><TD>boog</TD><TD>Boogie file</TD></TR>";
    echo "<TR><TD>caccrt</TD><TD>Cacao Cartesian file</TD></TR>";
    echo "<TR><TD>cadpac</TD><TD>Cambridge CADPAC file</TD></TR>";
    echo "<TR><TD>charmm</TD><TD>CHARMm file</TD></TR>";
    echo "<TR><TD>c3d1</TD><TD>Chem3D Cartesian 1 file</TD></TR>";
    echo "<TR><TD>c3d2</TD><TD>Chem3D Cartesian 2 file</TD></TR>";
    echo "<TR><TD>cssr</TD><TD>CSD CSSR file</TD></TR>";
    echo "<TR><TD>fdat</TD><TD>CSD FDAT file</TD></TR>";
    echo "<TR><TD>gstat</TD><TD>CSD GSTAT file</TD></TR>";
    echo "<TR><TD>dock</TD><TD>Dock Database file</TD></TR>";
    echo "<TR><TD>dpdb</TD><TD>Dock PDB file</TD></TR>";
    echo "<TR><TD>feat</TD><TD>Feature file</TD></TR>";
    echo "<TR><TD>fract</TD><TD>Free Form Fractional file</TD></TR>";
    echo "<TR><TD>gamout</TD><TD>GAMESS Output file</TD></TR>";
    echo "<TR><TD>gzmat</TD><TD>Gaussian Z-Matrix file</TD></TR>";
    echo "<TR><TD>gauout</TD><TD>Gaussian 92 Output file</TD></TR>";
    echo "<TR><TD>g94</TD><TD>Gaussian 94 Output file</TD></TR>";
    echo "<TR><TD>gr96A</TD><TD>GROMOS96 (A) file</TD></TR>";
    echo "<TR><TD>gr96N</TD><TD>GROMOS96 (nm) file</TD></TR>";
    echo "<TR><TD>hin</TD><TD>Hyperchem HIN file</TD></TR>";
    echo "<TR><TD>sdf</TD><TD>MDL Isis SDF file</TD></TR>";
    echo "<TR><TD>m3d</TD><TD>M3D file</TD></TR>";
    echo "<TR><TD>macmol</TD><TD>Mac Molecule file</TD></TR>";
    echo "<TR><TD>macmod</TD><TD>Macromodel file</TD></TR>";
    echo "<TR><TD>micro</TD><TD>Micro World file</TD></TR>";
    echo "<TR><TD>mm2in</TD><TD>MM2 Input file</TD></TR>";
    echo "<TR><TD>mm2out</TD><TD>MM2 Output file</TD></TR>";
    echo "<TR><TD>mm3</TD><TD>MM3 file</TD></TR>";
    echo "<TR><TD>mmads</TD><TD>MMADS file</TD></TR>";
    echo "<TR><TD>mdl</TD><TD>MDL MOLfile file</TD></TR>";
    echo "<TR><TD>molen</TD><TD>MOLIN file</TD></TR>";
    echo "<TR><TD>mopcrt</TD><TD>Mopac Cartesian file</TD></TR>";
    echo "<TR><TD>mopint</TD><TD>Mopac Internal file</TD></TR>";
    echo "<TR><TD>mopout</TD><TD>Mopac Output file</TD></TR>";
    echo "<TR><TD>pcmod</TD><TD>PC Model file</TD></TR>";
    echo "<TR><TD>pdb</TD><TD>PDB file</TD></TR>";
    echo "<TR><TD>psin</TD><TD>PS-GVB Input file</TD></TR>";
    echo "<TR><TD>psout</TD><TD>PS-GVB Output file</TD></TR>";
    echo "<TR><TD>msf</TD><TD>Quanta MSF file</TD></TR>";
    echo "<TR><TD>schakal</TD><TD>Schakal file</TD></TR>";
    echo "<TR><TD>shelx</TD><TD>ShelX file</TD></TR>";
    echo "<TR><TD>smiles</TD><TD>SMILES file</TD></TR>";
    echo "<TR><TD>spar</TD><TD>Spartan file</TD></TR>";
    echo "<TR><TD>semi</TD><TD>Spartan Semi-Empirical file</TD></TR>";
    echo "<TR><TD>spmm</TD><TD>Spartan Mol. Mechanics file</TD></TR>";
    echo "<TR><TD>mol</TD><TD>Sybyl Mol file</TD></TR>";
    echo "<TR><TD>mol2</TD><TD>Sybyl Mol2 file</TD></TR>";
    echo "<TR><TD>wiz</TD><TD>Conjure file</TD></TR>";
    echo "<TR><TD>unixyz</TD><TD>UniChem XYZ file</TD></TR>";
    echo "<TR><TD>xyz</TD><TD>XYZ file</TD></TR>";
    echo "<TR><TD>xed</TD><TD>XED file</TD></TR>";
    echo "</TABLE></TD>";
    
    echo "<TD ALIGN=\"top\"><TABLE BORDER=\"2\"<TR><TD COLSPAN=\"2\"><CENTER><STRONG>Output formats</STRONG></CENTER></TD></TR>";
    echo "<TR><TD>diag</TD><TD>DIAGNOTICS file</TD></TR>";
    echo "<TR><TD>alc</TD><TD>Alchemy file</TD></TR>";
    echo "<TR><TD>bs</TD><TD>Ball and Stick file</TD></TR>";
    echo "<TR><TD>bgf</TD><TD>BGF file</TD></TR>";
    echo "<TR><TD>bmin</TD><TD>Batchmin Command file</TD></TR>";
    echo "<TR><TD>box</TD><TD>DOCK 3.5 box file</TD></TR>";
    echo "<TR><TD>caccrt</TD><TD>Cacao Cartesian file</TD></TR>";
    echo "<TR><TD>cacint</TD><TD>Cacao Internal file</TD></TR>";
    echo "<TR><TD>cache</TD><TD>CAChe MolStruct file</TD></TR>";
    echo "<TR><TD>c3d1</TD><TD>Chem3D Cartesian 1 file</TD></TR>";
    echo "<TR><TD>c3d2</TD><TD>Chem3D Cartesian 2 file</TD></TR>";
    echo "<TR><TD>cdct</TD><TD>ChemDraw Conn. Table file</TD></TR>";
    echo "<TR><TD>dock</TD><TD>Dock Database file</TD></TR>";
    echo "<TR><TD>wiz</TD><TD>Wizard file</TD></TR>";
    echo "<TR><TD>contmp</TD><TD>Conjure Template file</TD></TR>";
    echo "<TR><TD>cssr</TD><TD>CSD CSSR file</TD></TR>";
    echo "<TR><TD>dpdb</TD><TD>Dock PDB file</TD></TR>";
    echo "<TR><TD>feat</TD><TD>Feature file</TD></TR>";
    echo "<TR><TD>fhz</TD><TD>Fenske-Hall ZMatrix file</TD></TR>";
    echo "<TR><TD>gamin</TD><TD>Gamess Input file</TD></TR>";
    echo "<TR><TD>gcart</TD><TD>Gaussian Cartesian file</TD></TR>";
    echo "<TR><TD>gzmat</TD><TD>Gaussian Z-matrix file</TD></TR>";
    echo "<TR><TD>gotmp</TD><TD>Gaussian Z-matrix tmplt file</TD></TR>";
    echo "<TR><TD>gr96A</TD><TD>GROMOS96 (A) file</TD></TR>";
    echo "<TR><TD>gr96N</TD><TD>GROMOS96 (nm) file</TD></TR>";
    echo "<TR><TD>hin</TD><TD>Hyperchem HIN file</TD></TR>";
    echo "<TR><TD>icon</TD><TD>Icon 8 file</TD></TR>";
    echo "<TR><TD>idatm</TD><TD>IDATM file</TD></TR>";
    echo "<TR><TD>sdf</TD><TD>MDL Isis SDF file</TD></TR>";
    echo "<TR><TD>m3d</TD><TD>M3D file</TD></TR>";
    echo "<TR><TD>macmol</TD><TD>Mac Molecule file</TD></TR>";
    echo "<TR><TD>macmod</TD><TD>Macromodel file</TD></TR>";
    echo "<TR><TD>micro</TD><TD>Micro World file</TD></TR>";
    echo "<TR><TD>mm2in</TD><TD>MM2 Input file</TD></TR>";
    echo "<TR><TD>mm2out</TD><TD>MM2 Ouput file</TD></TR>";
    echo "<TR><TD>mm3</TD><TD>MM3 file</TD></TR>";
    echo "<TR><TD>mmads</TD><TD>MMADS file</TD></TR>";
    echo "<TR><TD>mdl</TD><TD>MDL Molfile file</TD></TR>";
    echo "<TR><TD>miv</TD><TD>MolInventor file</TD></TR>";
    echo "<TR><TD>mopcrt</TD><TD>Mopac Cartesian file</TD></TR>";
    echo "<TR><TD>mopint</TD><TD>Mopac Internal file</TD></TR>";
    echo "<TR><TD>csr</TD><TD>MSI Quanta CSR file</TD></TR>";
    echo "<TR><TD>pcmod</TD><TD>PC Model file</TD></TR>";
    echo "<TR><TD>pdb</TD><TD>PDB file</TD></TR>";
    echo "<TR><TD>psz</TD><TD>PS-GVB Z-Matrix file</TD></TR>";
    echo "<TR><TD>psc</TD><TD>PS-GVB Cartesian file</TD></TR>";
    echo "<TR><TD>report</TD><TD>Report file</TD></TR>";
    echo "<TR><TD>smiles</TD><TD>SMILES file</TD></TR>";
    echo "<TR><TD>spar</TD><TD>Spartan file</TD></TR>";
    echo "<TR><TD>mol</TD><TD>Sybyl Mol file</TD></TR>";
    echo "<TR><TD>mol2</TD><TD>Sybyl Mol2 file</TD></TR>";
    echo "<TR><TD>maccs</TD><TD>MDL Maccs file</TD></TR>";
    echo "<TR><TD>torlist</TD><TD>Torsion List file</TD></TR>";
    echo "<TR><TD>tinker</TD><TD>Tinker XYZ file</TD></TR>";
    echo "<TR><TD>unixyz</TD><TD>UniChem XYZ file</TD></TR>";
    echo "<TR><TD>xyz</TD><TD>XYZ file</TD></TR>";
    echo "<TR><TD>xed</TD><TD>XED file</TD></TR>";
    echo "</TABLE></TD></TR>";
    echo "</TABLE></CENTER>";
    echo "<BR><P>Returned file will be of mime type chemical/x-output_format</P>";
//    echo "<BR><P><STRONG>NOTE:</STRONG> you may use \"babel.php?download\" to dowload this servlet</P>";
}

function do_conversion($babel_home, $iformat, $oformat)
{
    putenv("BABEL_DIR=$babel_home");
    
    // check for errors and return true or false appropriately
    exec("$babel_home/babel -i$iformat structure.in -o$oformat structure.out", $str_output, $str_var);
    if ( $str_var==0 )
    {
	return true;
    }else
    {
    	return false;
    }
}

function cleanup($random_str, $debug)
{
    if ( $debug == 1 ) return;
    
    unlink("structure.in");
    unlink("structure.out");
    
    chdir("../");
    rmdir("$system_tmproot/babel$random_str");
}

?>
