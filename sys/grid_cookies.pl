#!/usr/local/bin/perl -w
# ic_cookies - sample CGI script that uses a cookie to get/set user ID data
use CGI qw(:standard);

use Crypt::CBC;

use strict;

# We have three cases:
#	1) The user does not have a valid cookie
#		We need to send a form to ask for his data
#	2) The user does not have a valid cookie but has filled the form
#		Save the data on a cookie and say welcome
#	3) The user has a valid cookie
#		Retrieve user data and procceed

my $key = "15024480201502448021"; # this is unsafe, but will have to do for now
my $cookie_name = "grid-gui";

# Get data from the form
#   ensure username is a single word
my @fuwords = split('\b', param("user"));
my $form_user = $fuwords[0];
my $form_pass = param("password");
my $form_passphrase = param("passphrase");

my $cipher = Crypt::CBC->new( -key => $key,
					     -cipher => 'Blowfish'
					   );
my @uinfo;
if (cookie($cookie_name)) {
  @uinfo = split('\n', $cipher->decrypt(cookie($cookie_name)));
}

# note: a password and passphrase may contain blanks, but not
# the username which must be a single word (anything after a
# blanks on it might be later mistaken as an extra parameter)
my @uwords;
if ($uinfo[0]) {
  @uwords = split('\b', $uinfo[0]);
}
my $ui_user    = $uwords[0] || 'guest';
my $ui_pass   = $uinfo[1] || 'secret';
my $grid_passp = $uinfo[2] || 'very long secret phrase';

# if we didn't get anything from the form then we need to create it
unless ($form_user) {
    print header(), start_html("User ID"), h1("Please, identify yourself"),
          hr(), 
	  start_form(),
            p("Please enter your username on the UI: ", 
              textfield("user",$ui_user)),
            p("Please enter your password on the UI: ", 
              password_field("password",$ui_pass)),
            p("Please enter your passphrase for the Grid: ", 
              password_field("passphrase", $grid_passp)),
            submit(-NAME => "Go ahead!", -VALUE => shift),
          end_form(), 
	  hr();
    exit;
}

# if the cookie has already been set print its values (and do whatever we must)
if (cookie($cookie_name)) {
    print header(), start_html("Welcome"),
      	h1("Welcome to this site"),
	p("The cookie value was ".cookie($cookie_name)),
	p("Which once decrypted is ".$cipher->decrypt(cookie($cookie_name))),
      	p("Your saved username is `$ui_user'."),
      	p("Your saved password is `$ui_pass'."),
      	p("Your saved passphrase is `$grid_passp'.");
    exit;
}

# if the cookie hasn't been set, this is the first time we are called
# after the form is filled in and sent: print the form data and set
# the cookie and then activate the grid so it is ready for subsequent
# invokations

# We set the cookie using '\n' as this is a character we know for sure is
# not allowed in usernames nor passwords/passphrases. Furthermore,
# should we get a value tweaked to include it, it will result in an additional
# line and since we'll only use the first three lines anyhow, any exceeding 
# data will be ignored.
# We need to set the domain since we are on an HA balanced cluster and
# we don't know which machine will be accessed next (and will request
# the cookie).
my $cook = cookie(
                -NAME    => $cookie_name,
                -VALUE   => $cipher->encrypt($form_user."\n".$form_pass."\n".$form_passphrase."\n01234567890123456789"),
                -EXPIRES => "+2h",
		-DOMAIN => "cnb.uam.es"
            );

print header(-COOKIE => $cook),
      start_html("Welcome"),
      h1("Welcome to this site"),
      p("We have saved your details for later"),
      p("You chose as your username `$form_user'."),
      p("You chose as your password `$form_pass'."),
      p("You chose as your passphrase `$form_passphrase'.");

    # OK, time to activate the Grid:
    my $h = "villon.cnb.uam.es";
    my $vo = "biomed";
    open (GRID_UI, "|./SSH.sh $ui_user\@$h > .out 2>&1");
    print GRID_UI <<END_PROXY_CMD;
$ui_pass
voms-proxy-init --voms=$vo
$grid_passp
exit
END_PROXY_CMD
    close(GRID_UI);
