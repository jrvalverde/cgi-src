#!/bin/sh

exec 2>&1

### CSV ###
# Correct the uploaded file:
#   Fix some horrible CSV files (common format/edition errors).
# We do this in order to avoid parsing the CSV file (which we 
# will probably have to do sooner or later).

# For OpenOffice/StarOffice CSV
#	1. Empty fields are not enclosed in "
#	2. If there are two adjacent empty fields, the preceding command
#	only matches the first, so we repeat it.
#	3. Common user error: Yes is not always spelled the same
#	4. Add an empty field at the end (for acc_no)
#cat RBDB.csv | \
#    sed -e 's/,,/,\"\",/g' | \
#    sed -e 's/,,/,\"\",/g' | \
#    sed -e 's/\"YES\"/\"Yes\"/g' | \
#    sed -e 's/\"yes\"/\"Yes\"/g' | \
#    sed -e 's/$/,\"\"/g' > RBDB.ok

# For Excel we have a more serious problem: it uses ';' instead of ','
# (beats me MS calls this *Comma* Separated Value). Now, we can't just
# substitute all ';' by a ','. Furthermore, their use of " is not
# coherent either...

### TAB ###
#   Tab works OK out of the box. We'll use it instead of CSV.

if [ -x /opt/mysql/bin/mysql ] ; then
    MYSQL='/opt/mysql/bin/mysql'
elif [ -x /usr/local/bin/mysql ] ; then
    MYSQL='/usr/local/bin/mysql'
elif [ -x /usr/freeware/bin/mysql ] ; then
    MYSQL='/usr/freeware/bin/mysql'
else
    MYSQL='/usr/bin/mysql'
fi

#echo "<H1>$4</H1>"
#echo $MYSQL --password=$3 --user=$2 --host=$1 --batch --local-infile=1
$MYSQL --password=$3 --user=$2 --host=$1 --batch --local-infile=1 <<END
use rbdb;
drop table mut_nt;
create table mut_nt (
    location    	varchar(255),
    genomic 	varchar(255),
    cdna    	varchar(255),
    protein 	varchar(255),
    consequence varchar(255),
    type    	varchar(255),
    origin  	varchar(255) default "n.r.",
    sample  	varchar(255),
    phenotype  	varchar(255) default "n.r.",
    sex     	enum('M', 'F'),
    age_months 	int,
    country 	varchar(255),
    reference   varchar(255),
    pm_id    	varchar(255),
    patient_id  varchar(255) default "n.r.",
    l_db    	enum('Yes', 'No') default "No",
    remarks     text,
    rbdb_acc	int not null auto_increment primary key
    );
load data local infile '$4' into table mut_nt 
#    fields terminated by ',' enclosed by '"'
#    lines terminated by '\n'
    ignore 1 lines;

END

echo "done."
