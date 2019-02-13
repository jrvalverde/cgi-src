#include <stdio.h>
#include <stdlib.h>
#include <pwd.h>
#include <sys/types.h>
#include <errno.h>

main(int argc, char *argv[]) {
  char *user;

  printf("Content-type: text/html\n\n");
  printf("<HTML><BODY><H1>\n");

  user = getenv("REMOTE_USER");
  printf("user = [%s]\n", user);

  printf("\n</H1></BODY></HTML>\n");

}
