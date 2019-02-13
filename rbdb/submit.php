<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * submit a new database entry
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
 * Submit a new entry to the database maintainer
 *
 *  This function will send an e-mail to the database maintainer containing a
 * user-submitted data entry, and notify the submitting user of the result.
 *
 *  The user entry contents have been retrieved from a web form that has been
 * filled by the user. We are assuming that the contents are OK.
 *
 *  @param  string $maintainer	The e-mail address of the database maintainer
 *  @param  array  $contents	An associative array containing the entry contents
 *  	    	    	    	as strings associated with their corresponding fields.
 */
function submit_new_entry($maintainer, $contents)
{
    global $debug;
    
    $subject = "RBGMDB submission request";
    // $contents is an associative array "field" = "value"
    $message = addslashes($contents["email"])." has submitted the following information \n" .
    	"for inclusion in RBGMDB. Please check it and act accordingly.\n\n";
    $message = $message . 
    	"Location     = \"" . addslashes($contents["location"])    . "\"\n" .
	"Genomic      = \"" . addslashes($contents["genomic"])     . "\"\n" .
	"cDNA         = \"" . addslashes($contents["cdna"])        . "\"\n" .
	"Protein      = \"" . addslashes($contents["protein"])     . "\"\n" .
	"Consequence  = \"" . addslashes($contents["consequence"]) . "\"\n" .
	"Type         = \"" . addslashes($contents["type"])        . "\"\n" .
	"Origin       = \"" . addslashes($contents["origin"])      . "\"\n" .
	"Sample       = \"" . addslashes($contents["sample"])      . "\"\n" .
	"Phenotype    = \"" . addslashes($contents["phenotype"])   . "\"\n" .
	"Sex          = \"" . addslashes($contents["sex"])         . "\"\n" .
	"Age (months) = \"" . addslashes($contents["age_months"])  . "\"\n" .
	"Country      = \"" . addslashes($contents["country"])     . "\"\n" .
	"Reference    = \"" . addslashes($contents["reference"])   . "\"\n" .
	"PubMed ID    = \"" . addslashes($contents["pm_id"])       . "\"\n" .
	"Patient ID   = \"" . addslashes($contents["patient_id"])  . "\"\n" .
	"L-DB         = \"" . addslashes($contents["l_db"])        . "\"\n" .
	"Remarks      = \"" . addslashes($contents["remarks"])     . "\"\n" .
	"\n" ;
    
    // Prepare and send administrator version of the message
    $adm_message = $message . 	
    	"For security reasons perhaps you should send this message back\n" .
	"to the submitter with a request for confirmation (that, or you\n" .
	"do check the data with the appropriate PubMed reference).\n";
    	"\n" .
    	"You may either include this info in your own local copy and later use\n" .
	"\n\thttps://www.es.embnet.org/Services/rbdb/rbdb-update.html\n" .
	"\nto make a full update, or directly enter the new entry using the\n" .
	"\n\thttps://www.es.embnet.org/Services/rbdb/rbdb-add.html\n" .
	"\nweb form.\n";

    $adm_message = wordwrap($adm_message, 70);
    
    mail($maintainer, $subject, $adm_message);
    
    // prepare and send user version of the message
    $user_message = "The following message has been sent to the RBDB maintainer\n" .
    	"$maintainer on your behalf. You should be contacted \n" .
	"soon to confirm the validity of this data.\n\n" .
	"Until then, no further action is needed on your part.\n\n" .
	"----------------------------------------------------\n" .
	$message; 
    $user_message = wordwrap($user_message, 70);
    mail(addslashes($contents["email"]), $subject, $user_message);

    echo "<h2>The following message has been sent to the RBDB database ".
    	 "maintainer on your behalf:</h2>";
    echo "<center><table width=\"90%\" border=\"1\" bgcolor=\"white\">\n<tr><td>"; 
	echo "<pre>";
	echo "To: $maintainer\n";
	echo "Subject: $subject\n";
	echo $message;
	echo "</pre>";
    echo "</td></tr>\n</table></center>\n";
    echo "<h2>Please, allow some time for the coordinator to get back in " .
    	 "contact with you. If you feel it takes too long, please, feel " .
	 "free to contact us directly</h2>";
    
    return TRUE;
}


echo "<html>\n<head>\n\t<title>RBGMDB Update results</title>\n</head>\n";
echo "<body bgcolor=\"#ddddff\">\n";
echo "<H1>Submitting your data to RBGMDB maintainer</H1>";

$debug = FALSE;
if ($debug) {
    echo "<pre>"; print_r($_POST); echo "</pre>";
}

// validate incoming e-mail address (the rest will be validated by the
// database maintainer(s).
$email = trim($_POST['email']);  
if(!check_email($email)) { 
    echo '<h2>Invalid email address!</h2>';
    echo '<p>We have detected a problem with your e-mail address. ' .
    	 'Please, go back to the form and verify that the data you '.
	 'entered was correct and try again.</p>';
	 '<p>If you feel this to be an error (e.g. the e-mail address '.
	 'you entered is valid), please contact us.</p>';
}
else {
    if (!submit_new_entry($maintainer, $_POST)) {
    	show_error("There was a problem submitting your data, please check it and try again");
    }
}

show_copyright();

show_footer();

?>
