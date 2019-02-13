#!/bin/sh
#
#$Id: gramm.sh,v 1.1 2003/05/13 13:41:39 root Exp netadmin $
#$Log: gramm.sh,v $
#Revision 1.1  2003/05/13 13:41:39  root
#Initial revision
#
#Revision 1.3  2003/04/21 14:35:58  netadmin
#Modified the gramm call.
#
#Revision 1.2  2003/04/21 14:19:33  netadmin
#Deleted the release 1.0 comments and the 'coord' parameter for gramm.

GRAMMDAT=/opt/structure/gramm/
export GRAMMDAT


doit() {
    /opt/structure/gramm/gramm $1 $2
    # Here the PDB files should already been generated.
    # We do NOT generate the auxiliary files, they may be generated
    # on the fly with auxiliary scripts/servlets
    touch done
    #rm -f webgramm.log ;
    rm -f started
    #rm -f gramm.sh ;
    #rm -f Rodin_Penseur.jpg ;
}

doit $1 $2 > /dev/null 2>&1 &

exit

