/**
 * distdna - Distance matrix for DNA
 *
 *	Build a distance matrix for DNA sequences, allowing for 
 * various correction algorithms.
 *
 *	Usage: distdna infile outfile
 *
 *	Usage: distdna options
 *		-i infile
 *		--in infile
 *		-o outfile
 *		--out outfile
 *		-g gapwt (0.0)
 *		--gapweight gapwt (0.0)
 *  	    	-a a (1.0)
 *		-n use ambiguity codes
 *		--uncorrected
 *		--jukes-cantor
 *		--tajima-nei
 *		--kimura
 *		--tamura
 *		--jin-nei
 *		--galtier-gouy
 *  	    	--hky
 *
 * Read in DNA sequences:
 *
 *	All seqs must be the same length, in all-caps, with no ambiguity
 * codes (at least for now, but most methods do not allow them).
 *
 * Compact read sequences
 *
 * Compute distances AND print on the fly to reduce memory requirements.
 *
 *
 * (C) Jose R. Valverde, EMBnet/CNB, CSIC. 2009
 *	jrvalverde@cnb.csic.es
 *
 * License: GNU GPL
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package 	distdna
 * @author  	Jose R Valverde <jrvalverde@cnb.csic.es>
 * @copyright	EMBnet/CNB
 * @license 	c/gpl.txt
 * @version 	0.9
 * @see     	docs/Tajima.pdf
 * @see     	docs/Kimura.pdf
 * @see     	docs/Tamura.pdf
 * @see     	docs/Tajima-Nei.pdf
 * @see     	docs/Jin-Nei.pdf
 * @since   	File available since version 0.0
 * 
 *
 * $Id: distdna.c,v 1.22 2009/10/01 12:59:59 root Exp root $
 *
 * $Log: distdna.c,v $
 * Revision 1.22  2009/10/01 12:59:59  root
 * Grrr! Grrr!!! Further corrected PHYLIP output [j]
 *
 * Revision 1.21  2009/10/01 12:48:08  root
 * Grrr!!! and on writing column of sequence names [j]
 *
 * Revision 1.20  2009/10/01 12:45:58  root
 * Corrected small bug in PHYLIP header output [j]
 *
 * Revision 1.19  2009/10/01 12:40:08  root
 * Corrected help info on option gapweight [j]
 *
 * Revision 1.18  2009/10/01 12:23:34  root
 * Added full filter behaviour with support for data input from stdin [j]
 *
 * Revision 1.17  2009/10/01 06:55:55  root
 * Added support for output to stdout [j]
 *
 * Revision 1.16  2009/09/23 14:10:42  root
 * Fixed initialization bug that gave bad results on some machines [j]
 *
 * Revision 1.15  2009/09/23 13:37:21  root
 * Added support for LogDet distance of Lockhart et al. plus documentation
 * enhancements. [j]
 *
 * Revision 1.14  2009/09/23 10:22:24  root
 * Added support for HKY (Hasegawa-Kishino-Yano) distance [j]
 *
 * Revision 1.13  2009/09/22 13:55:19  root
 * Added support for Galtier-Gouy(95). Results differ slightly from PHYLO_WIN
 * perhaps due to the different way we count frequencies? [j]
 *
 * Revision 1.12  2009/09/21 09:40:41  root
 * Minor help enhancements [j]
 *
 * Revision 1.11  2009/09/21 09:37:02  root
 * Added support for ambiguity codes to uncorrected, Jukes-Cantor and Tajima-Nei distance calculations [j]
 *
 * Revision 1.10  2009/09/18 17:20:21  root
 * Fixed and enhanced PHYLIP compatibility mode (not quite there though yet)[j]
 *
 * Revision 1.9  2009/09/18 13:15:03  root
 * Added support for various output formats [j]
 *
 * Revision 1.8  2009/09/18 12:16:30  root
 * Refactored and added algorithm selection hints. [j]
 *
 * Revision 1.7  2009/09/18 09:23:27  root
 * Fixed support for Tajima-Nei method. Now works OK [j]
 *
 * Revision 1.6  2009/09/18 06:46:30  root
 * Added code for Tajima-Nei, but results differ from DISTMAT. Do not
 * use it yet until further investigated. [j]
 *
 * Revision 1.5  2009/09/17 10:43:01  root
 * Added support for Jin-Nei distance [j]
 *
 * Revision 1.4  2009/09/17 09:10:06  root
 * Added support for Tamura distance [j]
 *
 * Revision 1.3  2009/09/16 21:18:22  jr
 * Added support for Kimura two-parameter distance [j]
 *
 * Revision 1.2  2009/09/16 19:31:23  jr
 * Added command line options, partial refactoring, added Jukes-Cantor [j]
 *
 * Revision 1.1  2009/09/16 12:43:59  root
 * Initial revision
 *
 */

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/param.h>
#include <limits.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <string.h>
#include <math.h>
#include <ctype.h>
#include <float.h>

#define GNU_SOURCE
#include <getopt.h>

#ifdef DEBUG
#  define DBG(x)	x
#else
#  define DBG(x)
#endif

#define LOWER_TRIANGULAR	1
#define UNITY_DISTANCE		1<<1
#define PHYLIP			1<<2

#define EPSILON 		1.0e-10

/** 
 * Define the correction methods available
 *
 * We use NONE=0 and INVALID as centinels in command line procesing
 */
enum correction_t {
    NONE,		/** used to avoid conflict with getopt_long (0) */
    UNCORRECTED,	/** uncorrected distance using exact matches and gaps */
    JUKES_CANTOR,	/** Jukes-Cantor correction */
    KIMURA,		/** Kimura correction */
    TAMURA,		/** Tamura correction */
    JIN_NEI,		/** Jin-Nei correcrion */
    TAJIMA_NEI,		/** Tajima-Nei correction */
    GALTIER_GOUY,	/** Galtier-Gouy correction */
    HASEGAWA_KISHINO_YANO,
    HKY,		/** Hasegawa-Kishino-Yano correction */
    LOGDET,		/** Lockhart et. al. logDet correction */
    INVALID		/** used to avoid choosing an invalid correction */
};

/**
 * A list of all possible pairwise nucleotide combinations
 *
 * Used to index nucleotide combination matrices.
 */
enum nt_changes{
    AA = 0,
    AC,
    AG,
    AT,
    CA,
    CC,
    CG,
    CT,
    GA,
    GC,
    GG,
    GT,
    TA,
    TC,
    TG,
    TT
};

/* forward declarations */
void usage(char *myname);
void advice();
char *read_seqs(char *infn, int *ns, int *sl, char ***sqs, char ***sqns);
char *read_from_stdin(int *ns, int *sl, char ***sqs, char ***sqns);
void write_distmat(char *outfn, char **seqs, char **seqnames,
		   int nseqs, int seqlen, double gapwt, double a,
		   enum correction_t correction, int ambig, int oflags);
void write_header(FILE * outfile, char **seqnames, int nseqs, 
		  double gapwt, double a,
		  enum correction_t correction, int oflags);


double dist_uncorrected(char *s1, char *s2, int slen, double gapwt, int ambig);

double dist_jukes_cantor(char *s1, char *s2, int slen, double gapwt, int ambig);

double dist_kimura(char *s1, char *s2, int slen);

double dist_tamura(char *s1, char *s2, int slen);

double dist_jin_nei(char *s1, char *s2, int slen, double a);

double dist_tajima_nei(char *s1, char *s2, int slen, int ambig);

double dist_galtier_gouy(char *s1, char *s2, int slen, double alpha);

double dist_hasegawa_kishino_yano(char *s1, char *s2, int slen);

double dist_LogDet(char *s1, char *s2, int slen);



double nt_ambig_match(char n1, char n2);

int strchrpos(char *s, char c);

double galtier_gouy_alpha(char **seqs, int nseqs, int seqlen);

void pairwise_frequencies(char *s1, char *s2, int slen, double *mut_freq);



/**
 * main
 *
 * Main program: process command line options, read in input data
 * and compute distances.
 *
 * @param int argc  	argument count
 * @param char **argv	command line arguments
 */
int main(int argc, char **argv)
{
    char **seqs, **seqnames;
    int nseqs, seqlen;
    enum correction_t correction;
    int i, j, oflags, ambig;
    double gapwt, a;
    char *infn, *outfn;
    char *data;
    static struct option opts[] = {
	{"in", 1, NULL, 0},
	{"out", 1, NULL, 0},
	{"gapweight", 1, NULL, 0},
	{"jn_a", 0, NULL, 0},
	{"uncorrected", 0, NULL, UNCORRECTED},
	{"jukes-cantor", 0, NULL, JUKES_CANTOR},
	{"kimura", 0, NULL, KIMURA},
	{"tamura", 0, NULL, TAMURA},
	{"jin-nei", 0, NULL, JIN_NEI},
	{"tajima-nei", 0, NULL, TAJIMA_NEI},
	{"galtier-gouy", 0, NULL, GALTIER_GOUY},
	{"hasegawa-kishino-yano", 0, NULL, HASEGAWA_KISHINO_YANO},
        {"hky", 0, NULL, HKY},
        {"logdet", 0, NULL, LOGDET},
	{NULL, 0, NULL, 0}
    };

    /* initialize default values */
    oflags = 0;		/* output generation flags */
    ambig = 0;		/* consider ambiguity codes */
    gapwt = 0.0;
    a = 1.0;			/* as recommended by Jin-Nei */
    infn = outfn = NULL;
    correction = UNCORRECTED;

    /* use getopt_long to process command line options */
    do {
	i = getopt_long(argc, argv, "i:o:g:a:?hl1px", opts, &j);
	switch (i) {
	case '?':
	    usage(argv[0]);
	    break;
	case 'h':
	    advice();
	    break;
	case 'i':
	    infn = optarg;
	    break;
	case 'n':
	    ambig = 1;
	    break;
	case 'o':
	    outfn = optarg;
	    break;
	case 'g':
	    gapwt = atof(optarg);
	    break;
	case 'a':
	    a = atof(optarg);
	    break;
	case 'l':
	    oflags |= LOWER_TRIANGULAR;
	    break;
	case '1':
	    oflags |= UNITY_DISTANCE;
	    break;
	case 'p':
	    oflags |= (LOWER_TRIANGULAR | UNITY_DISTANCE | PHYLIP);
	    break;
	case 0:		/* long option */
	    switch (j) {
	    case 0:
		infn = optarg;
		break;
	    case 1:
		outfn = optarg;
		break;
	    case 2:
		gapwt = atof(optarg);
		break;
	    default:
		break;
	    }
	default:
	    /* may be a correction flag */
	    if ((i > NONE) && (i < INVALID))
		correction = i;
	    break;
	};

    }
    while (i != -1);

    DBG(fprintf(stderr, "%s: (in)%s (out)%s (gap)%f (corr)%d\n",
		argv[0], infn, outfn, gapwt, correction));

    data = read_seqs(infn, &nseqs, &seqlen, &seqs, &seqnames);

    write_distmat(outfn, seqs, seqnames, nseqs, seqlen, gapwt, a,
		  correction, ambig, oflags);

    /* and there we are */
    if ((infn[0] == '-') && (infn[1] =='\0')) {
	for (i = 0; i < nseqs; i++) {
	    free(seqs[i]);
	    free(seqnames[i]);
	}
	free(seqs);
	free(seqnames);
    }
    else {
	free(seqs);
	free(seqnames);
	free(data);
    }
    exit(0);
    return 0;
}


