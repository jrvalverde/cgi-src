/*
	hth.c
	v 1.0.5
	28 December 1993

	This simple program predicts whether a protein contains a helix-turn-
		helix motif, using the method of:

	Dodd, I. B., and J. B. Egan. 1990.  Improved detection of helix-turn-
		helix DNA-binding motifs in protein sequences. Nucleic Acids Res. 
		18:5019-5026.

	Version 1.0.5 corrects bugs in the previously released version, 1.0.1.

	This code written and donated to the public domain by:
		Conrad Halling
		Department of Molecular Genetics and Cell Biology
		University of Chicago
		920 E 58th St
		Chicago IL 60637

		current address:

		Monsanto Co.
		700 Chesterfield Parkway North
		St. Louis MO 63198
		e-mail: chhall@ccmail.monsanto.com

	How to compile this program:

		This program is written in ANSI C using THINK C 5.0.4.
		On the UNIX system at the University of Chicago (running SunOS Release 4.1.1),
		    this code will not compile using cc but will compile using either gcc or acc.
		For example, to compile this program, you would type
			acc -o hth hth.c <return>
		This means, "start the acc (ANSI C Compiler) program, send
			the output to a file called "hth", and take the input
			from the file "hth.c".
		When the program is compiled, you run it by typing "hth"
			at the prompt.

	tabs = 4

		When using vi under UNIX, you can set the tabs to 4 by opening the
			file, typing escape (to go into command mode), colon (":") to go
			to the command line, and "set tabstop=4" (without the quotes).

	Format of input protein sequence:

		One protein sequence per file
		Single-letter code in upper case and/or lower case
		White space characters (space, tab, return, etc.) are ignored
		The program will abort if an invalid character is found
*/

#include <ctype.h>
#include <limits.h>
#include <stddef.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#ifndef __HTH__
	#define __HTH__
	
	#ifndef TRUE
		#define TRUE				 1
	#endif
	
	#ifndef FALSE
		#define FALSE				 0
	#endif

	#define AMINO_ACIDS_COUNT		20
	#define MAX_SEQUENCE_LENGTH	 20000
	
	#define WINDOW_SIZE				22		/* These values from Table 3	*/
	#define NON_HTH_MEAN_SCORE	   238.71	/* of Dodd and Egan (1991)		*/
	#define NON_HTH_STD_DEV		   293.61

	/*
		Errors
	*/
	
	#define NO_ERROR				 0
	#define SEQUENCE_TOO_SHORT		 1
	#define OUT_OF_MEMORY			 2
	#define QUIT					 3
	#define INVALID_CHAR			 4
	
	/*
		Function prototypes
	*/
	
	void	DisplayError(
				short			error );
	void	DisplayResults(
				double			convertedScore,
				size_t			maxScorePosition,
				const char		*sequence );
	short	GetAminoAcid(
				char			residue );

#endif


