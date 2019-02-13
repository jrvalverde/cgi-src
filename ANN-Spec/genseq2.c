#include <stdio.h>
#include <stdlib.h>
#include <ctype.h>
#include <time.h>
#include <string.h>

/* generate random sequences */
/* v2: */
/*  genseq -n #seqs -s size [-t {D|R|P}] [-p pattern] [-f f1:f2:f3...] */

char *dna   = "ACGT";
char *rna   = "ACGU";
char *aa    = "ACDEFGHIKLMNPQRSTVWY";
int ntlen   = 4;
int aalen   = 20;
double ntfr[4] = { 0.25, 0.25, 0.25, 0.25 };
double aafr[20] = { 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20,
    	    	1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20, 1/20};

void usage()
{
    	fprintf(stderr, "\nusage: genseq -n noseqs -s size [-t {D|R|P}] [-p pattern] [-f f1:f2...]\n\n");
	fprintf(stderr, "\t-n noseqs        Number of sequences to generate\n\n");
	fprintf(stderr, "\t-s size          Size of sequences to generate\n\n");
	fprintf(stderr, "\t-t type          Type of sequences (optional):\n");
	fprintf(stderr, "\t                      D: DNA (default)\n");
	fprintf(stderr, "\t                      R: RNA\n");
	fprintf(stderr, "\t                      P: Protein\n\n");
	fprintf(stderr, "\t-p pattern       Pattern to embed in the sequences (optional)\n\n");
	fprintf(stderr, "\t-f frequencies   Frequencies for each residue (optional)\n");
	fprintf(stderr, "\t                 specified in alphabetical order (e.g. ACGT, ACGU\n");
	fprintf(stderr, "\t                 or ACDEFGHIKLMNPQRSTVWY) as decimal numbers separated \n");
	fprintf(stderr, "\t                 by colons (:), e.g.:\n");
	fprintf(stderr, "\t                      0.25:0.30:0.20:0.25\n");
	fprintf(stderr, "\t                 at the end, unspecified frequencies will be set to zero.\n");
	fprintf(stderr, "\t                 If this parameter is omitted, even frequencies for all\n");
	fprintf(stderr, "\t                 residues will be assumed by default.\n\n");

}

int main(int argc, char *argv[])
{
    int no_seqs, seq_size, pat_size, resno;
    char *pattern, *the_seq, *residues, *frequencies, *fr;
    double *freq, cum_freq, *cfreq, drnd;
    int count, i;
    long j;
    FILE *positions;

    if (argc < 5) {
    	usage();
	exit(1);
    }
    /* set defaults */
    frequencies = NULL; residues = NULL; the_seq = NULL; pattern = NULL;
    resno = ntlen; residues = dna; freq = ntfr;

    for (i = 1; i < argc; i++) {
	if ((argv[i][0] != '-') || (i > (argc - 1))){
	    /* if it is not an argument or there is no value behind */
	    usage();
	    exit(1);
	}
	switch (argv[i][1]) {
	    case 'n':   /* number of sequences */
	    	i++;
		no_seqs = atoi(argv[i]);
		if (no_seqs == 0) {
		    usage();
		    exit(1);
		}
		break;
	    case 's':   /* size of sequences */
	    	i++;
		seq_size = atoi(argv[i]);
		if (seq_size == 0) {
		    usage();
		    exit(1);
		}
		break;
	    case 't':   /* type of sequences */
	    	i++;
		switch (tolower(argv[i][0])) {
		    case 'p':   /* protein */
			resno = aalen;
			residues = aa;
			freq = aafr;
			break;
		    case 'r':   /* rna */
			resno = ntlen;
			residues = rna;
			freq = ntfr;
			break;
		    case 'd':   /* dna */
		    default:
			resno = ntlen;
			residues = dna;
			freq = ntfr;
			break;

		}
		break;
	    case 'p':   /* include pattern */
	    	i++;
    	    	pattern = argv[i];
    	    	pat_size = strlen(pattern);
	    	if (seq_size <= pat_size) {
	    	    fprintf(stderr, "\nERROR: Sequence size is too short (%d) for pattern (%d)\n",
		    	    seq_size, pat_size);
	    	    exit(1);
	    	}
		break;
	    case 'f':   /* frequencies on command line */
		/* defer processing until we are sure to know the 
		 * sequence type (namely, after processing the command 
		 * line */
		 i++;
		frequencies = argv[i];
		break;
	    default:
		usage();
		exit(1);
	}
    }
    /* at this point we are sure to have all needed parameters */
    if (frequencies != NULL) {
    	/* default to zero */
    	for (i = 0; i < resno; i++) freq[i] = 0.0;
	/* start processing frequencies */
	fr = strtok(frequencies, ":");
	for (i = 0; i < resno; i++) {
	    freq[i] = atof(fr);
	    fr = strtok(NULL, ":");
	    if (fr == NULL)
	    	break;
	}
	/* note that sum of frequencies might be less than one */
    }
    /* compute cumulative frequencies for internal use in residue
       assignment */
    cfreq = malloc(resno * sizeof(* cfreq));
    if (cfreq == NULL) {
	fprintf(stderr, "ERROR: not enough memory\n");
	exit(1);
    }
    cum_freq = 0.0;
    for (i = 0; i < resno; i++) {
	cum_freq += freq[i];
	cfreq[i] = cum_freq;
    }
    if ((cum_freq < 0.99) || (cum_freq > 1.01)) {  /* allow for 1% error */
	fprintf(stderr, "ERROR: frequencies do not sum up to 1\n");
	exit(1);
    }

    if ((positions = fopen("positions.txt", "w+")) == NULL)
    	exit(0);
    
    /* We are ready to start */
    the_seq = malloc(seq_size + 1);
    if (the_seq == NULL) {
        fprintf(stderr, "ERROR: run out of memory!\n");
        exit(1);
    }
    srandom(time(NULL));
    for (count = 1; count <= no_seqs; count++) {
    	/* write out header line */
    	printf(">ID%d (automagically generated)\n", count);
	/* generate random sequence */
	the_seq[0] = the_seq[seq_size + 1] = '\0';
	for (i = 0; i < seq_size; i++) {
	    drnd = (double) random() / (double) RAND_MAX; 
	    for (j = (resno - 1); j >= 0; j--)
		if (drnd < cfreq[j])
		    the_seq[i] = residues[j];
		else
		    break;
	}
	if (pattern != NULL) {
	    /* select pattern position */
	    j = random() % (seq_size - pat_size); /* leave room for it */
	    /* insert pattern */
	    /* This may be enhanced by allowing random changes of NRY */
	    strncpy(&the_seq[j], pattern, pat_size);
     	    fprintf(positions, "ID%d:\t%ld\t%s\n", count, j, pattern);
       }
	/* write out sequence */
	for (j = 0; j < seq_size; j++) {
	    putchar(the_seq[j]);
	    if  ((j % 80) == 79) putchar('\n');
	}
	putchar('\n');
    }
    exit(0);
    return 0;
}
