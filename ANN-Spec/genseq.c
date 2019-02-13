#include <stdio.h>
#include <stdlib.h>
#include <time.h>

/* generate random sequences */

char *dna = "ATGC";
char *rna = "AUGC";
char *aa = "GAVILFPMSTYWNQCDEKRH";
int dnalen = 4;
int rnalen = 4;
int aalen = 20;

main(int argc, char *argv[])
{
    int no_seqs, seq_size, pat_size, resno;
    char *pattern, *the_seq, *residues;
    int count, i, res;
    long j;
    FILE *positions;

    /* argv[1] = number of sequences to output */

    /* argv[2] = size of sequences */
    /* argv[3] = type of sequences (d, r, p) */
    /* argv[4] = pattern to embed in each sequence */
    if (argc < 4) {
    	fprintf(stderr, "\nusage: genseq #seqs size {D|R|P}[pattern]\n\n");
	exit(1);
    }
    no_seqs = atoi(argv[1]);
    if (no_seqs == 0) {     /* might be a non-number */
    	exit(0);
    }
    seq_size = atoi(argv[2]);
    if (seq_size == 0) {    /* might be a non-number */
    	exit(0);
    }
    switch (tolower(argv[3][0])) {
    	case 'p':	resno = aalen;
		residues = aa;
		break;
	case 'r': resno = rnalen;
		residues = rna;
		break;
	case 'd':
	default: resno = dnalen;
		residues = dna;
		break;
    }
    the_seq = malloc(seq_size + 1);
    if (the_seq == NULL) {
    	fprintf(stderr, "ERROR: run out of memory!\n");
	exit(1);
    }
    if (argc == 5) {	/* we want a pattern included */
    	pattern = argv[3];
    	pat_size = strlen(pattern);
	if (seq_size <= pat_size) {
	    fprintf(stderr, "\nERROR: Sequence size is too short for pattern\n");
	    exit(1);
	}
    	srandom(no_seqs + seq_size + pat_size + (unsigned) pattern[0]);
    } else {
    	srandom(no_seqs + seq_size);
    }
    if ((positions = fopen("positions.txt", "w+")) == NULL)
    	exit(0);
    
    /* We are ready to start */
    srandom(time(NULL));
    for (count = 1; count <= no_seqs; count++) {
    	/* write out header line */
    	printf(">ID%d (automagically generated)\n", count);
	/* generate random sequence */
	the_seq[0] = the_seq[seq_size + 1] = '\0';
	for (i = 0; i < seq_size; i++) {
	    res = random() % resno;
	    the_seq[i] = residues[res];
	}
	if (argc == 5) {
	    /* select pattern position */
	    j = random() % (seq_size - pat_size); /* leave room for it */
	    /* insert pattern */
	    /* This may be enhanced by allowing random changes of NRY */
	    strncpy(&the_seq[j], pattern, pat_size);
     	    fprintf(positions, "ID%d:\t%d\t%s\n", count, j, pattern);
       }
	/* write out sequence */
	for (j = 0; j < seq_size; j++) {
	    putchar(the_seq[j]);
	    if  ((j % 80) == 79) putchar('\n');
	}
	putchar('\n');
    }
    exit(0);
}