const short weightMatrix[ AMINO_ACIDS_COUNT ][ WINDOW_SIZE ] =
	{
	/* A (alanine) */
		-125, -194,  -84,   70,   36,   54,  238,  -15,   77,   26, -194,
		-194,  -56,  -84,   14,   77,  -56,  -56,  -56,   46, -195,   36,
	/* C (cysteine) */
		 -64,  -64,  -63,  -63,  -64,  -64,  -64,  -64,  -64,   47,   47,
		 -63,  -63,  -64,  -64,  -64,  -64,  -64,  -64,  -63,  -64,   47,
	/* D (aspartate) */
		-156, -154, -156, -154,  109, -156, -156,  109, -154, -156,    6,
		-156, -154,  -85, -156, -156, -156, -156, -154, -154, -156,  -85,
	/* E (glutamate) */
		 -31,   -9, -171,   70,  156, -171, -171,  107,   50,  -60,  -60,
		-171,  -60,   78,   86, -171, -171, -101, -171, -170,   86,    9,
	/* F (phenylalanine) */
		  10, -130,   10, -130, -130,   10, -130, -129, -130,  102, -130,
		-130, -130, -130, -129, -130, -130, -129, -129, -129,  180, -130,
	/* G (glycine) */
		  30,    5, -190,  -51, -191, -191,   18, -191, -191, -191,  202,
		-191,  -10, -191,    5, -190, -191,  -80, -190, -190, -191,  -51,
	/* H (histidine ) */
		  62,   33,  -76,  -76,   -7,  -78,  -78,   33,   -7,  -78,   84,
		 -78,   33,   33,  -78,   -7,  -78,   -7,   62,   84,  -78,   -7,
	/* I (isoleucine ) */
		  75, -156,  101,  -45,  -86,  116, -156,  -16,   65,  -16, -156,
		 128, -156,  -86, -156, -155,  188, -155,  -16,   53,  122, -155,
	/* K (lysine ) */
		 -31,  -31,   10,   70,   79, -170, -170,   94,   70, -171,   -9,
		-100, -100,   25, -100, -170, -171,   -9,   38,  -31,   -9,  101,
	/* L (leucine) */
		  66, -212,   72, -213, -212,  144, -213, -102,   37,  132, -213,
		  97, -213, -142, -212, -212,   97, -212, -212,   37,   88, -213,
	/* M (methionine) */
		 122,  -74,   -3,  -73,  -73,  -73,  -74,  -74,   88,  122,  -73,
		 158,  -74,  -74,   -3,  -74,   -3,  -74,  -74,   -3,  -74,  -73,
	/* N (asparagine) */
		-137,   72, -137, -136, -137, -137, -137,  -67, -136, -136,  128,
		-137,   72, -136,    2,  -67, -137,    2,   84, -137, -137,  104,
	/* P (proline) */
		-156,   23, -157, -156, -157, -157, -157, -157, -157, -157, -157,
		-157,  -46,  101,   39, -157, -157, -157, -157, -157, -157,  -46,
	/* Q (glutamine) */
		 -60, -130,  175,   90,  110, -131, -131,   90,   78, -131, -131,
		 -60, -130,  154,   65,  119, -131,  -20,  119,   31,  -20,   90,
	/* R (arginine) */
		  65,   76,  110,   65,    7, -155, -154,  123,   76, -155, -154,
		-155, -154,  129,   54,   40, -155,  129,  179,  -45, -155,  123,
	/* S (serine) */
		-118,   96, -188,   21,  -48, -187,   -8, -187, -118, -118,  -77,
		-188,  174, -187,  135,  -26, -188,  150,  -77, -188, -187,  -26,
	/* T (threonine) */
		  11,  149,  -59,   80, -169,   -8, -170,  -99,  -99,  -30, -170,
		  -8,  131,  -30,  -59,  198, -170,  -30, -169, -170,  -30,  -59,
	/* V (valine) */
		  17,  -67, -177, -177, -108,  100, -178, -178, -108,   71, -178,
		 160, -178,  -16,  -67,  -67,  169, -178,   17,   31,   17, -178,
	/* W (tryptophan) */
		  44,  -26,  -26,  -26,  -26,  -26,  -26,  -26,  -26,  -26,  -26,
		 -26,  -26,  -25,  -26,  -25,  -26,  -26,   44,  279,  -26,  -26,
	/* Y (tyrosine) */
		 -40, -110,   30,    1, -110, -109, -110, -110,  -40,   30, -110,
		-109, -109,  -40, -110,  -40, -110,  162,   52,   86, -110, -110
	};

const char aminoAcidsString[] = "ACDEFGHIKLMNPQRSTVWYacdefghiklmnpqrstvwy";