/**
 * usage
 *
 * Print usage information
 *
 * @param char *myname	name used to call this program
 */
void usage(char *myname)
{
    printf("%s -- compute distance matrix for DNA sequences\n\n", myname);
    puts("Usage: %s options");
    puts("Options:");
    puts("        -?             print this help");
    puts("        -h             give hints on algorithm selection");
    puts("        -i infile");
    puts("        --in infile    name of file with aligned sequences in");
    puts("                       FASTA format");
    puts("        -o outfile");
    puts("        --out outfile  name of output file with the distance");
    puts("                       matrix");
    puts("            -l         (ell) write out a lower triangular matrix");
    puts("                       instead of default upper-triangular");
    puts("            -1         (one) output distance per nucleotide instead");
    puts("                       of default per one hundred positions");
    puts("            -p         output similarities PHYLIP style (i. e.,");
    puts("                       1 - unity distances instead of percent,");
    puts("                       and a l - lower-triangular matrix");
    puts("        --gapweight #  gap weight (defaults to 0)");
    puts("");
    puts("    Selection of distance correction method");
    puts("        --uncorrected  use uncorrected computation of distances");
    puts("            -n         also consider ambiguity codes");
    puts("        --jukes-cantor use Jukes-Cantor correction for distance");
    puts("            -n         also consider ambiguity codes");
    puts("        --kimura       use Kimura's correction for distance");
    puts("        --tamura       use Tamura's correction for distance");
    puts("        --jin-nei      use Jin-Nei correction for distance");
    puts("            -a #       value of 'a' parameter for Jin-Nei distance");
    puts("                       computations (defaults to 1.0)");
    puts("        --tajima-nei   use Tajima-Nei correction for distance");
    puts("            -n         also consider ambiguity codes");
    puts("        --galtier-gouy use Galtier-Gouy correction");
    puts("        --hasegawa-kishino-yano");
    puts("        --hky          use Hasegawa-Mishino-Yano correction");
    puts("        --logdet       use LogDet distance of Lockhart et al.");
    puts("");
    puts("You may use options in any combination and repeat or combine them");
    puts("any way you like. In case of conflicting options, the last one");
    puts("will be used.\n");
    puts("You can get some hints on selecting a method using '-h', e.g.:");
    printf("\t%s -h | more\n", myname);
    puts("\nJose R. Valverde, 2009\n");
    exit(0);
}


/**
 * advice
 *
 * print advice on algorithm selection
 *
 */
void advice()
{
    puts("\n\
\t\tHopefully helpful advice\n\
\t\t========================\n\n\
If you have doubts on which algorithm to use, the following advice\n\
may be helpful.\n\
\n\
1. The sequence alignment\n\
-------------------------\n\
The single most important issue is the sequence alginment, which you\n\
must have done with some other program. Always review you alignment\n\
and fix it by hand if possible before proceeding. If subsequent work\n\
fails to give meaningful results, revieweing the alignment may help\n\
identify some flaws.\n\
\n\
2. Deciding what to compare\n\
---------------------------\n\
Next you must decide which regions will be used to score. Often you\n\
will be interested only in homologous regions. You must also check\n\
that the regions do make sense to compare (i.e. there are no inversions\n\
or duplications). In deciding what to analyze you may also think\n\
orthogonally, i. e. not in terms of a contiguous sequence but of\n\
vertical sections of it (namely, whether to use all nucleotides in\n\
each triplet or only some positions). Finally, remember that the\n\
regions you analyze in all sequences must all include all corresponding\n\
information from all sequences and nothing more that is specific of\n\
a subset of them (unless that difference is evolutively significant).\n\
\n\
3. Selecting the appropriate correction\n\
---------------------------------------\n\
Once you are sure you are comparing the appropriate data, you must\n\
select the appropriate algorithm.\n\
\n\
0. Start by computing an uncorrected matrix to get an impression of the\n\
   overall distribution of distances. If there are large deviations, you\n\
may need to apply a correction method.\n\
\n\
Jin and Nei(1990) gave some guidelines you might find useful:\n\
There is no universally good method for measuring distances for\n\
Neighbor-Joining, so you must chose wisely:\n\n\
1. You can use Jukes-Cantor when the estimated number of substitutions\n\
   per site (d) is ~0.1 or less (<= 10%) (even if there is a transition -\n\ transversion       bias or if nucleotide frequencies vary).\n\n\
2. When the distance (d) is > 0.1 and < ~0.3 (10-30%) use Jukes-Cantor\n\
   unless you have a high transition bias (e.g. > 0.5) in which\n\
   case you must use Kimura distance.\n\n\
3. When distance is > ~0.3 but < 1 (30-100%) and there is evidence that\n\
   substitution rates vary widely among sites, use the Jin-Nei Gamma\n\
   method, with a suggested 'a' of 1.0 unless you have computed a better\n\
   and sound estimate for 'a'.\n\n\
4. When distance is > ~0.3 and < 1 (30-100%) and there is evidence that\n\
   nucleotide composition deviates from uniformity, use the Tajima-Nei\n\
   distance.\n\n\
5. When distance is > ~0.3 and < 1 (30-100%) and there are both a strong\n\
   G+C and transition/transversion bias use Tamura's distance.\n\n\
6. When distance is > 1 (>100%) the data is not reliable and should not\n\
   be used for phylogenetic tree reconstruction. In this case you may try\n\
    - using only the first or first and second positions in each codon\n\
    - translate the sequences to protein and work with amino acids\n\
    - remove regions that evolve relatively much faster\n\
    - use a different gene that evolves more slowly\n\
\n\
For other methods you better familiarize yourself with the literature\n\n\
\nFor help on using the program use option '-?'\n\n\
");

    exit(0);
}

/**
 * read_seqs
 *
 * read in the sequences from the specified file into memory
 * allocating two arrays of pointers to the sequence names and the
 * sequences themselves.
 *
 * This routine will attempt to open the specified file name. If
 * it succeeds it finds out the file size and allocates a buffer 
 * in memory to hold in the full contents of the file.
 *
 * After reading the file, the buffer holds an exact image of the
 * sequence alignment in FASTA format. In other words, it contains
 * only sequence names and sequences (with some extra whitespace).
 *
 * Find out sequence length and the number of sequences (just count
 * '>' characters).
 *
 * Allocate two pointer arrays for sequence names and sequences and
 * fill them in with the addresses of the corresponding names and
 * sequences.
 *
 * As a convenience, while processing, remove all whitespace from
 * the sequences and terminate names and sequences with a '\0'
 *
 * At the end we have:
 *
 *  - a buffer of size "filesize" containing all the input data
 *  - an array of "n sequences" pointers to the sequence names
 *  - an array of "n sequences" pointers to the sequences
 *
 * As all further processing will be done in place the total amount
 * of extra memory needed (barring some minor auxiliary variables)
 * is
 *
 *  	m = filesize + nseqs * sizeof(char*) * 2
 *
 * We could use even less memory by actually compacting the whole
 * buffer instead of just each sequence separately, and calling
 * realloc() to reduce the buffer size and return unused memory
 * to the system, but this would require more processing time and
 * more complex logic, which for the time being is not needed yet.
 *
 * On exit we return the number of sequences, sequence size, the
 * arrays of sequences and their names and a pointer to the data
 * buffer (this last will be unused except fot freeing it when we
 * are done --which BTW is unnecessary as the system will free it
 * anyway on program termination).
 *
 * @param char *infn	name of input file
 * @param int  *ns  	number of sequences read in
 * @param int  *sl      sequence length
 * @param char ***seqs	array of sequences
 * @param char ***seqns array of sequence names
 *
 * @return char *   	pointer to data buffer.
 */
