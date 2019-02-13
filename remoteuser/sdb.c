#include <stdio.h>
#include <stdlib.h>
#include <sqlite.h>

int found;
char *user;
char accept;

int check(void *pArg, int argc, char **argv, char **columnNames) {
	int i;
	found++;
	printf("Found:");
	for (i = 0; i < argc; i++) {
		printf(" %s", argv[i]);
	}
	printf("\n");
	/* hard-coded */
	user = argv[0]; accept = *argv[1];
	return 0;
}

int db_enter(sqlite *db, char *user, char accept);

int main(int argc, char *argv[]) {
    sqlite *db;
    char *error_msg;
    int status;
    
    db = sqlite_open("gcgusers", 0666, &error_msg);
    if (db == NULL) {
        printf("Error opening gcgusers: %s\n", error_msg);
	exit(0);
    }
    
    status = sqlite_exec(db, 
    	"CREATE TABLE gcgusers (user VARCHAR(10), accept VARCHAR(1))",
	NULL, NULL, &error_msg);
    printf("Create table: %d %s\n", status, error_msg);
    
    db_enter(db, "jruser", 'Y');

    db_enter(db, "root", 'N');
    
    db_enter(db, "nobody", 'N');
       
    sqlite_close(db);
    exit(1);
}

int db_enter(sqlite *db, char *user, char accept)
{
    int status;
    char *error_msg;
    char command[1024];
    
    /* start a new transaction */
    status = sqlite_exec(db, 
    	"BEGIN TRANSACTION",
	NULL, NULL, &error_msg);
    printf("Begin transaction: %d %s\n", status, error_msg);

    /* first check if the user already exists */
    found = 0;
    snprintf(command, 1024, "SELECT * FROM gcgusers WHERE user=\"%s\"", user);
    status = sqlite_exec(db, command, check, NULL, &error_msg);
    printf("Select %s: %d %s\n", user, status, error_msg);

    if (found == 0) {
    	snprintf(command, 1024, 
		"INSERT INTO gcgusers VALUES(\"%s\", \"%c\")", user, accept);
	status = sqlite_exec(db, command, NULL, NULL, &error_msg);
    	printf("Insert %s: %d %s\n", user, status, error_msg);
    }
    else {
        snprintf(command, 1024, 
		"UPDATE gcgusers SET user=\"%s\", accept=\"%c\" WHERE user=\"%s\"", user, accept, user);
    	status = sqlite_exec(db, command, NULL, NULL, &error_msg);
    	printf("Update %s: %d %s\n", user, status, error_msg);
    }
    
    /* finally commit the transaction */
    status = sqlite_exec(db, 
    	"COMMIT TRANSACTION",
	NULL, NULL, &error_msg);
    printf("Commit transaction: %d %s\n", status, error_msg);
    

}
