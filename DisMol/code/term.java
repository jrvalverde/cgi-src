/**
 * term.java - MM2-style energy term, for computing interatomic forces
 * Copyright (c) 1997 Will Ware, all rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    or its derived works must display the following acknowledgement:
 * 	This product includes software developed by Will Ware.
 * 
 * This software is provided "as is" and any express or implied warranties,
 * including, but not limited to, the implied warranties of merchantability
 * or fitness for any particular purpose are disclaimed. In no event shall
 * Will Ware be liable for any direct, indirect, incidental, special,
 * exemplary, or consequential damages (including, but not limited to,
 * procurement of substitute goods or services; loss of use, data, or
 * profits; or business interruption) however caused and on any theory of
 * liability, whether in contract, strict liability, or tort (including
 * negligence or otherwise) arising in any way out of the use of this
 * software, even if advised of the possibility of such damage.
 */

import java.lang.Math;
import java.util.Vector;
import atom;

public abstract class term
{
  public static final String rcsid =
  "$Id: term.java,v 1.1.1.1 1998/02/10 19:03:53 pcm Exp $";
  public atom[] myAtoms;
  public abstract void enumerate (Vector atomList, Vector termList);
  public abstract void computeForces();
  public static final double pi = 3.1415926;

  // atomic numbers, used to look up coefficients
  protected final static int H = 1;
  protected final static int C = 6;
  protected final static int N = 7;
  protected final static int O = 8;
  public term ()
  {
    // needed for termList
  }
  public static boolean redundant (term t)
  {
    // the default case, overload for specifics
    return false;
  }
  public boolean redundant (Vector termList)
  {
    int i;
    for (i = 0; i < termList.size (); i++)
      {
	term t = (term) termList.elementAt (i);
	if (t.redundant (this))
	  return true;
      }
    return false;
  }
  // handy vector arithmetic
  protected double[] crossProduct (double[] x, double[] y)
  {
    double[] z = new double[3];
    z[0] = x[1] * y[2] - x[2] * y[1];
    z[1] = x[2] * y[0] - x[0] * y[2];
    z[2] = x[0] * y[1] - x[1] * y[0];
    return z;
  }
  protected double dotProduct (double[] x, double[] y)
  {
    return x[0] * y[0] + x[1] * y[1] + x[2] * y[2];
  }
  protected double veclen (double[] x)
  {
    return Math.sqrt (dotProduct (x, x));
  }
  protected double[] scalevec (double m, double[] x)
  {
    double[] z = new double[3];
    int i;
    for (i = 0; i < 3; i++)
      z[i] = m * x[i];
    return z;
  }
  protected double sqrt (double x)
  {
    if (x <= 0.0) return 0.0;
    return Math.sqrt (x);
  }
  protected double acos (double x)
  {
    if (x >= 1.0) return pi / 2;
    if (x <= -1.0) return -pi / 2;
    return Math.acos (x);
  }
}