char *read_seqs(char *infn, int *ns, int *sl, char ***sqs, char ***sqns)
{
    int i, j, k, infd, insize, nseqs, seqlen;
    char *data, **seqs, **seqnames, *s;
    struct stat instat;

#ifdef NOSTDIN
    /* this can't be used with the current input method: we need to
    find out the input file length, and that's not possible with
    stdin */
    if ((infn[0] == '-') && (infn[1] == '\0'))
    	return read_from_stdin(ns, sl, sqs, sqns);
    else 
#endif
    {
	/* open input file */
	infd = open(infn, O_RDONLY);
	if (infd == -1) {
	    fprintf(stderr, "Error: could not open input file %s\n", infn);
	    exit(1);
	}
    }
    /* find out input file details */
    fstat(infd, &instat);
    insize = instat.st_size;

    /* allocate memory and read in the file */
    if ((data = malloc(insize * sizeof(char))) == NULL) {
	fprintf(stderr, "Error: could not allocate %d bytes of memory\n",
		insize);
	exit(4);
    }
    /* 
     * this should be faster than reading one character at a time
     * and is certainly quicker and easier to implement.
     *
     * *** JR *** to do
     *
     * OTOH if we did read char by char we could reduce the number
     * of passes and memory footprint by counting the number of 
     * sequences (>) and compacting the input on the fly:
     *
     *  get(ch)
     *  if (ch == '>')
     *      nseqs++
     *      read (sequence name) until '\n'
     *      remove trailing whitespace (end with '\n')
     *      read non whitespace (sequence) until next '>'
     *
     * and later when we know nseqs, do a second pass to assign
     * names and seqs, substituting '\n' by '\0'. This later
     * pass might be eliminated by using 'realloc' but that might
     * fragment memory and I ain't sure if it would be really
     * returned to the system.
     *
     * My guess is we could probably save 1-5% in memory space
     * so this went down my priority list in my initial 
     * implementation.
     *
     * *** JR *** to be done
     *
     */
    i = read(infd, data, insize);
    if (i == -1) {
	fprintf(stderr, "Error, could not read input file %s\n", infn);
	exit(1);
    }

    /* find out number of sequences */
    /* we run over the whole dataset once only to find this out */
    nseqs = seqlen = j = 0;
    for (i = 0; i < insize; i++)
	if (data[i] == '>')
	    nseqs++;

    /* allocate arrays of pointers to each sequence */
    seqs = malloc(nseqs * sizeof(char *));
    seqnames = malloc(nseqs * sizeof(char *));
    if ((seqs == NULL) || (seqnames == NULL)) {
	fprintf(stderr,
		"Error: not enough memory to identify sequences\n");
	exit(4);
    }

    /* process data and compact it */
    /* run over the whole dataset once more */
    i = j = k = seqlen = nseqs = 0;
    do {
	if (data[i] == '>') {
	    nseqs++;
	    seqnames[k] = &data[i + 1];
	}
	while (data[i++] != '\n')
	    j++;		/* jump to end of sequence name */
	data[i - 1] = '\0';
	/*
	 * here we check for the presence of a ; in the sequence
	 * name and remove up to it... 
	 */
	if (s = strchr(seqnames[k], ';')) seqnames[k] = ++s;
	/* we are now in the sequence region: compact it */
	seqs[k++] = &data[i];
	j = i;
	while (data[i] != '>') {
	    if (i > insize)
		break;
	    if (isspace(data[i]))
		i++;
	    else if (j != i)
		data[j++] = data[i++];
	    else
		i++;
	}
	while (j < i)
	    data[j++] = '\0';	/* must be at least the last '\n' */
    }
    while (i < insize);
    seqlen = strlen(seqs[0]);
    if (infd != 0)
    	close(infd);

    /* set return values and finish */
    *ns = nseqs;
    *sl = seqlen;
    *sqs = seqs;
    *sqns = seqnames;
    return data;
}

/**
 * read_from_stdin
 *
 * Read sequence data from stdin. This case is special because we
 * cannot find the data size in advance and hence need to do some
 * exercising to allocate memory
 *
 * @param int  *ns  	number of sequences read in
 * @param int  *sl      sequence length
 * @param char ***seqs	array of sequences
 * @param char ***seqns array of sequence names
 *
 * @return char *   	pointer to data buffer.
 */
char *read_from_stdin(int *ns, int *sl, char ***sqs, char ***sqns)
{
    /* define a chunksize for memory allocation (8K entries for now) */
#define CHUNKSIZE	1024*8
    int i, j, k, curpos, infd, insize, memsize, nseqs, seqlen, newlen;
    char *data, **seqs, **seqnames, *s, line[BUFSIZ+1];
    struct stat instat;
    FILE *infile;

    infd = 0;	/* stdin */
    infile = stdin;

    data = NULL;
    seqs = seqnames = NULL;
    insize = seqlen = 0;
    memsize = CHUNKSIZE;
    
    /* allocate temptative seqs and seqnames */
    seqs = malloc(memsize * sizeof(char *));
    seqnames = malloc(memsize * sizeof(char *));
    if ((seqs == NULL) || (seqnames == NULL)) {
	fprintf(stderr,
		"Error: not enough memory to identify sequences\n");
	exit(4);
    }
    
    /* first sequence is highly special and we'll treat it
       differently so later work can be made more efficient */
    
    /* skip until first sequence */
    do {
        if ((s = fgets(line, BUFSIZ, stdin)) == NULL) {
	    /* here it can only mean one thing: */
	    fputs("Error: no sequences could be read\n", stderr);
	    exit(3);
	}
    } while (line[0] != '>');

    /* at this point we have the name of the first sequence */
    /*
     * get >sequence_name
     * jump to next white space
     * we do not expect the title line to be longer than BUFSIZ
     * if it is, we have a problem
     */
    while (!isspace(*s) && *s) s++;
    *s = '\0';
    insize = s - line;
    /* add 1 after comparison to account for trailing '\0' */
    if (insize++ == BUFSIZ) {
	fprintf(stderr, "Error:\n%s\nRidiculously long line\n", line);
	exit(5);
    }
    if ((seqnames[0] = malloc((insize) * sizeof(char))) == NULL) {
	fprintf(stderr, "could not allocate %d memory bytes\n",
		memsize);
	exit(4);
    }
    seqnames[0][0] = '\0';
    strncat(seqnames[0], line, insize);
    
    /* now get the sequence itself */
    if ((seqs[0] = malloc(BUFSIZ * sizeof(char))) == NULL) {
	    fprintf(stderr, "could not allocate %d memory bytes\n",
		    memsize);
	    exit(4);
	}
    seqs[0][0] = '\0';
    seqlen = 1;	
    s = fgets(line, BUFSIZ, stdin);
    while ((*s != '>') && (s != NULL))  {
	/* add the sequence including whitespace */
	seqlen += strlen(line);
	if ((seqs[0] = realloc(seqs[0], seqlen * sizeof(char))) == NULL) {
	    fprintf(stderr, "could not allocate %d memory bytes\n",
		    memsize);
	    exit(4);
	}
	strcat(seqs[0], line);
	s = fgets(line, BUFSIZ, stdin);
    }
    /* now compact the sequence to find its actual length */
    newlen = 0;
    for (i = 0; i < seqlen; i++) {
        if (!isspace(seqs[0][i])) {
	  seqs[0][newlen] = seqs[0][i];
	  newlen++;
	}
    }
    seqs[0][newlen] = '\0';
    seqlen = --newlen;
    /* release unused memory */
    if ((seqs[0] = realloc(seqs[0], seqlen * sizeof(char))) == NULL) {
	fprintf(stderr, "could not allocate %d memory bytes\n",
		memsize);
	exit(4);
    }
    if (newlen == 1) {
    	fprintf(stderr, "Error, sequence size is 0\n");
	exit(5);
    }   

    /* at this point we are ready to read in the rest of the sequences */
    i = 0;
    do {
      if (*s == '>') {
	if (newlen != seqlen) {
	    fprintf(stderr, "Error: sequences of unequal size\n");
	    exit(5);
	}
        i++;
	if (i > memsize) {
	    memsize += CHUNKSIZE;
	    seqs = malloc(memsize * sizeof(char *));
	    seqnames = malloc(memsize * sizeof(char *));
	    if ((seqs == NULL) || (seqnames == NULL)) {
		fprintf(stderr,
			"Error: not enough memory to identify sequences\n");
		exit(4);
	    }
	}
	/*
	 * get >sequence_name
	 * jump to next white space
	 * we do not expect the title line to be longer than BUFSIZ
	 * if it is, we have a problem
	 */
	while (!isspace(*s) && *s) s++;
	*s = '\0';
	insize = s - line;
	if (insize++ == BUFSIZ) {
	    fprintf(stderr, "Error:\n%s\nRidiculously long line\n", line);
	    exit(5);
	}
	if ((seqnames[i] = malloc(insize * sizeof(char))) == NULL) {
	    fprintf(stderr, "could not allocate %d memory bytes\n",
		    memsize);
	    exit(4);
	}
	strcat(seqnames[i], line);
	newlen = 0;
	if ((seqs[i] = malloc(seqlen * sizeof(char))) == NULL) {
	    fprintf(stderr, "could not allocate %d memory bytes\n",
		    seqlen);
	    exit(4);
	}
      }
      else {
	/*
	 * get the sequence 
	 */
	while ((*s != '\0') && (*s != '\n')) {
	    if (! isspace(*s)) {
	        seqs[i][newlen] = *s;
		newlen++;
	    }
	    if (newlen > seqlen) {
	        fprintf(stderr, "Error: sequence %d of unequal size\n", i+1);
		exit(5);
	    }
	    s++;
	}
      }	
    } while ((s = fgets(line, BUFSIZ+1, stdin)) != NULL);
    nseqs = i+1;

    /* set return values and finish */
    *ns = nseqs;
    *sl = seqlen;
    *sqs = seqs;
    *sqns = seqnames;
    return seqs[0];
}


void
write_distmat(char *outfn, char **seqs, char **seqnames,
	      int nseqs, int seqlen, double gapwt, double a,
	      enum correction_t correction, int ambig, int oflags)
{
    int i, j;
    double alpha, dist;
    FILE *outfile;

    /* open output file */
    if ((outfn[0] == '-') && (outfn[1] == '\0'))
        outfile = stdout;
    else if ((outfile = fopen(outfn, "w+")) == NULL) {
	fprintf(stderr, "Error: could not open output file %s\n", outfn);
	exit(2);
    }


    DBG(			/* print out sequences to test till here */
	for (i = 0; i < nseqs; i++) {
	   fprintf(stdout, ">%s\n\n%s\n\n", seqnames[i], seqs[i]);
	}
	/* exit(0); */
    );

    if (correction == GALTIER_GOUY) {
	/*
	 * We must run twice over the dataset, first to estimate the 
	 * mean value of alpha, then to do the actual distance calculation.
	 * This implementation is terribly inefficient as we mucst recompute
	 * some parameters twice, but is the only way to avoid having to store
	 * two N*N auxiliary matrices to hold paiwrise vtransition and 
	 * transversion counts.
	 */
	alpha = galtier_gouy_alpha(seqs, nseqs, seqlen);
    	write_header(outfile, seqnames, nseqs, gapwt, alpha, correction, oflags);

    }
    else
    	write_header(outfile, seqnames, nseqs, gapwt, a, correction, oflags);


