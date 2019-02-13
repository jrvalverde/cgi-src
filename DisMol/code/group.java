/**
 * group.java - group of atoms and terms
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
import java.awt.image.ImageProducer;
import java.lang.Math;
import java.util.Vector;
import atom;
import PDBAtom;
import ShadeRef;
import Bond;
import term;
import view;
import dlentry;
import dl_atom;
import dl_bond;
import dlforce;

public class group
{
  public static final String rcsid =
  "$Id: group.java,v 1.8 2000/04/15 23:32:12 pcm Exp $";
  public Vector atomList;
  public Vector bondList;
  public Vector termList;
  private boolean needToEnumerateTerms;
  private boolean showForces = false;
  private Vector drawingList;
  public Dimension mypanel_size;
  public view v;
  public double forceMultiplier = 100.0;

  private Image backBuffer;
  private Graphics backGC;
  private Dimension backSize;

  public group ()
  {
    v = new view ();
    empty ();
  }
  public group (Panel p)
  {
    mypanel_size = p.size();
    v = new view ();
    empty ();
  }
  public final void setPanelSize(Dimension d)
  {
    mypanel_size = d;
  }
  public void updateViewSize ()
  {
    v.updateSize (mypanel_size.width, mypanel_size.height);
  }
  public void setDefaultZoomFactor()
  {
    v.zoomFactor = 25;
  }
  public void empty ()
  {
    needToEnumerateTerms = true;
    atomList = new Vector ();
    bondList = new Vector ();
    termList = new Vector ();
  }
  public void setShowForces (boolean sf)
  {
    showForces = sf;
  }
  public atom selectedAtom (double[] scrPos, boolean picky)
  {
    int i;
    atom a, amin;
    double sqDist, minSqDist = 0;
    amin = null;
    double zpos = -1.e9;
    for (i = 0; i < atomList.size (); i++)
      {
        a = (atom) atomList.elementAt (i);
	dl_atom dla = new dl_atom(a,v);
	sqDist = dla.pixelSquaredDistance(scrPos);
	if (sqDist < 0)
	  {
	    if(dla.zvalue() > zpos)
	      {
		minSqDist = 0;
		amin = a;
		zpos = dla.zvalue();
	      }
	  }
        else if (sqDist < minSqDist || i == 0)
          {
            minSqDist = sqDist;
            amin = a;
          }
      }
    // if we're picky, we need to be right on top of the atom
    if (!picky || minSqDist < 3)
      return amin;
    else
      return null;
  }
  public void addAtom (atom a)
  {
    needToEnumerateTerms = true;
    atomList.addElement (a);
  }
  public void addAtom (atom a, double[] scrPos)
  {
    needToEnumerateTerms = true;
    a.x = v.screenToXyz (scrPos);
    atomList.addElement (a);
  }
  public void addAtom (atom a, double x0, double x1, double x2)
  {
    needToEnumerateTerms = true;
    a.x[0] = x0;
    a.x[1] = x1;
    a.x[2] = x2;
    atomList.addElement (a);
  }
  public void deleteAtom (atom a)
  {
    int i;
    if (atomList.size () == 0)
      return;
    needToEnumerateTerms = true;
    // remove all bonds connected to the atom
    for (i = 0; i < atomList.size (); i++)
      {
	atom a2 = (atom) atomList.elementAt (i);
	if (a2.bonds.contains (a))
	  a2.bonds.removeElement (a);
      }
    // remove the atom
    atomList.removeElement (a);
    deleteBonds(a,null);
  }
  protected void deleteBonds(atom a1,atom a2)
  {
    int i;
    for (i = 0; i < bondList.size(); ++i)
    {
      Bond b = (Bond)bondList.elementAt(i);
      if(b.contains(a1) && (a2 == null || b.contains(a2)))
      {
	bondList.removeElement (b);
	if(a2 != null)
	  break;		// assume no duplicate bonds
      }
    }
  }
  public void addBond (atom a1, atom a2)
  {
    if (a1 == null || a2 == null)
      return;
    if (a1.bonds.contains (a2))
      return;
    needToEnumerateTerms = true;
    a1.bonds.addElement (a2);
    a2.bonds.addElement (a1);
    a1.rehybridize ();
    a2.rehybridize ();
    bondList.addElement(new Bond(a1,a2,Bond.NormBondFlag));
  }
  public void addBond (int a1, int a2)
  {
    atom at1 = (atom) atomList.elementAt (a1),
      at2 = (atom) atomList.elementAt (a2);
    addBond (at1, at2);
  }
  public void deleteBond (atom a1, atom a2)
  {
    if (!a1.bonds.contains (a2))
      return;
    needToEnumerateTerms = true;
    a1.bonds.removeElement (a2);
    a2.bonds.removeElement (a1);
    a1.rehybridize ();
    a2.rehybridize ();
    deleteBonds(a1,a2);
  }
  public void resizeAtoms(double parm)
  {
    int i;
    PDBAtom.atomsize_parm = parm;
    for (i = 0; i < atomList.size (); i++)
      ((PDBAtom)atomList.elementAt(i)).setParms();
  }
  public void centerAtoms ()
  {
    int i, j;
    atom a;
    double[] x = { 0, 0, 0 };
    for (i = 0; i < atomList.size (); i++)
      {
        a = (atom) atomList.elementAt (i);
        for (j = 0; j < 3; j++)
          x[j] += a.x[j];
      }
    for (j = 0; j < 3; j++)
      x[j] /= atomList.size ();
    for (i = 0; i < atomList.size (); i++)
      {
        a = (atom) atomList.elementAt (i);
        for (j = 0; j < 3; j++)
          a.x[j] -= x[j];
      }
  }
  private void drawAxisLine(Graphics g, boolean is_wireframe,
			    double[] start, double[] stop, int index,
			    double angstroms_between_ticks,
			    double angstroms_per_tick_side)
  {
    double[] scr1 = v.xyzToScreen (start);
    double[] scr2 = v.xyzToScreen (stop);
    if(is_wireframe)
      g.drawLine((int)scr1[0],(int)scr1[1],(int)scr2[0],(int)scr2[1]);
    else
    {
      ShadeRef.DrawLine((int)scr1[0],(int)scr1[1],(int)scr1[2],
			(int)scr2[0],(int)scr2[1],(int)scr2[2], 1, 1);
    }
    if(index >= 0)
    {
      double d[] = {stop[0] - start[0], stop[1] - start[1], stop[2] - start[2]};
      double length = Math.sqrt(d[0]*d[0] + d[1]*d[1] + d[2]*d[2]);
      double num_ticks = length/angstroms_between_ticks;
      d[0] /= num_ticks;
      d[1] /= num_ticks;
      d[2] /= num_ticks;
      double tick1start[] = { start[0], start[1], start[2] };
      double tick1stop[] = { start[0], start[1], start[2] };
      double tick2start[] = { start[0], start[1], start[2] };
      double tick2stop[] = { start[0], start[1], start[2] };
      int i = (index == 0 ? 1 : 0);
      tick1start[i] -= angstroms_per_tick_side;
      tick1stop[i] += angstroms_per_tick_side;
      i = (index == 2 ? 1 : 2);
      tick2start[i] -= angstroms_per_tick_side;
      tick2stop[i] += angstroms_per_tick_side;
      double j;
      for(j = 0; j < num_ticks; ++j)
      {
	drawAxisLine(g, is_wireframe, tick1start, tick1stop, -1, 0.0, 0.0);
	drawAxisLine(g, is_wireframe, tick2start, tick2stop, -1, 0.0, 0.0);
	for(i = 0; i < 3; ++i)
	{
	  tick1start[i] += d[i];
	  tick1stop[i] += d[i];
	  tick2start[i] += d[i];
	  tick2stop[i] += d[i];
	}
      }
    }

  }
  public double drawAxes(Graphics g, boolean is_wireframe)
  {
    double minxy[] = { 0, 0, 0 };
    double maxxy[] = {(double)mypanel_size.width,(double)mypanel_size.height,0};
    double minscreen[] = v.screenToXyz(minxy);
    double maxscreen[] = v.screenToXyz(maxxy);
    double max_dimension = Math.max(mypanel_size.height,mypanel_size.width)/v.zoomFactor;
    double log10d = Math.log(max_dimension)/Math.log(10);
    double angstroms_between_ticks = Math.pow(10,Math.floor(log10d) - 1);
    double angstroms_per_tick_side = 2/v.zoomFactor;
    Color save_color = g.getColor();
    g.setColor(Color.blue);
    int i;
    for(i = 0; i < 3; ++i)
    {
      minxy[0] = maxxy[0] = 0;
      minxy[1] = maxxy[1] = 0;
      minxy[2] = maxxy[2] = 0;
      if(minscreen[i] < maxscreen[i])
      {
	minxy[i] = minscreen[i];
	maxxy[i] = maxscreen[i];
      }
      else
      {
	minxy[i] = maxscreen[i];
	maxxy[i] = minscreen[i];
      }
      drawAxisLine(g, is_wireframe, minxy, maxxy, i, angstroms_between_ticks,
		   angstroms_per_tick_side);
    }
    g.setColor(save_color);
    return angstroms_between_ticks;
  }
  public void drawLineToAtom (Graphics g, atom a, double x, double y)
  {
    dl_atom dummy = new dl_atom (a, v);
    dummy.drawLineToAtom (a, x, y, g);
  }
  public void bubblePaint (Panel mypanel)
  {
    int i;
    Vector dlist = new Vector ();
    //setBackgroundBuffer(mypanel);
    dl_atom dla = null;
    for (i = 0; i < atomList.size (); i++)
      {
	dla = new dl_atom ((atom) atomList.elementAt (i), v);
	dlist.addElement (dla);
      }
    if (dla != null)
      dla.quickpaint (dlist, backGC);
  }
  public void boundingboxPaint(Panel mypanel)
  {
    
  }
  public void wireframePaint (Panel mypanel)
  {
    int i, j;
    Vector dlist = new Vector ();
    setBackgroundBuffer(mypanel,false);
    dl_bond dlb = null;
    int max_bonds_shown = 100;
    int step = Math.max(1,bondList.size()/max_bonds_shown);
    for (i = 0; i < bondList.size (); i += step)
    {
      Bond b = (Bond)bondList.elementAt (i);
      atom a1 = b.sourceAtom();
      atom a2 = b.destAtom();
      dlb = new dl_bond (a1, a2, v);
      dlist.addElement (dlb);
    }
    if (dlb != null)
      dlb.quickpaint (dlist, backGC);
  }
  private void setBackgroundBuffer(Panel mypanel,boolean use_ras)
  {
    if (backSize == null || backSize.height != mypanel.size().height
	|| backSize.width != mypanel.size().width)
    {
      if(use_ras)
      {
	ImageProducer prod = ShadeRef.imageProducer(mypanel_size.width, mypanel_size.height);
	ShadeRef.CPKColourAttrib(atomList);
	backBuffer = mypanel.createImage(prod);
      }
      else
      {
	backBuffer = mypanel.createImage(mypanel_size.width, mypanel_size.height);
	backGC = backBuffer.getGraphics();
      }
      backSize = mypanel_size;
    }
    if(!use_ras)
    {
      backGC.setColor(mypanel.getBackground());
      backGC.fillRect(0,0,mypanel_size.width,mypanel_size.height);
    }
  }
  public void fullPaint (Panel mypanel)
  {
    int i, j;
    dl_atom dla = null;
    dl_bond dlb = null;
    Vector dlist = new Vector ();
    setBackgroundBuffer(mypanel,true);
    if (showForces)
      computeForces ();
    for (i = 0; i < atomList.size (); i++)
      {
	dla = new dl_atom ((atom) atomList.elementAt(i), v);
	dlist.addElement (dla);
      }
    if(PDBAtom.atomsize_parm < 360)
      for (i = 0; i < bondList.size (); i++)
      {
	Bond b = (Bond)bondList.elementAt (i);
	atom a1 = b.sourceAtom();
	atom a2 = b.destAtom();
	dlb = new dl_bond (a1, a2, v);
	dlist.addElement (dlb);
	if (showForces)
	  {
	    dlforce dlf = new dlforce (a1.x, a1.f, v);
	    dlf.setForceMultiplier (forceMultiplier);
	    dlist.addElement (dlf);
	  }
      }
    long t1 = System.currentTimeMillis();
    if (dla != null)
	dla.paint (dlist, backGC);
    long t2 = System.currentTimeMillis();
    if (dlb != null)
	dlb.paint (dlist, backGC);
    long tnow = System.currentTimeMillis();
    //System.err.println("group paint time " + (tnow - t1) + " " + (tnow - t2));
  }
  public void paint (Graphics g, Panel mypanel)
  {
    if(backBuffer != null)
      g.drawImage(backBuffer, 0, 0, mypanel);
  }
  private void enumerateTerms ()
  {
    int i, j, k;
    if (!needToEnumerateTerms)
      return;
    needToEnumerateTerms = false;

    for (i = 0; i < atomList.size (); i++)
      ((atom) atomList.elementAt (i)).rehybridize ();

    termList = new Vector ();
    atom a = new carbon ();
    term t;
    t = new lterm (a, a);
    t.enumerate (atomList, termList);
    t = new aterm (a, a, a);
    t.enumerate (atomList, termList);
    t = new tterm (a, a, a, a);
    t.enumerate (atomList, termList);
    t = new lrterm ();
    t.enumerate (atomList, termList);
  }
  public void computeForces ()
  {
    int i;
    enumerateTerms ();
    for (i = 0; i < atomList.size (); i++)
      ((atom) atomList.elementAt (i)).zeroForce ();
    for (i = 0; i < termList.size (); i++)
      ((term) termList.elementAt (i)).computeForces ();
  }
  public void energyMinimizeStep (double stepsize)
  {
    int i;
    computeForces ();
    for (i = 0; i < atomList.size (); i++)
      {
	int j;
	double flensq, m;
	atom a = (atom) atomList.elementAt (i);
	for (j = 0, flensq = 0.0; j < 3; j++)
	  flensq += a.f[j] * a.f[j];
	if (flensq > 0.0)
	  {
	    m = stepsize / Math.sqrt (flensq);
	    for (j = 0; j < 3; j++)
	      a.x[j] += m * a.f[j];
	  }
      }
    centerAtoms ();
  }
}