int main( void )
	{
	char		fileName[ FILENAME_MAX ],
				format[ 12 ],
				residue,
				*sequence;
	int			resultsDisplayed = FALSE,
				theChar;
	short		aminoAcid,
				fileNameEntered,
				maxScore,
				status,
				tempScore;
	size_t		i,
				j,
				length,
				maxWindowPosition,
				maxScorePosition,
				position,
				sequenceLength;
	double		convertedScore;
	FILE		*sequenceFile;
	
	status = NO_ERROR;
	
	printf( "Welcome to hth, a program that predicts whether a protein contains\n" );
	printf( "a helix-turn-helix motif.\n\n" );
	printf( "For more information, please read\n\n" );
	printf( "    Dodd, I. B., and J. B. Egan. 1990.  Improved detection of\n" );
	printf( "        helix-turn-helix DNA-binding motifs in protein sequences.\n" );
	printf( "        Nucleic Acids Res. 18:5019-5026.\n\n" );
	printf( "Please be sure that your protein sequence is in \"plain\" format\n" );
	printf( "using the single-letter code.\n\n" );

	/*
		Allocate memory for the protein sequence.
	*/

	sequence = ( char * ) malloc( MAX_SEQUENCE_LENGTH * sizeof( char ) );
	if ( NULL == sequence )
		status = OUT_OF_MEMORY;

	while ( NO_ERROR == status )
		{
	
		/*
			Get the name of the sequence file.
		*/

		fileNameEntered = FALSE;
		while ( TRUE )
			{
			printf( "Name of protein file (q/Q = Quit):  " );
			sprintf( format, "%%%us", FILENAME_MAX );
			scanf( format, fileName );
			length = strlen( fileName );
			if ( 0 != length )
				{
				/*
					Check for quit command.
				*/
				
				if ( ( 1 == length ) &&
					( ( fileName[ 0 ] == 'q' ) || ( fileName[ 0 ] == 'Q' ) ) )
					{
					status = QUIT;
					break;
					}
				else
					{
					/*
						Open the sequence file.
					*/
			
					sequenceFile = fopen( fileName, "r" );
					if ( NULL == sequenceFile )
						printf( "File not found!\n\n" );
					else
						break;
					}
				}
			}

		if ( NO_ERROR == status )
			{
			/*
				Read the sequence from sequenceFile into sequence[].
			*/
			
			i = 0;
			while ( i < MAX_SEQUENCE_LENGTH )
				{
				/*
					Stop reading at end of file.
				*/
				
				theChar = getc( sequenceFile );
				if ( feof( sequenceFile ) )
					break;
	
				/*
					Skip white space characters.
				*/
				
				if ( isspace( theChar ) )
					continue;
	
				/*
					GetAminoAcid will return AMINO_ACIDS_COUNT if the character
						is not found in AminoAcidsString[].
				*/
	
				if ( AMINO_ACIDS_COUNT == GetAminoAcid( ( char ) theChar ) )
					{
					status = INVALID_CHAR;
					break;
					}
	
				/*
					The character is valid; add it to the string.
				*/
	
				sequence[ i++ ] = ( char ) theChar;
				}
	
			if ( NO_ERROR == status )
				sequence[ i++ ] = 0x00;
		
			fclose( sequenceFile );
			}
	
		if ( NO_ERROR == status )
			{
			sequenceLength = strlen( sequence );
			if ( sequenceLength < WINDOW_SIZE )
				status = SEQUENCE_TOO_SHORT;
			}
		
		if ( NO_ERROR == status )
			{
			/*
				Calculate the highest score for the sequence.
			*/
	
			resultsDisplayed = FALSE;
			maxScore = SHRT_MIN;	/* defined in <limits.h> */
			maxScorePosition = 0;
			maxWindowPosition = sequenceLength - WINDOW_SIZE;
			for ( i = 0; i < maxWindowPosition; i++ )
				{
				tempScore = 0;
				for ( j = 0; j < WINDOW_SIZE; j++ )
					{
					position = i + j;
					residue = sequence[ position ];
					aminoAcid = GetAminoAcid( residue );
					tempScore += weightMatrix[ aminoAcid ] [ j ];
					}
				if ( tempScore > maxScore )
					{
					maxScore = tempScore;
					maxScorePosition = i;
					convertedScore =
						( ( double ) maxScore - NON_HTH_MEAN_SCORE ) /
							( NON_HTH_STD_DEV );
					if ( convertedScore >= 2.5 )
						{
						DisplayResults(
							convertedScore,
							maxScorePosition,
							sequence );
						resultsDisplayed = TRUE;
						maxScore = SHRT_MIN;
						}
					}
				}
			if ( !resultsDisplayed )
				DisplayResults( convertedScore, maxScorePosition, sequence );
			}
		if ( ( QUIT != status ) && ( NO_ERROR != status ) )
			{
			DisplayError( status );
			status = NO_ERROR;
			}
		}
	DisplayError( status );
	}


void DisplayResults(
	double			convertedScore,
	size_t			maxScorePosition,
	const char		*sequence )
	{
	char			maxScoreString[ WINDOW_SIZE + 1 ];
	short			i,
					percentage;
	size_t			position;
	
	printf(
		"The score is %0.2f at position %lu.\n", 
		convertedScore,
		maxScorePosition + 1 );

	for ( i = 0; i < WINDOW_SIZE; i++ )
		{
		position = maxScorePosition + i;
		maxScoreString[ i ] = sequence[ position ];
		}
	maxScoreString[ i ] = 0x00;

	printf(
		"The sequence at this position is %s.\n",
		maxScoreString );

	if ( convertedScore >= 4.5 )
		percentage = 100;
	else if ( convertedScore >= 4.0 )
		percentage = 90;
	else if ( convertedScore >= 3.5 )
		percentage = 71;
	else if ( convertedScore >= 3.0 )
		percentage = 50;
	else if ( convertedScore >= 2.5 )
		percentage = 25;

	if ( convertedScore < 2.5 )
		printf( "This score is not significant.\n\n" );
	else
		{
		printf(
			"This score suggests an approximately %hd%% probability that ",
			percentage );
		printf( "this protein\ncontains a helix-turn-helix motif.\n\n" );
		}
	}
				

short GetAminoAcid(
	char		residue )
	{
	short		i,
				limit;
	
	limit = strlen( aminoAcidsString );
	for ( i = 0; i < limit; i++ )
		{
		if ( residue == aminoAcidsString[ i ] )
			break;
		}
	if ( i >= AMINO_ACIDS_COUNT )
		i -= AMINO_ACIDS_COUNT;

	return ( i );
	}


void DisplayError(
	short		error )
	{
	
	char		errorString[5][80] =
		{
		"\n\n",
		"The protein sequence is too short to analyze.\n\n",
		"There is insufficient memory to continue.\n\n",
		"Good-bye!\n\n",
		"There is an invalid character in the protein sequence.\n\n"
		};

	printf( errorString[ error ] );
	}