    /* We are ready to start counting distances */
    for (i = 0; i < nseqs; i++) {
        if (oflags & PHYLIP) {
	    char snam[11];
	    int count;
	    for (count = 0; count < 10; count++)
	        if (isalnum(seqnames[i][count]))
		  snam[count] = seqnames[i][count];
		else
		  snam[count] = ' ';
	    snam[10] = '\0';
	    fprintf(outfile, "%s", snam);
        }
	/* start output with a '\t' */
	fprintf(outfile, "\t");

/*	This code is intrinsecally unparalelizable as it relies on the */
/*	sequential ordering for printing results. If we did store dist */
/*	on an array and printed it later then we could parallelize this*/
/*	but we would require (((seqlen*(seqlen-1))/2)* sizeof(double)+ */
/*	size of input file ) memory at least.                          */
/*	This program works well for 20.000 sequences. Parallelization  */
/*	would be interesting for bigger datasets, but then memory      */
/*	requirements would be huge as well. Conclusion: not for OpenMP */
/*	                                                               */
/*	#pragma omp parallel for shared(seqs, seqlen, gapwt, a, ambig) */

	for (j = 0; j < nseqs; j++) {
	    if (oflags & LOWER_TRIANGULAR) {
	      if (j > i) {
		fprintf(outfile, "\t");
		DBG(fflush(outfile));
		continue;
	      }
	    } else {
	      if (j < i) {
	        fprintf(outfile, "\t");
		DBG(fflush(outfile));
		continue;
	      }
	    }
	    if (i == j) {
		fprintf(outfile, "%6.2f\t", 0.0);
		DBG(fflush(outfile));
	    } else {

		switch (correction) {
		case LOGDET:
		    dist = dist_LogDet(seqs[i], seqs[j], seqlen);
		    break;
		case HKY:
		case HASEGAWA_KISHINO_YANO:
		    dist = dist_hasegawa_kishino_yano(seqs[i], seqs[j], seqlen);
		    break;
		case GALTIER_GOUY:
		    dist = dist_galtier_gouy(seqs[i], seqs[j], seqlen, alpha);
		    break;
		case TAJIMA_NEI:
		    dist = dist_tajima_nei(seqs[i], seqs[j], seqlen, ambig);
		    break;
		case TAMURA:
		    dist = dist_tamura(seqs[i], seqs[j], seqlen);
		    break;
		case JIN_NEI:
		    dist = dist_jin_nei(seqs[i], seqs[j], seqlen, a);
		    break;
		case KIMURA:
		    dist = dist_kimura(seqs[i], seqs[j], seqlen);
		    break;
		case JUKES_CANTOR:
		    dist =
			dist_jukes_cantor(seqs[i], seqs[j], seqlen, gapwt, ambig);
		    break;
		case UNCORRECTED:
		default:
		    dist = dist_uncorrected(seqs[i], seqs[j], seqlen, gapwt, ambig);
		    break;
		}


		/* write distance defaulting to 'EMBOSS DISTMAT' style */
		if (oflags & UNITY_DISTANCE)
		    /* write unity distance */
		    fprintf(outfile, "%4.4f\t", dist);
		else {
		    dist *= 100.0;
		    fprintf(outfile, "%6.2f\t", dist);
		    DBG(fflush(outfile));
		}
		/* NOTE: I suspect PHYLIP values are rather 1-dist
		   or simply (matches/((double)seqlen - gaps + (gaps * gapwt))) 
		   need to check when I implement corrected methods */
	    }
	}
	/* row values done, label the row and end it */
	if (!(oflags & PHYLIP))
	    fprintf(outfile, "\t%s %d", seqnames[i], i + 1);
	fprintf(outfile, "\n");
	DBG(fflush(outfile));
	DBG(fprintf(stderr, "%d..", i));
    }

    fclose(outfile);
}


/**
 * write_header
 *
 * Write header to the output file
 *
 * The header contains information on the parameters used to compute
 * the distance matrix, as well as a table header line with the numbers
 * of the sequences acting as labels for each table column.
 *
 * @param FILE *outfile     file to write to
 * @param int nseqs 	    number of sequences
 * @param double gapwt	    gap weight
 * @param enum correction_t correction	method used to compute the distances
 */
void
write_header(FILE * outfile, char **seqnames, int nseqs, 
             double gapwt, double a,
	     enum correction_t correction, int oflags)
{
    int i;

    /* write out output header */
    if (oflags & PHYLIP) {
        for (i = 0; i < nseqs; i++) {
	    char snam[11];
	    int count;
	    for (count = 0; count < 10; count++)
	        if (isalnum(seqnames[i][count]))
		  snam[count] = seqnames[i][count];
		else
		  snam[count] = ' ';
	    snam[10] = '\0';
	    fprintf(outfile, "\t%s", snam);
        }
    } else {
	fprintf(outfile, "Distance Matrix\n");
	fprintf(outfile, "---------------\n");
	if (correction == UNCORRECTED)
	    fprintf(outfile, "Uncorrected for Multiple Substitutions\n");
	else if (correction == JUKES_CANTOR)
	    fprintf(outfile, "Using the Jukes-Cantor correction method\n");
	else if (correction == KIMURA)
	    fprintf(outfile, "Using the Kimura correction method\n");
	else if (correction == TAMURA)
	    fprintf(outfile, "Using the Tamura correction method\n");
	else if (correction == TAJIMA_NEI)
	    fprintf(outfile, "Using the Tajima-Nei correction method\n");
	else if (correction == JIN_NEI)
	    fprintf(outfile, "Using the Jin-Nei correction method with a=%f\n", a);
	else if (correction == GALTIER_GOUY)
	    fprintf(outfile, "Using the Galtier-Gouy correction method with alpha= %f\n", a);
	else if ((correction == HKY) || (correction == HASEGAWA_KISHINO_YANO))
	    fprintf(outfile, "Using the HKY correction method\n");
	else if (correction == LOGDET)
	    fprintf(outfile, "Using the LogDet correction method\n");

	fprintf(outfile, "Using base positions 123 in the codon\n");
	if ((correction == UNCORRECTED) || (correction == JUKES_CANTOR))
	    fprintf(outfile, "Gap weighting is %f\n\n", gapwt);

        for (i = 0; i < nseqs; i++) {
	    fprintf(outfile, "\t%8d", i + 1);
        }
    }
    fprintf(outfile, "\n");
    DBG(fflush(outfile));
}

/**
 * dist_uncorrected
 *
 * compute distance among two aligned sequences counting only matches and gaps
 *
 *    dist = 1 - (matches / (slen - gaps) + (gaps * gapwt)))
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 * @param double gapwt	gap weight (defaults to 0.0)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_uncorrected(char *s1, char *s2, int slen, double gapwt, int ambig)
{
    double matches;
    int gaps, k;
    double dist;

    /* compute unambiguous sum of matches and gaps */
    gaps = 0;
    matches = 0.;
    for (k = 0; k < slen; k++) {
	if ((s1[k] == '-') || (s2[k] == '-'))
	    gaps++;
	else if (ambig)
	    matches += nt_ambig_match(s1[k], s2[k]);
	else if (s1[k] == s2[k])
	    matches += 1.0;
    }

    /* avoid divide by zero: return maximum distance */
    if (slen == 0) return FLT_MAX;

    /* compute uncorrected distance */
    dist = 1.0 - (matches / ((double) (slen - gaps) + ((double)gaps * gapwt)));

    return dist;
}


/**
 * dist_jukes_cantor
 *
 * compute distance among two aligned sequences counting matches and gaps
 * and applying Jukes-Cantor correction.
 *
 * p = proportion of different nt
 * n = number of nt examined
 * k = number of nt pairs that differ
 * p ~= k/n = 1 - (matches/npos)
 *
 *     d = -b log_e[1 - (p/b)]
 *
 * assuming equal frequencies for all nt, f(nt) = 1/4 and b=3/4
 *
 *
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 * @param double gapwt	gap weight (defaults to 0.0)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_jukes_cantor(char *s1, char *s2, int slen, double gapwt, int ambig)
{
    double matches;
    int gaps, k;
    double dist;

    /* compute unambiguoug sum of matches and gaps */
    matches = gaps = 0;
    for (k = 0; k < slen; k++) {
	if ((s1[k] == '-') || (s2[k] == '-'))
	    gaps++;
	else if (ambig)
	    matches += nt_ambig_match(s1[k], s2[k]);
	else if (s1[k] == s2[k])
	    matches += 1.0;
    }

    /* avoid divide by zero */
    if (slen == 0) return FLT_MAX;

    /* compute uncorrected distance */
    /*
     * p = proportion of different nt
     * n = number of nt examined
     * k = number of nt pairs that differ
     * p ~= k/n = 1 - (matches/npos)
     */
    dist = 1.0 - (matches / ((double) (slen - gaps) + ((double)gaps * gapwt)));

    /* compute Jukes-Cantor distance */
    /*
     * d = -b log_e[1 - (p/b)]
     *
     * assuming equal frequencies for all nt, f(nt) = 1/4 and b=3/4
     *
     */
    dist = -(3. / 4.) * log(1. - (dist / (3. / 4.)));

    return dist;
}


/**
 * dist_kimura
 *
 * compute distance among two aligned sequences counting matches and
 * ignoring gaps and applying Kimura correction.
 *
 * compute distance among two aligned sequences counting matches and
 * ignoring gaps and applying Kimura correction.
 *
 * Motoo Kimura (1980). A simple method for estimating evolutionary
 * rates of base substitutions through comparative studies of
 * nucleotide sequences. J. Mol.Evol. 16, 111-120.
 *
 * From the abstract:
 *
 *    Let P and Q be respectively the fractions of nucleotide sites 
 * showing type I and type II differences between two sequences 
 * compared, then the evolutionary distance per site is 
 *
 *  	K = - (1/2) ln {(1 - 2P - Q) ((1 - 2Q)^(1/2)) } 
 *
 * The evolutionary rate per year is then given by k = K/(2T), where 
 * T is the time since the divergence of the two sequences. 
 *
 * @ see docs/Kimura.pdf
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_kimura(char *s1, char *s2, int slen)
{
    int matches, transitions, transversions, npos, k;
    double dist, P, Q;
    const char *nt = "AGCTU";
    const char *pur = "AGR";
    const char *pyr = "CTUYR";

    /* compute transitions and transversions */
    matches = transitions = transversions = npos = 0;
    for (k = 0; k < slen; k++) {
	/* gaps are ignored */
	if ((s1[k] == '-') || (s2[k] == '-'))
	    continue;
	else if (strchr(nt, s1[k]) == strchr(nt, s2[k]))
	    /* both are the same unambiguous nucleotide */
	    matches++, npos++;
	else {
	    /* count transitions and transversions */
	    if (strchr(pur, s1[k]) && strchr(pur, s2[k]))
		/* R -> R */
		transitions++, npos++;
	    else if (strchr(pyr, s1[k]) && strchr(pyr, s2[k]))
		/* Y -> Y */
		transitions++, npos++;
	    else
		/* R -> Y */
		transversions++, npos++;
	}
    }
    
    /* avoid divide by zero: if no matches return maximum distance */
    if (npos == 0) return FLT_MAX;
    /*
     * P=transitions/npos;
     * Q=transversions/npos;
     * distance = -0.5 ln[ (1-2P-Q)*sqrt(1-2Q)]
     */
    P = (double) transitions / (double) npos;
    Q = (double) transversions / (double) npos;

    /* compute Kimura distance */
    dist = -0.5 * log((1 - (2 * P) - Q) * sqrt(1 - (2 * Q)));

    return dist;
}


