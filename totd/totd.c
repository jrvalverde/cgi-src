/* 
 * cookie.c
 *
 *  A program to print cookies.
 *
 *  By:
 *	J. R. Valverde
 */

#include <stdio.h>
#include <stdlib.h>
#ifdef vms
#  include <file.h>
#else
#  include <sys/file.h>
#endif
#include <math.h>
#include <ctype.h>
#ifdef vms
#ifndef GNU
#  include <unixio.h>
#endif
#  include <types.h>
#endif
#include <time.h>

#ifdef vms
    char default_bakery[] = "SYS$LIBRARY:TOTD.FILE";
#   define LOGICAL_BAKERY	"TOTD"
#else
    char default_bakery[] = "/usr/local/lib/totd.file";
#   define LOGICAL_BAKERY	"totd"
#endif

#if defined(GNU) || defined(ultrix) || defined(__sgi)
#define FALSE 0
#define TRUE  1
#endif

#define MAXLINE 	256
#define MAXRANDOM   4294967295
#define OCCASIONAL	10

#define NOBACKGROUNDS	20

char *background[] = {
	"/bkgs/EMBnetCNB.gif",
	"/bkgs/Gotele.gif",
	"/bkgs/Crunched.gif",
	"/bkgs/Linen.gif",
	"/bkgs/bluemarble.jpg",
	"/bkgs/marble.jpg",
	"/bkgs/marble2back.gif",
	"/bkgs/whitebrick.gif",
	"/bkgs/WhiteCloth2.gif",
	"/bkgs/WhiteLeather3.gif",
	"/bkgs/E.coli2.gif",
	"/bkgs/bambooback.gif",
	"/bkgs/branchback.gif",
	"/bkgs/bullback.gif",
	"/bkgs/fernback.gif",
	"/bkgs/greytileback.gif",
	"/bkgs/lotusback.gif",
	"/bkgs/tile2back.gif",
	"/bkgs/tileback.gif",
	"/bkgs/marbleback.gif",
	
};

FILE *open_cookie_file(int argc,char *argv[]);
long get_file_length(FILE *fptr);
void print_cookie(FILE *bakery, long length);
long fit_to_length(long rno, long length);
void print_error(char *errmsg);

extern void my_seed(unsigned long int s);
extern unsigned long int my_random(unsigned long int r);

main(argc, argv)
int argc;
char *argv[];
{
    static FILE *bakery;
    static long length;
    static unsigned long bkgno;

    fprintf(stdout, "Content-type: text/html\n\n");
    fprintf(stdout, 
    "<HTML>\n<HEAD>\n<TITLE>Tip of the day</TITLE>\n</HEAD>\n");

    bkgno = time(NULL) % NOBACKGROUNDS;
    fprintf(stdout, "<BODY background=\"%s\">\n", background[bkgno]);


    bakery = open_cookie_file(argc, argv);
    if (bakery == NULL) {
    	fprintf(stdout, "<P><H1>Sorry, couldn't find any tips!</H1></P>\n");
    	fprintf(stdout, "\n</BODY>\n</HTML>\n");
		exit(0);
	}

    length = get_file_length(bakery);
    if (length == 0l) {
    	fprintf(stdout, "<P><H1>Sorry, we have no tips available!</H1></P>\n");
		exit(0);
	}

    print_cookie(bakery, length);
	fprintf(stdout, "\n</BODY>\n</HTML>\n");
    exit(0);
}

FILE *open_cookie_file(argc, argv)
int argc;
char *argv[];
{
    static char *auxname;
    static FILE *bakery;

    /* 1. Open specified file if arguments are available */
    if (argc > 1) {
	/* loop for all arguments: args are alternative files */
	while (--argc) {
	    if ((bakery = fopen(argv[argc], "r")) != NULL)
		return bakery;
	    else {
		if ((auxname = getenv(argv[argc])) != NULL)
		    if ((bakery = fopen(auxname, "r")) != NULL)
			return bakery;
	    }
	}
    }

    /* 2. If that failed, then try with default file name */
    if ((bakery = fopen(default_bakery, "r")) != NULL)
	return bakery;
    else {
		if ((auxname = getenv(LOGICAL_BAKERY)) != NULL)
	 	   if ((bakery = fopen(auxname, "r")) != NULL)
			return bakery;
   	}

    /* 3. If this also failed, then forget about this */
    return NULL;
}

long get_file_length(fptr)
FILE *fptr;
{
    static long pos;

    fseek(fptr, 0L, 2);   /* go to end of file */
    if ((pos = ftell(fptr)) == EOF) {
		return 0l;
    }
    return pos;
}

void print_cookie(bakery, length)
FILE *bakery;
long length;
{
    static unsigned long pos;
    static char line[MAXLINE];
    static int done;

    /* 5. Now get a random number between 0 and file length */
    my_seed(0L);
    pos = my_random(length);

    /* 6. Locate the first cookie */
    done = FALSE;
    fseek(bakery, pos, 0);  /* seek from beginning of file */
    fgets(line, MAXLINE, bakery); /* discard first line read */
    for (;;) {
	if (fgets(line, MAXLINE, bakery) == NULL) {
	    if (done) {
		fprintf(stderr, "<P><H1>There are no tips at all!</H1></P>\n");
		return;	    /* there are no cookies */
	    }
	    fseek(bakery, 0L, 0);   /* rewind */
	    done = TRUE;	    /* we have passed EOF */
	    continue;
	}
	if (line[0] != ' ') break;	/* we have found a cookie */
    }

    /* 7. print the cookie */
    fprintf(stdout, "\n<STRONG>\n");
    fprintf(stdout, "%s\n", line);
    while (fgets(line, MAXLINE, bakery) != NULL) {
		if (line[0] == '\0') {
		    fprintf(stdout, "\n<P></P>\n");
		    continue;
		}
		if (line[0] != ' ') break;	          /* we are done */
		fprintf(stdout, "%s\n", &line[1]);
    }
    fprintf(stdout, "\n</STRONG>\n");
    fprintf(stdout, "<FORM METHOD=\"POST\" ACTION=\"/cgi-bin/totd\">\n");
    fprintf(stdout, "<CENTER><INPUT TYPE=\"submit\" NAME=\"Action\" VALUE=\"New Tip\"></CENTER>\n");

    return;
}

long fit_to_length(rno, length)
long rno, length;
{
    static double drno, dlen, dno;

    drno = rno; dlen = length;
    dno = drno * dlen;
    dno /= MAXRANDOM;
    return floor(dno);
}

