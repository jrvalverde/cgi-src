# For gcc
CC= gcc
# For ANSI compilers
#CC= cc

#For Optimization
#CFLAGS= -O2
#For debugging
CFLAGS= -g

RM= /bin/rm -f
#--- You shouldn't have to edit anything else. ---

.c.o: 
	$(CC) -c $(CFLAGS) $<

all: query post-query animate count random

att:
	make all CC=gcc CFLAGS="-O2 -DNO_STDLIB_H"

aux:
	make all CC=gcc CFLAGS=-O2

aix3:
	make all CC=gcc

aix4:
	make all CC=gcc

hp-gcc:
	make all CC=gcc

hp-cc:
	make all CC=cc CFLAGS=-Ae

linux:
	make all CC=gcc

netbsd:	
	make all CC=cc

next:
	make all CC=cc

osf1:
	make all CC=cc

sgi:
	make all CC=cc

solaris:
	make all CC=gcc

sunos:
	make all CC=gcc

svr4:
	make all CC=cc

ultrix:
	make all CC=gcc

post-query: post-query.o util.o
	$(CC) post-query.o util.o -o post-query

query: query.o util.o
	$(CC) query.o util.o -o query

animate: animate.o
	$(CC) animate.o -o animate

count: count.o
	$(CC) count.o -o count

random: random.o
	$(CC) random.o -o random

change-passwd: change-passwd.o util.o
	$(CC) change-passwd.o util.o -o sec-cgi/change-passwd

clean:
	rm -f *.o post-query query sec-cgi/change-passwd animate count random