/**
 * dist_tamura
 *
 * compute distance among two aligned sequences counting matches and
 * ignoring gaps and applying Tamura correction.
 *
 * Koichiro Tamura (1992) Estimation of the number of nucleotide
 * substitutions when there are strong transition-transversion and
 * G+C-Content biases. Mol. Biol. Evol. 9(4) 678-687.
 *
 * From the paper:
 *
 * P and 0 are the estimates of the proportions of the nucleotide 
 * sites showing, respectively, transitional and transversional differences
 * between the two sequences. We can easily calculate these estimates 
 * from the observed number of the different nucleotide matches 
 * between the two sequences compared. In this equation, theta is the 
 * estimate of theta for the two sequences. In practice, however, the 
 * theta's may not be the same for the two sequences. In this case, 
 * the following equation seems to be useful:
 *
 *                                                                                 1                            1 - theta1 - theta_2 +  2 � theta_1 � theta_2 
 * d = -(theta_1+theta_2 - 2 � theta_1 � theta_2) log_e( 1 - ----------------------------------------- P - Q) - --------------------------------------------- log_e(1 - 2Q)
 *                                                           theta_1 + theta_2 - 2 � theta_1 � theta_2                                  2
 *
 * where theta_1, and theta_2, are the estimates of the B's for the two 
 * sequences, respectively.
 *
 * @see docs/Tamura.pdf
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_tamura(char *s1, char *s2, int slen)
{
    int k;
    int matches, transitions, transversions, npos;
    int gc1, at1, gc2, at2;
    double dist, P, Q, theta1, theta2, C;
    const char *nt = "AGCTU";
    const char *pur = "AGR";
    const char *pyr = "CTUY";

    /* compute transitions, transversions and GC content */
    matches = transitions = transversions = npos = 0;
    gc1 = at1 = gc2 = at2 = 0;
    for (k = 0; k < slen; k++) {
        /* this check is for compatibility with DISTMAT but 
	   shouldn't be done as it forces ignoring valid
	   nucleotides without a corresponding counterpart
	   in the other sequence *** JR *** */
        if (strchr("ATUGC", s1[k]) && strchr("ATUGC", s2[k])) { 

	/* check GC1 content */
	if (strchr("GCS", s1[k]))
	    gc1++;
	else if (strchr("ATUW", s1[k]))
	    at1++;
	/* check GC2 content */
	if (strchr("GCS", s2[k]))
	    gc2++;
	else if (strchr("ATUW", s2[k]))
	    at2++;

	}/* anything else cannot be identified as G+C and is ignored */

	/* gaps are ignored */
	if ((s1[k] == '-') || (s2[k] == '-'))
	    continue;
	else if (strchr(nt, s1[k]) == strchr(nt, s2[k]))
	    /* both are the same unambiguous nucleotide */
	    matches++, npos++;
	else {
	    /* count transitions and transversions */
	    if (strchr(pur, s1[k]) && strchr(pur, s2[k]))
		/* R -> R */
		transitions++, npos++;
	    else if (strchr(pyr, s1[k]) && strchr(pyr, s2[k]))
		/* Y -> Y */
		transitions++, npos++;
	    else
		/* R -> Y */
		transversions++, npos++;
	}
    }
    
    /*
     * avoid divide by zero: if no matches return max. distance;
     * plus,if npos != o ==> there is at least one non-ambiguous nt
     * on each sequence and therefore (gc?+ac?) cannot be zero
     */
    if (npos == 0) return FLT_MAX;
    
    /*
     * P=transitions/npos;
     * Q=transversions/npos;
     * theta1 = GC fraction in sequence 1
     * theta2 = GC fraction in sequence 2
     * C = theta1 + theta2 - 2*theta1*theta2
     * 
     * distance = -C ln(1-P/C-Q) - 0.5(1-C) ln(1-2Q)
     */
    P = (double) transitions / (double) npos;
    Q = (double) transversions / (double) npos;
    theta1 = (double) gc1 / (double) (gc1 + at1);
    theta2 = (double) gc2 / (double) (gc2 + at2);
    C = theta1 + theta2 - (2 * theta1 * theta2);

    if ((2. * Q) > 1.) return -1.;	/* cannot use Tamura's distance */

    /* compute Tamura distance */
    dist = -C * log(1 - P / C - Q) - (0.5 * (1 - C) * log(1 - (2 * Q)));

    return dist;
}


/**
 * dist_jin_nei
 *
 * compute distance among two aligned sequences counting matches and
 * ignoring gaps and applying Jin-Nei correction.
 *
 * Li Jin and Masatoshi Nei (1990) Limitations of the Evolutionary 
 * Parsimony Method of Phylogenetic Analysis. Mol. Biol. Evol. 7(1): 82-102.
 *
 * From the paper:
 *
 * As shown by Kimura, the expected number of nucleotide substitutions 
 * per site between the two sequences compared is given by d = 2Et + 4Bt. 
 * Therefore, the estimate (d) of d can be obtained by
 *
 *    d = (a/2) [(1-2P-Q)^(-1/a) + (1/2)(1-2Q)^(-1/a) - (3/2)
 *
 * @see docs/Jin-Nei.pdf
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 * @param double a	'a' parameter for Jin-Nei algorithm
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_jin_nei(char *s1, char *s2, int slen, double a)
{
    int k;
    int matches, transitions, transversions, npos;
    int gc1, at1, gc2, at2;
    double dist, P, Q, L;
    const char *nt = "AGCT";
    const char *pur = "AGR";
    const char *pyr = "CTY";

    /* compute transitions, transversions and GC content */
    matches = transitions = transversions = npos = 0;
    gc1 = at1 = gc2 = at2 = 0;
    for (k = 0; k < slen; k++) {
	/* check GC1 content */
	if (strchr("GCS", s1[k]))
	    gc1++;
	else if (strchr("ATUW", s1[k]))
	    at1++;
	/* check GC2 content */
	if (strchr("GCS", s2[k]))
	    gc2++;
	else if (strchr("ATUW", s2[k]))
	    at2++;

	/* gaps are ignored */
	if ((s1[k] == '-') || (s2[k] == '-'))
	    continue;
	else if (strchr(nt, s1[k]) == strchr(nt, s2[k]))
	    /* both are the same unambiguous nucleotide */
	    matches++, npos++;
	else {
	    /* count transitions and transversions */
	    if (strchr(pur, s1[k]) && strchr(pur, s2[k]))
		/* R -> R */
		transitions++, npos++;
	    else if (strchr(pyr, s1[k]) && strchr(pyr, s2[k]))
		/* Y -> Y */
		transitions++, npos++;
	    else
		/* R -> Y */
		transversions++, npos++;
	}
    }
    if (a == 0.0) {
	/* needs to be calculated */
	/* for now reset it to default value of 1.0 recommended by Jin and Nei */
	a = 1.0;
    }

    /* avoid divide by zero: if no matches return maximum distance */
    if (npos == 0) return FLT_MAX;

    /*
     * P=transitions/npos;
     * Q=transversions/npos;
     *
     * L = average substituition = transition_rate + 2 * transversion_rate
     * a = (average L)^2/(variance of L)
     *
     * distance = 0.5 * a * [(1 - 2P - Q)**(-1/a) + 0.5 * (1 - 2Q)**(-1/a) - 3/2 ]
     */
    P = (double) transitions / (double) npos;
    Q = (double) transversions / (double) npos;
    /*
     * This might be used to compute 'a' but it would require two separate
     * previous runs over all the dataset to find SUM(L)/nseqs (similar to
     * Galtier-Gouy) and var(L) or storage of L_i on an auxiliary array 
     * L_i[nseqs]for variance calculation. We won't implement computation 
     * of 'a' yet. 
     *
     * Instead we'll use the recommended 1.0 value or a user supplied one. 
     *
    L = ((double) transitions +
	 2.0 * (double) transversions) / (double) npos;
     */
     
    /* compute Jin-Nei distance */
    dist = 0.5 * a * (pow((1.0 - 2.0 * P - Q), (-1.0 / a))
		      + 0.5 * pow((1.0 - 2.0 * Q),
				  (-1.0 / a)) - 3.0 / 2.0);

    return dist;
}



