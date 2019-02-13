#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <ndbm.h>

int main(int argc, char *argv[]) {
    DBM *db;
    char userv[11], acceptv[2]; 
    datum user;
    datum accept;
    int result;
	
    db = dbm_open("gcgusers", O_CREAT, 0600);
    if (db == NULL) {
    	printf("Error opening gcgusers\n");
	return 0;
    }
    dbm_close(db);
    db = dbm_open("gcgusers", O_RDWR, 0600);
    
    user.dptr = "jruser";
    user.dsize=strlen("jruser");

    accept = dbm_fetch(db, user);
    if (accept.dptr == NULL) {
    	/* jruser is NOT in the database */
	accept.dptr="NO"; accept.dsize=strlen("NO");
	result = dbm_store(db, user, accept, DBM_INSERT);
	if (result == 0) printf("Stored jruser\n");
	else {
		printf("jruser could not be stored: %d\n", result);
		return 0;
	}
    } else {
    	/* jruser IS in the database */
	printf("Found user: %s accept: %c\n", user.dptr, (char) *accept.dptr);
	accept.dptr="YES"; accept.dsize=strlen("YES");
	result = dbm_store(db, user, accept, DBM_REPLACE);
	if (result == 0) printf("Replaced jruser\n");
	else {
		printf("jruser could not be replaced: %d\n", result);
		return 0;
	}
    }
    return 1;
}
