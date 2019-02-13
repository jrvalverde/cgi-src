#!/usr/local/bin/perl -w
# ic_cookies - sample CGI script that uses a cookie to get/set user ID data
use CGI qw(:standard);

use Crypt::CBC;

use strict;

# We need MD5 to generate the key from provided data.
use Digest::MD5 qw(md5);
#$digest = md5($data);


# Set cookie name
my $cookie_name = "grid-gui";

# Set name of traslucent database
my $userbd = "/data/www/EMBnet/cgi-bin/sys/userdb";

# UI host
my $h = "villon.cnb.uam.es";
# VO to use
my $vo = "biomed";

my $data;
my $key;
my $cookie;
my $ui_user;
my $ui_pass;
my $grid_passp;

# We have three cases:
#	1) The user does not have a valid cookie
#		We need to send a form to ask for his data
#	2) The user does not have a valid cookie but has filled the form
#		Save the data on a cookie and say welcome
#	3) The user has a valid cookie
#		Retrieve user data and procceed


# check presence of the cookie and grab value
if (cookie($cookie_name)) {
# if the cookie has already been set print its values (and do whatever we must)
     my @values;
     @values = split(':',cookie($cookie_name));
     my $user = $values[0];
     $key = $values[1];	# MD5 HEX of user data used as encryption key

    # Extract user data from traslucent database.
    ###
    my $user_data;
    if (! get_user_data($user, $user_data)) {
	# user not found.
	# notify, delete cookie and log in again
	&get_auth_data;
	exit;
    }

    # Create cypher object
    my $cipher = Crypt::CBC->new( -key => $key,
			           -cipher => 'Blowfish'
			   	);
    # decrypt user data
    $data = $cipher->decrypt_hex($user_data);
    # verify data has been correctly decrypted
    my $cksum = md5($data);
    if ($cksum ne $key) {
	# someone is trying to fool us or the user data
	# has changed. Ask to authenticate again
        &get_auth_data;
	exit;
    }

    # Process encrypted user_data
    my @uinfo;
    my @uwords;

    @uinfo = split('\n', $data);
    if ($uinfo[0]) {
    	# note: a password and passphrase may contain blanks, but not
    	# the username which must be a single word (anything after a
    	# blanks on it might be later mistaken as an extra parameter)
  	@uwords = split('\b', $uinfo[0]);
    }
    else {
	# something's gone wrong. Ask to reauthenticate
	&authenticate;
	exit;
    }
    $ui_user = $uwords[0] || &get_auth_data;
    $ui_pass   = $uinfo[1] || &get_auth_data;
    $grid_passp = $uinfo[2] || &get_auth_data;

    # for debugging only
    print header(), start_html("Welcome"),
      	h1("Welcome to this site"),
	p("The cookie value was ".cookie($cookie_name)),
	p("Which once decrypted is <pre>".$cipher->decrypt(cookie($cookie_name)))."</pre>",
      	p("Your saved username is `$ui_user'."),
      	p("Your saved password is `$ui_pass'."),
      	p("Your saved passphrase is `$grid_passp'.");
	# do work here
    exit;
}

# if there is no cookie (may be it expired), check whether we are called to print a new
# form ot if we are being called with form data.

# Try to get the data from the form
#	ensure username is a single word
my @fuwords = split('\b', param("user"));
my $ui_user = $fuwords[0];
my $ui_pass = param("password");
my $grid_passp = param("passphrase");

# if we didn't get any username from the form then we need to print out the form
unless ($ui_user) {
	&get_auth_data;
	exit;		# never reached
}


# if the cookie hasn't been set and we are being called after the form 
# is filled in and sent: print a welcome message (e.g. form data) and set
# the cookie and then activate the grid so it is ready for subsequent
# invocations

# OK, time to activate the Grid:
#open (GRID_UI, "|./SSH.sh $ui_user\@$h > .out 2>&1");
open (GRID_UI, 
    "| ssh -x -t -t $ui_user\@$h /opt/glite/bin/voms-proxy-init --voms=$vo > .out 2>&1");

print GRID_UI <<END_PROXY_CMD;
$ui_pass
$grid_passp;
exit
END_PROXY_CMD

if (close(GRID_UI) != 0)
	&exit_wrong_id;

# Note: we should check grid activation succeeded and tell the user.

# We set the cookie using '\n' as this is a character we know for sure is
# not allowed in usernames nor passwords/passphrases. Furthermore,
# should we get a value tweaked to include it, it will result in an additional
# line and since we'll only use the first three lines anyhow, any exceeding 
# data will be ignored.
# We need to set the domain since we are on an HA balanced cluster and
# we don't know which machine will be accessed next (and will request
# the cookie).
$data = $form_user."\n".$form_pass."\n".$form_passphrase."\n(C) Jose R. Valverde, 2007";

$key = md5($data);

# store the key on the cookie
my $cookie = cookie(
                -NAME    => $cookie_name,
		-VALUE => $key,
                -EXPIRES => "+2h",
		-DOMAIN => "cnb.uam.es"
            );

# insert encrypted data into database
# Create cypher object
my $cipher = Crypt::CBC->new( -key => $key,
			      -cipher => 'Blowfish'
			   );

my $db_entry = $form.user.':'$cipher->encrypt_hex($form_user."\n".$form_pass."\n".$form_passphrase."\n01234567890123456789");

save_user_data($db_entry);

print header(-COOKIE => $cook),
      start_html("Welcome"),
      h1("Welcome to this site"),
      p("We have saved your details for later"),
      p("You chose as your username `$ui_user'."),
      p("You chose as your password `$ui_pass'."),
      p("You chose as your passphrase `$grid_passp'.");




#-----------
#
#	get_user_data ($user, $user_data)
sub get_user_data
{
    my $user = $_[0];
    my $found = 0;

    # Extract user data from traslucent database.
    #
    open (DB, "<$userdb");
    while (<DB>) {
	next if /^#/;			# ignore comment lines
	next unless $_ =~ /^$user:/;	# check if user found
	@fields = split(":", $_);	# split into user:cyphertext
	$_[1] = $fields[1];
	$found = 1;
	last;
    }
    close(DB);
    # we should check the user was found and if not delete existing
    # cookie and print message to log in again. XXX JR XXX
    return $found;
}

# ask for user authentication
# NOTE should use styles.
sub get_auth_data {
    print header(), start_html("User ID"), h1("Please, identify yourself"),
          hr(), 
	  start_form(),
            p("Please enter your username on the UI: ", 
              textfield("user", "guest")),
            p("Please enter your password on the UI: ", 
              password_field("password", "secret")),
            p("Please enter your passphrase for the Grid: ", 
              password_field("passphrase", "very long secret passphrase")),
            submit(-NAME => "Go ahead!", -VALUE => shift),
          end_form(), 
	  hr();
    exit;
}