/**
 * dist_tajima_nei
 *
 * compute distance among two aligned sequences counting matches and
 * ignoring gaps and applying Tajima-Nei correction.
 *
 * Fumio Tajima and Masatoshi Nei (1984) Estimation of evolutionary
 * distance between nucleotide sequences. Mol. Biol. Evol. 1(3):269-285.
 *
 + From the paper:
 *
 * ... and
 *
 *    h = SUM(i=1..3) SUM(j=1..4) x_ij^2 / 2 q_i q_j)
 *
 * ... These observations suggest that an approximate estimate of delta is 
 * obtained by
 *
 *    delta = - b log_e(1 - PI/b)
 *
 * where b is the average of b_1 and b_2 and given by
 *
 *    b = (1 - SUM(i=1..4) q_i^2 + PI^2 / h) / 2
 *
 * @see docs/Tajima-Nei.pdf
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_tajima_nei(char *s1, char *s2, int slen, int ambig)
{
    int k, l, m;
    int npos;			/* positions used */
    enum nucleotide {
	A, T, C, G
    };
    int nnt[4];			/* nt counts */
    int nch[4][4];		/* change counts */
    double q[4];
    double x[4][4];
    double matches;
    char *nt = "ATUCG";
    char n1, n2;
    double h, b, pi, dist;

    /* compute required frequencies */
    npos = 0;
    matches = 0.;
    for (l = 0; l < 4; l++)
	nnt[l] = 0;
    for (l = 0; l < 4; l++)
	for (m = 0; m < 4; m++)
	    nch[l][m] = 0;

    for (k = 0; k < slen; k++) {
	n1 = s1[k];
	n2 = s2[k];
	/* anything but unambiguous codes is ignored */
	if ((strchr(nt, n1)) && (strchr(nt, n2))) {
	    npos++;
	    /* count base frequencies */
	    switch (n1) {
	    case 'A':
		nnt[A]++;
		break;
	    case 'U':
	    case 'T':
		nnt[T]++;
		break;
	    case 'G':
		nnt[G]++;
		break;
	    case 'C':
		nnt[C]++;
		break;
	    }
	    switch (n2) {
	    case 'A':
		nnt[A]++;
		break;
	    case 'T':
		nnt[T]++;
		break;
	    case 'G':
		nnt[G]++;
		break;
	    case 'C':
		nnt[C]++;
		break;
	    }
	    /* compute change frequencies */
	    if (n1 == 'A')
		switch (n2) {
		case 'A':
		    nch[A][A]++;
		    break;
		case 'T':
		    nch[A][T]++;
		    break;
		case 'G':
		    nch[A][G]++;
		    break;
		case 'C':
		    nch[A][C]++;
		    break;
	    } else if (n1 == 'T')
		switch (n2) {
		case 'A':
		    nch[T][A]++;
		    break;
		case 'T':
		    nch[T][T]++;
		    break;
		case 'G':
		    nch[T][G]++;
		    break;
		case 'C':
		    nch[T][C]++;
		    break;
	    } else if (n1 == 'G')
		switch (n2) {
		case 'A':
		    nch[G][A]++;
		    break;
		case 'T':
		    nch[G][T]++;
		    break;
		case 'G':
		    nch[G][G]++;
		    break;
		case 'C':
		    nch[G][C]++;
		    break;
	    } else if (n1 == 'C')
		switch (n2) {
		case 'A':
		    nch[C][A]++;
		    break;
		case 'T':
		    nch[C][T]++;
		    break;
		case 'G':
		    nch[C][G]++;
		    break;
		case 'C':
		    nch[C][C]++;
		    break;
		}
	    if (ambig)
	        matches += nt_ambig_match(n1, n2);
	    else if (n1 == n2)
		matches += 1.0;
	}
    }

    /* if no matches return maximum distance */
    if (npos == 0) return FLT_MAX;

    /* compute parameters */
    /* compute frequencies */
    for (l = 0; l < 4; l++) {
	q[l] = (double) nnt[l] / (2.0 * (double) npos);
	if (q[l] < EPSILON)
	    q[l] = EPSILON;
	for (m = 0; m < 4; m++) {
	    x[l][m] = (double) (nch[l][m] + nch[m][l]) / (double) npos;
	    if (x[l][m] < EPSILON)
		x[l][m] = EPSILON;
	}
    }

    /* atcg = 0123
     * h = SUM(i=0..2) SUM(j=i+1..3) ((1/2 x(ij)**2 / q(i) * q(j)
     */
    h = 0.0;
    for (l = 0; l < 3; l++)
	for (m = l + 1; m < 4; m++)
	    h += 0.5 * (x[l][m] * x[l][m]) / (q[l] * q[m]);

    matches = 0;
    for (l = 0; l < 4; l++)
	matches += nch[l][l];

    /* compute PI, proportion of different nucleotides per site */
    pi = 1.0 - ((double) matches / (double) npos);

    /* b = (1 - (sum(i=0..3) q(i)**2) + pi**2 / h) / 2 */
    b = 0.0;
    /* compute sum terms */
    for (l = 0; l < 4; l++)
	b += q[l] * q[l];
    /* compute b */
    b = 0.5 * ((1.0 - b) + ((pi * pi) / h));

    /* compute Tajima-Nei distance */
    dist = -b * log(1.0 - (pi / b));

    return dist;
}

/**
 * nt_ambig_match
 *
 *	compute ambiguous match between two nucleotides
 *
 * To compute the match we check if the two ambiguous codes may represent
 * a common nucleotide. The probability of each ambiguity code of representing
 * that nucleotide is p = 1 / (number of nts represented), so the joint match
 * probability would be p1 * p2.
 *
 * In order to speed up comparisons we convert the ambiguity code checks and
 * comparisons to binary format so we may use a binary &.
 *
 * @param char n1	first nucleotide
 * @param char n2	second nucleotide
 *
 * @return		probability of a match given the code ambiguity
 */
double nt_ambig_match(char n1, char n2)
{
    enum IUPAC_nt {
        /* assign one bit to each nt, and set corresponding bits for
	   ambiguity codes */
        A = 1,
	T = 2,
	U = 2,
	G = 4,
	C = 8,
	R = A|G,
	Y = C|T,
	M = A|C,
	K = G|T,
	S = C|G,
	W = A|T,
	B = C|G|T,
	D = A|G|T,
	H = A|C|T,
	V = A|C|G,
	N = A|C|G|T
    } bcode_val;	/* IUPAC codes in binary form for quick comparison */
    char    *acode   = "ATUGCRYMKSWBDHVN";
    int      bcode[] = {A, T, U, G, C, R, Y, M, K, S, W, B, D, H, V, N};
    double   ambig[] = {1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 4};
    int i, j;
    
    i = strchrpos(acode, n1);
    j = strchrpos(acode, n2);
    if ((i == -1) || (j == -1))
    	return 0;

    /* by using bitwise comparison, we can check for all macthes in a
      single comparison */
    if (bcode[i] & bcode[j]) {
        /* match found */
	return ((1. / ambig[i]) * (1. / ambig[j]));
    }
    else
    	return 0;	/* no match */
}



/** strchrpos
 *
 *	locate characte in string and return its position (zero offset)
 *
 * @param char *s		The string to be scanned
 * @param char c		The character we are looking for
 *
 * @return int			Position of the character in the string
 *				(zero-offsed) or -1 if not found.
 */
int strchrpos(char *s, char c)
{
    char cur;
    int count;
    
    count = -1;
    do {
        cur = *s++;
        count++;
    } while ((cur != '\0') && (cur != c));
    if (cur == c)
        return count;
    else
        return -1;
}


/**
 * galtier_gouy_alpha
 *
 * Compute alpha parameter for Galtier-Gouy distance calculation.
 *
 * the GG95 paper straight formula is
 *
 * alpha(i,j) / 2 = (log(1-2*p-q) - (0.5*log(1-2*q))) / log(1-2*q)
 *
 * However in PHYLO_WIN (by same authors) they compute alpha using
 * a slightly different (but equivalent) approach resulting from
 * a different derivation --that of the original paper of Kimura's
 * two parameter model:
 *
 * Alpha over beta --Kimura (alsurbeta_kim)
 * quotient between transition and transversion rates following
 * Kimura (1980) 2-parameter model.
 *
 * On Kimura's paper, 
 *	alpha_kimura = evolutionary rate of transition type substitutions
 *  2 * beta_kimura = evolutionary rate of transversion type substitutions
 *
 *  k = a + 2b = total rate of substitutions per site per unit time
 *
 * 4 * alpha_k = -log(1-2P-Q) + (1/2) log(1-2Q)
 *
 * 8 * beta_k = -log(1-2Q)
 *
 * Now, on PHYLO_WIN they return alpha_k / beta_k, since from Kimura's
 * paper the transversion rate is 2 * beta_k, it means that we return
 * the double of the quotient, hence (as in GG95) alpha_gg = 2 * rate,
 * yielding GG95
 *
 *  alpha_gg / 2 = -log(1-2p-q)+0.5 log(1-2q)  /   -log(1-2q) =
 *               =  log(1-2p-q)-0.5 log(1-2q)  /    log(1-2q)
 *
 * We approximate alpha using the averahe of the computed alpha for
 * every possible pair of sequences.
 *
 * @see docs/Kimura.pdf
 * @see docs/Galtier-Gouy.pdf
 *
 * @param char **seqs	    The set of aligned sequences
 * @param int    nseqs	    The number of sequences in the set
 * @param int    seqlen     The sequence length (same for all of them)
 *
 * @return double     	    Estimated alpha value
 */
