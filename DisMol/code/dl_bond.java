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

public class dl_bond extends dlentry
{
  public static final String rcsid =
  "$Id: dl_bond.java,v 1.4 1998/05/20 17:30:51 pcm Exp $";
  private double x1, y1, z1, r1;  // screen coordinates for first atom
  private double x2, y2, z2, r2;
		// temp arrays allocated here to avoid repeated mem allocs
  private static double rvec[] = new double[3];
  private static double tvec[] = new double[3];
  private atom atm1, atm2;
  // gap between lines in double and triple bonds, in angstroms
  private static final double gap = 0.2;
  public dl_bond (atom a1, atom a2, view v)
  {
    atm1 = a1;
    atm2 = a2;
    vw = v;

    v.xyzToScreen (a1.x, rvec);
    x1 = rvec[0];
    y1 = rvec[1];
    z1 = rvec[2];
    r1 = radiusRatio * a1.covalentRadius () * v.zoomFactor;
    r1 *= v.perspectiveFactor (rvec);

    v.xyzToScreen (a2.x, rvec);
    x2 = rvec[0];
    y2 = rvec[1];
    z2 = rvec[2];
    r2 = radiusRatio * a2.covalentRadius () * v.zoomFactor;
    r2 *= v.perspectiveFactor (rvec);
  }
  public double zvalue ()
  {
    return (z1 + z2) / 2;
  }
  public void quickpaint (Graphics g)
  {
     Color c1 = atm1.color ();
     Color c2 = atm2.color ();
		// avoid blending with background
     if (c1 == Color.black) c1 = Color.gray;
     if (c2 == Color.black) c2 = Color.gray;
     rvec[0] = x1;
     rvec[1] = y1;
     rvec[2] = z1;
     tvec[0] = x2;
     tvec[1] = y2;
     tvec[2] = z2;
     drawBondLine (g, c1, c2, rvec, tvec);
  }
  public void paint (Graphics g)
  {
    int i;
    double xdiff = (x1 - x2);
    double ydiff = (y1 - y2);
    double zdiff = (z1 - z2);
    double lensq = xdiff * xdiff + ydiff * ydiff + zdiff * zdiff;
    double len = Math.sqrt(lensq);
    ShadeRef.DrawCylinder((int)(x1 - r1 * xdiff / len),
			  (int)(y1 - r1 * ydiff / len),
			  (int)(z1 - r1 * zdiff / len),
			  (int)(x2 + r2 * xdiff / len),
			  (int)(y2 + r2 * ydiff / len),
			  (int)(z2 + r2 * zdiff / len),
			  atm1.getColorIndex(), atm2.getColorIndex(),
			  (int)(0.4*(r1 + r2)/2));
  }
}
