/**
 * dlentry.java - entry in a drawing list
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
 *      This product includes software developed by Will Ware.
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

import java.awt.*;
import java.util.Vector;
import atom;

public class dl_atom extends dlentry
{
  public static final String rcsid =
  "$Id: dl_atom.java,v 1.4 1998/05/20 17:30:50 pcm Exp $";
  private double x,y,z, r1;  // screen coordinates for first atom
  private static double rvec[] = new double[3];
  private atom atm1;
  static int cnt = 0;
  public dl_atom (atom a, view v)
  {
    atm1 = a;
    vw = v;
    v.xyzToScreen (a.x, rvec);
    x = rvec[0];
    y = rvec[1];
    z = rvec[2];
    r1 = radiusRatio * a.covalentRadius () * v.zoomFactor;
    r1 *= v.perspectiveFactor (rvec);
  }
  public double zvalue ()
  {
    return z;
  }
  public double xvalue ()
  {
    return x + r1;
  }
  public double yvalue ()
  {
    return y + r1;
  }
	// square of distance from displayed edge of atom, negative if inside atom
  public double pixelSquaredDistance(double[] scrPos)
  {
    double dx = x - scrPos[0];
    double dy = y - scrPos[1];
    return (dx * dx + dy * dy) - r1*r1;
  }
  public void quickpaint (Graphics g)
  {
	g.drawOval ((int)x, (int)y, (int)(2 * r1), (int)(2 * r1));
  }

  public void paint (Graphics g)
  {
    int iradius = (int)r1;
    int c = ((PDBAtom)atm1).getColorIndex();
    ShadeRef.DrawSphere((int)x, (int)y, (int)z, iradius, c);
  }
}