double galtier_gouy_alpha(char **seqs, int nseqs, int seqlen)
{
    char n1, n2;
    int i, j, k;
    long int nvalidpairs = 0L;
    long int npos = 0L;
    long int transitions = 0L;
    long int transversions = 0L;
    char *nt = "ATGCRY";
    char *pur = "AGR";
    char *pyr = "CTY";
    double p, q, alpha;
    double accum;

    alpha = 0.0;
    for (i = 0; i < nseqs; i++) {
	for (j = i+1; j < nseqs; j++) {
	    /* count transitions and transversions */
    	    transitions = transversions = npos = 0L;
	    for (k = 0; k < seqlen; k++) {
	        n1 = seqs[i][k];
		n2 = seqs[j][k];
		if (strchr(nt, n1) && strchr(nt, n2)) {
		  npos++;
		  if (n1 != n2) {
		    if (strchr(pur, n1) && strchr(pur, n2))
		    	transitions++;
		    else if (strchr(pyr, n1) && strchr(pyr, n2))
		        transitions++;
		    else if (strchr(pur, n1) && strchr(pyr, n2))
		        transversions++;
		    else if (strchr(pyr, n1) && strchr(pur, n2))
		        transversions++;
		    /* other cases are ignored*/
		  }
		}
	    }
	    if (npos == 0) continue;
	    /* P = proportion of sites showing a transition */
	    p = (double) transitions / (double) npos;
	    /* Q = proportion of sites showing a transversion */
	    q = (double) transversions / (double) npos;
	    if (((2. * q) > 1.) || ((2.*p+q)>=1.))
	    	continue;	/* ignore this pair */

#ifdef GG95
	    /*
	     * the GG95 paper straight formula is
	     *
	     * alpha(i,j) / 2 = (log(1-2*p-q) - (0.5*log(1-2*q))) / log(1-2*q)
	     *
	     * hence one would take
	     */
	    alpha += (2. * (log(1. - 2. * p - q) - (0.5 * log(1.-(2.*q)))) /
		         (log(1.-2.*q)));

#else

	    /*
	     * this is how it is computed in PHYLO_WIN (by same authors):
	     *
	     * Alpha over beta --Kimura
	     * quotient between transition and transversion rates following
	     * Kimura (1980) 2-parameter model.
	     *
	     * On Kimura's paper, 
	     *	alpha_kimura = evolutionary rate of transition type substitutions
	     *  2 * beta_kimura = evolutionary rate of transversion type substitutions
	     *
	     *  k = a + 2b = total rate of substitutions per site per unit time
	     *
	     * 4 * alpha_k = -log(1-2P-Q) + (1/2) log(1-2Q)
	     *
	     * 8 * beta_k = -log(1-2Q)
	     *
	     * Now, on PHYLO_WIN they return alpha_k / beta_k, since from Kimura's
	     * paper the transversion rate is 2 * beta_k, it means that we return
	     * the double of the quotient, hence (as in GG95) alpha_gg = 2 * rate,
	     * yielding GG95
	     *
	     *  alpha_gg / 2 = -log(1-2p-q)+0.5 log(1-2q)  /   -log(1-2q) =
	     *               =  log(1-2p-q)-0.5 log(1-2q)  /    log(1-2q)
	     */
	    {
	    double kim_alpha, kim_beta, check;
	     
	    if ((-0.125 * log(1.-2*q)) == 0.) continue;
	     
	    kim_alpha = -0.25*log(1.-2*p-q)+0.125*log(1.-2*q);
	    kim_beta = -0.125*log(1.-2*q);
            DBG(
	    check = (-log(1.-2.*p-q)+0.5*log(1.-2.*q)) / 4;
	    check = -log(1.-2*q) / 8;
	    check = ((-log(1.-2.*p-q)+0.5*log(1.-2.*q)) / 4) / (-log(1.-2*q) / 8);
	    check = (2. * (log(1.- 2.*p-q) - (0.5 * log(1.-(2.*q)))) /
		         (log(1.-2.*q)));
	    );
	    alpha += (kim_alpha / kim_beta);
	    }
#endif
	     nvalidpairs++;
	}
    }
    /* and now compute the average */
    return (alpha / (double)nvalidpairs);
}

/**
 * dist_galtier_gouy
 *
 * Compute pairwise distance using the correction method of Galtier and
 * Gouy.
 *
 * Nicolas Galtier and Manolo Gouy (1995) Inferring phylogenies from DNA
 * sequences of unequal base compositions. Proc. Natl. Acad. Sci. USA.
 * Vol. 92, pp. 11317-11321.
 *
 * From the paper:
 *
 * Estimates theta_1^ and theta_2^ of parameters theta_1 and theta_2 are
 * given by the G+C contents of sequences 1 and 2, respectively. This
 * assumes that the equilibrium base composition has been reached in both
 * lineages. With this additional assumption, Eq. 10 reduces to
 *
 *  	theta_0^ = (theta_1^ + theta_2^) / 2
 *
 * Finally, the transition/transversion ratio is assumed to be the same
 * in all lineages. It is estimated once, from the whole data set. In the
 * substitution model of Fig. 1, the ratio between the sums of transition
 * and of transversion rates equals alpha/2. This ratio is estimated 
 * following Kimura's model for each sequence pair (i, j):
 *
 *                   ln[1-2P(i,j)-Q(i,j)] - 1/2 ln(1-2Q(i,j)) 
 * alpha(i,j) / 2 = ------------------------------------------
 *                             ln(1-2Q(i,j)
 *
 * where P(i,j) is the observed proportion of sites in sequences i and j
 * showing a transition difference and Q(i,j) is the observed proportion
 * of sites showing a transversion difference. Estimate alpha^ of
 * parameter alpha is given by the mean of alpha(i,j) values for all
 * sequence pairs. This estimate is used for all pairqise distance
 * computations.
 *
 * Substituting these five estimates into Eq. 7 provides an estimate K^
 * of the average number of nucleotide substitutions between the two 
 * sequences:
 *
 *        1                             (alpha+1)/4
 * K = - --- K_1 ln(1-2Q) + K_2[1-(1-2Q)           ]
 *        2
 *
 * with K_1 = 1 + alpha [ theta_1(1-theta_1) + theta_2(1-theta_2)]
 *
 *                                            2
 * and k_2 = alpha/(alpha+1) (theta_1-theta_2)
 *
 * @see docs/Galtier-Gouy-DNA.pdf
 * @see docs/Galtier-Gouy-Gautier.pdf
 * @see docs/Kimura.pdf
 *
 * @param char *s1  	First sequence
 * @param char *s2  	Second sequence
 * @param int slen  	Sequence length
 * @param double alpha	Alpha parameter of Galtier-Gouy
 *
 * @return double   	pairwise distance corrected by the method of Galtier
 *  	    	    	and Gouy.
 */
double dist_galtier_gouy(char *s1, char *s2, int slen, double alpha)
{
    int k;
    long int gc1, gc2, at1, at2, npos;
    char n1, n2;
    long int transitions;
    long int transversions;
    char *nt = "ATGCRY";
    char *pur = "AGR";
    char *pyr = "CTY";
    char *strong = "CGS";
    char *weak = "ATUW";
    double p, q, theta1, theta2, k1, k2, dist;
    
    if (alpha == 0.) return -1.;
    
    gc1 = gc2 = at1 = at2 = npos = transitions = transversions = 0L;
    for (k = 0; k < slen; k++){
    	n1 = s1[k];
	n2 = s2[k];
	/* count gc content in seq1 */
	if (strchr(strong, n1))
	    gc1++;
	else if (strchr(weak, n1))
	    at1++;
	/* count gc content in seq2 */
	if (strchr(strong, n2))
	    gc2++;
	else if (strchr(weak, n2))
	    at2++;

	/* count transitions and transversions */
	if (strchr(nt, n1) && strchr(nt, n2)) {
	  npos++;
	  if (n1 != n2) {
	    if (strchr(pur, n1) && strchr(pur, n2))
		transitions++;
	    else if (strchr(pyr, n1) && strchr(pyr, n2))
		transitions++;
	    else if (strchr(pur, n1) && strchr(pyr, n2))
		transversions++;
	    else if (strchr(pyr, n1) && strchr(pur, n2))
		transversions++;
	    /* other cases are ignored (-SDW...N)*/
	  }
	}
    }
    /* compute parameters */
    /* P = proportion of sites showing a transition */
    p = (double) transitions / (double) npos;

    /* Q = proportion of sites showing a transversion */
    q = (double) transversions / (double) npos;
    
    /* Sanity checks */
    if (((2. * q) > 1.) || ((2.*p+q)>=1.))
	return -1.;	/* avoid log of negative number and impossible frequencies */

    /* theta1 = CG content of sequence 1 */
    theta1 = (double) gc1 / (double) (gc1 + at1);

    /* theta2 = CG content of sequence 2 */
    theta2 = (double) gc2 / (double) (gc2 + at2);

#ifdef GG95
    /*
     * Use the Galtier-Gouy(1995) paper formula eq. 13 
     *
     * This is how it is reported to be implemented in TREECON.
     */
    /* k1 */
    k1 = 1. + (alpha * ((theta1 * (1.-theta1)) + (theta2 * (1. - theta2))));

    /* k2 */
    k2 = (alpha / (alpha + 1.)) * ((theta1 - theta2) * (theta1 - theta2));

    /* and finally compute distance k */
    dist = (- 0.5 * k1 * log(1. - 2*q)) +
	             (k2 * (1 - (1. - pow((1.-(2.*q)), ((alpha+1.)/4.)))));

#else

    /*
     * Use PHYLO_WIN (by same authors) method 
     *
     * This relies also on the paper, eq. 7.
     */
    {
        double theta0, rt, e, k11, k12, k21, k22;
	
	rt = -0.5 * log(1.-q*2.);
	theta0 = (theta1 + theta2) / 2.;
	e = 1. - exp(-(alpha + 1.) * rt / 2);
	k11 = (0.5 + alpha * theta1 * (1. - theta1)) * rt;
	k12 = (alpha / (alpha + 1.)) * ((theta0 - theta1) * (1. - 2. * theta1)) * e;
	k21 = (0.5 + alpha * theta2 * (1. - theta2)) * rt;
	k22 = (alpha / (alpha + 1.)) * ((theta0 - theta2) * (1. - 2. * theta2)) * e;
	
	dist = k11 + k12 + k21 + k22;
    }
#endif
    return dist;
}



/**
 * dist_hasegawa_kishino_yano
 *
 * Compute pairwise distance using the correction method of Hasegawa, Kishino
 * and Yano.
 *
 * Masami Hasegawa, Hirohisa Kishino, and Taka-aki Yano (1985) Dating of the
 * human-ape splitting by a molecular clock of mitochondrial DNA. J. Mol.
 * Evol. 22: 170-174.
 *
 * @note: the implementation here follows closely the implementation of the
 * method in PHYLO_WIN.
 *
 * see docs/HKY.pdf
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_hasegawa_kishino_yano(char *s1, char *s2, int slen){
    double dist, 
    	   mut_freq[16],
	   a, c, g, t, r, y,
	   P, P1, P2, Q, A1, A2, A3,
	   alpha, beta, gamma,
	   cc, ee, 
	   var1, var2, covar1_2, covar1_3, covar2_3, 
	   delta, epsilon, ksi, eta, nu, 
	   ff; 
    double l1, l2, l3;
    
    /* compute frequencies of all pairwise nt relationships */
    pairwise_frequencies(s1, s2, slen, mut_freq);
    
    /* compute transition frequency */
    P1 = mut_freq[AG] + mut_freq[GA];
    P2 = mut_freq[CT] + mut_freq[TC];
    P = P1 + P2;
    
    /* compute transversion frequency */
    Q = mut_freq[AC] + mut_freq[AT] + mut_freq[CA] + mut_freq[CG] + mut_freq[GC] +
        mut_freq[GT] + mut_freq[TA] + mut_freq[TG];

    if ((P < EPSILON) && (Q < EPSILON))
	return 0;		/* not enough mutations to tell them apart */

    /* compute nt frequencies */
    a = mut_freq[AA] +
       (mut_freq[AC] + mut_freq[AG] + mut_freq[AT] + mut_freq[CA] + mut_freq[GA] +
        mut_freq[TA]) / 2.0;

    c = mut_freq[CC] +
       (mut_freq[AC] + mut_freq[CA] + mut_freq[CG] + mut_freq[CT] + mut_freq[GC] +
        mut_freq[TC]) / 2.0;
	
    g = mut_freq[GG] +
       (mut_freq[AG] + mut_freq[CG] + mut_freq[GA] + mut_freq[GC] + mut_freq[GT] +
        mut_freq[TG]) / 2.0;

    t = mut_freq[TT] +
       (mut_freq[AT] + mut_freq[CT] + mut_freq[GT] + mut_freq[TA] + mut_freq[TC] +
        mut_freq[TG]) / 2.0;
    
    /* compute frequencies of purines and pyrimidines */
    r = a + g;
    y = c + t;
    
    if ((r+y) < EPSILON)
        return 0;    /* these were 'empty' sequences! (with no non-ambiguous nt) */
        
    /* compute l's */
    l1 = 1. - Q / (2. * r * y);
    l2 = 1. - Q / (2. * r) - (r * P1) / (2 * a * g);
    l3 = 1. - Q / (2. * y) - (y * P2) / (2 * c * t);
    
    if ((l1 <= 0.) || (l2 <= 0) || (l3 <= 0)) return -1.;
    
    A1 = (y / r) * log(l1) - log(l2) / r;
    A2 = (r / y) * log(l1) - log(l3) / y;
    A3 = -log(l1);
    
    cc = 1. - Q / (2. * r * y);
    ee = 1. - (r * P1) / (2. * a * g) - Q / (2. * r);
    ff = 1. - (y * P2) / (2. * c * t) - Q / (2. * y);
    
    delta =   1. / (2. * ee * r * r) - 1. / (2. * cc * r * r);
    epsilon = 1. / (2. * ee * a * g);
    ksi =     1. / (2. * ff * y * y) - 1. / (2. * cc * y * y);
    eta =     1. / (2. * ff * c * t);
    nu =      1. / (2. * cc * r * y);
    
    var1 = ((delta * delta * Q + epsilon * epsilon * P1) -
            (delta * Q + epsilon * P1) *
	    (delta * Q + epsilon * P1)) / slen;

    var2 = ((ksi * ksi * Q + eta * eta * P2) -
            (ksi * Q + eta * P2) *
	    (ksi * Q + eta * P2)) / slen;

    covar1_2 = (delta * ksi * Q * (1. - Q) - 
                delta * eta * Q * P2 - 
		epsilon * eta * P1 * P2) / slen;

    covar1_3 = nu * Q * (delta * (1. - Q) - epsilon * P1) / slen;
    
    covar2_3 = nu * Q * (ksi * (1. -Q) - eta * P2) /slen;
    
    gamma = (var2 - covar1_2) / (var1 + var2 - 2. * covar1_2) + 
                ((r * y) / (a * g + c * t)) * 
		((covar1_3 - covar2_3) / (var1 + var2 - 2. * covar1_2));

    dist = 2. * (a * g + c * t) * (gamma * A1 + (1. - gamma) * A2) + 
           2. * r * y * A3;

    return dist;
}

/**
 * pairwise frequencies
 *
 * compute all pairwise nucleotide combination frequencies among two sequences.
 *
 * @param char *s1	The first sequence
 * @param char *s2	The second sequence
 * @param int slen	Sequences length
 * @param double f[16]	An array to store all 4x4 combination possibilities
 *
 */
void pairwise_frequencies(char *s1, char *s2, int slen, double *f)
{
    long int i, validpos;

    /* reset frequency counts */
    for (i = 0; i < 16; i++) f[i] = 0.;

    /* count pairwise frequencies and valid positions */
    validpos = slen;
    for (i = 0; i < slen; i++) {
        switch (s1[i]) {
	case 'A':
	    switch(s2[i]) {
	        case 'A': f[AA] += 1.0; break;
		case 'G': f[AG] += 1.0; break;
		case 'C': f[AC] += 1.0; break;
		case 'T': f[AT] += 1.0; break;
		default: validpos--; break;
	    }
	    break;
	case 'C':
	    switch(s2[i]) {
	        case 'A': f[CA] += 1.0; break;
		case 'G': f[CG] += 1.0; break;
		case 'C': f[CC] += 1.0; break;
		case 'T': f[CT] += 1.0; break;
		default: validpos--; break;
	    }
	    break;
	case 'G':
	    switch(s2[i]) {
	        case 'A': f[GA] += 1.0; break;
		case 'G': f[GG] += 1.0; break;
		case 'C': f[GC] += 1.0; break;
		case 'T': f[GT] += 1.0; break;
		default: validpos--; break;
	    }
	    break;
	case 'T':
	    switch(s2[i]) {
	        case 'A': f[TA] += 1.0; break;
		case 'G': f[TG] += 1.0; break;
		case 'C': f[TC] += 1.0; break;
		case 'T': f[TT] += 1.0; break;
		default: validpos--; break;
	    }
	    break;
	default: validpos--; break;
	}
    }
    
    /* convert counts to frequencies */
    if (validpos != 0) {
        for (i = 0; i < 16; i++) f[i] /= (double) validpos;
    }
    /* else all f[i] == 0 */
}


/*
 * dist_LogDet
 *
 * Compute distances using LogDet correction of Lockhart et al.
 * 
 * Peter J. Lockhart, Michael A. Steel, Michael D. Hendy, and David 
 * Penny (1994) Recovering Evolutionary Trees under a More Realistic 
 * Model of Sequence Evolution. Mol. Biol. Evol. 11(4):605-612
 *
 * From the paper:
 *
 * For each pair of taxa x and y, we record a "ivergence matrix" Fxy. 
 * This is an r X r matrix ( r = 4 for nucleic acid sequences; and
 * r = 20 for amino acid sequences), with entries being non-negative 
 * and summing to 1. The ijth entry of Fxy is the proportion of sites 
 * in which taxa x and y have character states i andj, respectively... 
 * For each pair of taxa x and y a single dissimilarity value, dxy,
 * is calculated using the following transformation
 *
 *	dxy = -ln[det(Fxy)],
 *
 * (where det is the determinant of the matrix, and in the
 * natural logarithm-hence the name "LogDet")...
 *
 * ...However, for special models (stationary, with equal nucleotide
 * frequencies) the edge lengths can be obtained with a modification 
 * of dxy by adding either ln( det Fxx Fyy)/2 or -r. ln( r) and 
 * scaling by 1 /r, e.g., setting
 *
 *    d'xy = {dxy + [ln(det FxxFyy)]/2)}/r
 *
 * where F, and Fyy are matrices whose entries give the frequencies 
 * of character states for taxa x and y.
 * 
 *
 * @note 	We loosely follow PHYLO_WIN implementation here.
 * 
 * @see docs/Lockhart_et_al.pdf
 *
 * @param char *s1	sequence 1
 * @param char *s2	sequence 2
 * @param int slen	sequence length (both sequences must have same length)
 *
 * @return double	distance calculated per nucleotide position
 */
double dist_LogDet(char *s1, char *s2, int slen)
{
    double mut_freq[16], Fxy[4][4];
    double a1, c1, g1, t1;
    double a2, c2, g2, t2;
    double d;
    /* next are for the inline determinant evaluation by Gauss elimination */
    int i, j, k;
    double tmp, factor;
    
    pairwise_frequencies(s1, s2, slen, mut_freq);
    
    /* we might probably take advantage of C two-dimensional arrays memory layout */
    for (i = 0; i < 16; i++) 
        Fxy[i/4][i%4] = mut_freq[i];

    /* compute nucleotide frequencies in each sequence */
    a1 = c1 = g1 = t1 = a2 = c2 = g2 = t2 = 0.;
    for (i = 0; i < 4; i++) {
	a1 += Fxy[0][i];
	c1 += Fxy[1][i];
	g1 += Fxy[2][i];
	t1 += Fxy[3][i];
	
	a2 += Fxy[i][0];
	c2 += Fxy[i][1];
	g2 += Fxy[i][2];
	t2 += Fxy[i][3];
    }
    
    /* d = determinant(Fxy); */
    /* *** JR *** */
    /* UGLY, UGLY, UGLY. Compute determinant by inline Gauss elimination */
    /* This should rather be done with auxiliary LU-decomposition and */
    /* matrix multiplication subroutines, but I feel lazy for debugging */
    /* Since we do not need Fxy any longer, we can do it in place */
    d = 1.;
    for (i = 0; i < 3; i++) {
        /* check if the diagonal element is zero */
        if ((Fxy[i][i] < EPSILON) && (Fxy[i][i] > -EPSILON)) {
	   /* if the diagonal element is zero, resort the matrix */
	   for (j = i+1; j < 4; j++) {
	     if ((Fxy[j][i] > EPSILON) || (Fxy[j][i] < -EPSILON)) {
	         /* swap columns */
	         for (k = 0; k < 4; k++) {
		     tmp = Fxy[i][k];
		     Fxy[i][k] = Fxy[j][k];
		     Fxy[j][k] = tmp;
		 }
		 /* when we do a swap, the determinant changes sign */
		 d = -d;
		 break;	    /* next diagonal element */
	     }
	   }
	}
    	if ((Fxy[i][i] < EPSILON) && (Fxy[i][i] > -EPSILON)) {
	    /* if it still is zero, then the determinant is zero */
	    d = 0.;
	    /* if the determinant is zero, then we cannot compute the distance */
	    return -1.;
	}
	/* eliminate lower rows */
	for (j = i+1; j < 4; j++) {
	    if ((Fxy[j][i] > EPSILON) || (Fxy[j][i] < -EPSILON)) {
	        factor = Fxy[j][i] / Fxy[i][i];
		for (k = i; k < 4; k++)
		    Fxy[j][k] -= factor * Fxy[i][k];
	    }
	}
    }
    for (i = 0; i < 4; i++)
        d *= Fxy[i][i];

    /* *** JR *** */

    if (d < EPSILON) return -1.;

    d = (-log(d) + (log(a1 * c1 * g1 * t1 * a2 * c2 * g2 * t2) / 2.)) / 4.;
    return d;
}
