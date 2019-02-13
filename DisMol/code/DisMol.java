/**
 * DisMol.java
 * Copyright (c) 1998 Peter McCluskey, all rights reserved.
 * Based on code from Will Ware's NanoCAD and Roger Sayle's RasMol.
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
 * any author be liable for any direct, indirect, incidental, special,
 * exemplary, or consequential damages (including, but not limited to,
 * procurement of substitute goods or services; loss of use, data, or
 * profits; or business interruption) however caused and on any theory of
 * liability, whether in contract, strict liability, or tort (including
 * negligence or otherwise) arising in any way out of the use of this
 * software, even if advised of the possibility of such damage.
 */

import java.applet.*;
import java.awt.*;
import java.lang.Math;
import java.util.StringTokenizer;
import atom;
import view;
import group;
import java.io.*;
import java.net.URL;
import java.net.URLConnection;
import java.net.MalformedURLException;
import pdbreader;
import xyzreader;
import ShadeRef;

public class DisMol extends Applet
{
  public static final String rcsid =
  "$Id: DisMol.java,v 1.14 2000/04/15 23:32:12 pcm Exp $";
  private Button getMoleculeFile;
  private Button resizeAtoms;
  private Choice whichElement;
  private group grp;
  private Panel drawingArea;
  private Choice fileList;
  private TextArea inputWindow2;
  private int xxx, yyy;
  private atom atom1;
  private double atom1z;
  private boolean inDrawingArea, needToRepaint = true;
  private Label atomInfoBlab;
  private Panel controls;
  private TextArea instrucs;
  private boolean resized_axes = false;
  private Checkbox showAxes;
  private String fileUrl;
  private String sizeOfAtoms;

  public DisMol()
  {
    ShadeRef.InitialiseTransform();
  }
  public void paint (Graphics g)
  {
    if(grp == null) return;
    int ht = drawingArea.size().height;
    if(ht == 0)
      ht = size().height - controls.size().height;
    grp.setPanelSize(new Dimension(drawingArea.size().width,ht));
    grp.updateViewSize ();
    long t1 = System.currentTimeMillis();
    if(dragFlag)
      grp.wireframePaint (drawingArea);
    else grp.fullPaint(drawingArea);
    if(false)
    {
      long tnow = System.currentTimeMillis();
      System.err.println("paint time " + (tnow - t1) + " " + (tnow - ShadeRef.t1));
    }
    if(showAxes.getState())
    {
      double angstroms_between_ticks = grp.drawAxes(g, dragFlag);
      if(resized_axes)
      {
	atomInfoBlab.setBackground (this.getBackground ());
	atomInfoBlab.setText ("axis ticks set " + angstroms_between_ticks + " angstroms apart");
	resized_axes = false;
      }
    }
    grp.paint(g,drawingArea);
    //    if(this.getCursor() != Cursor.getDefaultCursor())
    //this.setCursor(Cursor.getDefaultCursor());
    setPainted();
  }
    public void update(Graphics g) {
      /*	if (backBuffer == null)
	    g.clearRect(0, 0, size().width, size().height);
      */
      paint(g);
    }
    private synchronized void setPainted() {
	needToRepaint = false;
	notifyAll();
    }
  private int x0, y0;  // xxx,yyy tracks the mouse, x0,y0 stands still
  private boolean dragFlag = false;
  private int mouseModifiers;
  public void atomInfo ()
  {
    atomInfoBlab.setBackground (this.getBackground ());
    atomInfoBlab.setText ("");
  }
  public void atomInfo (String s)
  {
    atomInfoBlab.setBackground (this.getBackground ());
    atomInfoBlab.setText (s);
  }
  public void atomInfo (atom a)
  {
    if (a == null)
      {
	atomInfoBlab.setBackground (this.getBackground ());
	atomInfoBlab.setText ("");
	return;
      }
    String hinfo = "", atom_idno = "";
    PDBAtom p = (PDBAtom)a;
    if(p != null)
    {
      atom_idno = "#" + p.serno + " ";
    }
    switch (a.hybridization)
      {
      case atom.SP3: hinfo = "sp3"; break;
      case atom.SP2: hinfo = "sp2"; break;
      case atom.SP:  hinfo = "sp"; break;
      }
    atomInfoBlab.setBackground (this.getBackground ());
    atomInfoBlab.setText (atom_idno + a.name() + " " +
			  a.symbol() + " " + hinfo + " coords: " + a.x[0]
			  + "," + a.x[1] + "," + a.x[2]);
  }
  public boolean mouseDown (Event e, int x, int y)
  {
    Rectangle r = drawingArea.bounds();
    inDrawingArea = y < r.height;
    needToRepaint = false;
    double[] scrPos = { x, y, 0 };
    atom1 = grp.selectedAtom (scrPos, true);
    dragFlag = false;

    // We only care about the SHIFT and CTRL modifiers, mask out all others
    mouseModifiers = e.modifiers & (Event.SHIFT_MASK | Event.CTRL_MASK);
    if (atom1 != null)
      {
	double[] atomScrPos = grp.v.xyzToScreen (atom1.x);
	atom1z = atomScrPos[2];
      }
    else
      {
	atom1z = 0;
      }
    atomInfo (atom1);
    xxx = x; yyy = y;
    x0 = x; y0 = y;
    return true;
  }
  public boolean mouseDrag (Event e, int x, int y)
  {
    boolean movingAtom = false;  // if moving atom, no need for extra line
    if (!dragFlag)
      if (x < x0 - 2 || x > x0 + 2 || y < y0 - 2 || y > y0 + 2)
	dragFlag = true;
    if (dragFlag)
      {
	needToRepaint = true;
	if (atom1 == null)
	  {
	    switch (mouseModifiers)
	      {
	      default:
		grp.v.rotate (0.01 * (x - xxx), 0.01 * (y - yyy));
		break;
	      case Event.SHIFT_MASK:
		// grp.forceMultiplier *= Math.exp (0.01 * (x - xxx));
		grp.v.pan (x - xxx, y - yyy);
		break;
	      case Event.CTRL_MASK:
		grp.v.zoomFactor *= Math.exp (0.01 * (x - xxx));
		grp.v.perspDist *= Math.exp (0.01 * (y - yyy));
		//System.err.println("zoom " + grp.v.zoomFactor + " " + grp.v.perspDist);
		if(grp.v.zoomFactor > 100)
		  grp.v.zoomFactor = 100;
		if(grp.v.perspDist < 400)
		  grp.v.perspDist = 400;
		resized_axes = true;
		break;
	      }
	    repaint();
	    /*
	    grp.wireframePaint (drawingArea);
	    */
	  }
	else
	  {
	    switch (mouseModifiers)
	      {
	      default:
		double[] scrPos = { x, y, atom1z };
		atom1.x = grp.v.screenToXyz (scrPos);
		movingAtom = true;
		grp.wireframePaint (drawingArea);
		break;
	      case Event.SHIFT_MASK:
		grp.bubblePaint (drawingArea);
		break;
	      case Event.CTRL_MASK:
		grp.bubblePaint (drawingArea);
		break;
	      }
	  }
	xxx = x; yyy = y;
	if (atom1 != null && !movingAtom)
	  grp.drawLineToAtom (drawingArea.getGraphics(), atom1, x, y);
      }
    return true;
  }
  public boolean mouseUp (Event e, int x, int y)
  {
    if (atom1 != null)
      {
	double[] scrPos = { x, y, atom1z };
	atom atom2 = grp.selectedAtom (scrPos, false);
	if (dragFlag)
	  {
	    // we dragged on an atom
	    switch (mouseModifiers)
	      {
	      default:
		atom1.x = grp.v.screenToXyz (scrPos);
		atom2 = atom1;
		break;
	      case Event.SHIFT_MASK:
		if (atom1 != atom2 && atom1 != null && atom2 != null)
		  {
		    // create a new bond if none exists, or increment the
		    // order if it does exist.
		    grp.addBond (atom1, atom2);
		  }
		break;
	      case Event.CTRL_MASK:
		if (atom1 != atom2 && atom1 != null && atom2 != null)
		  grp.deleteBond (atom1, atom2);
		break;
	      }
	    // give information about the last atom we visited
	    atomInfo (atom2);
	  }
      }
    else if (!dragFlag)
      {
	// we clicked on air
	switch (mouseModifiers)
	  {
	  default:
	    break;
	  case Event.CTRL_MASK:
	    needToRepaint = true;
	    atomInfo ();
	    //grp.updateViewSize (drawingArea,drawingArea.size().height);
	    grp.centerAtoms ();
	    break;
	  }
      }
    else
    {
      needToRepaint = true;
      dragFlag = false;
    }
    if (needToRepaint)
    {
      //this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
      repaint ();
    }
    return true;
  }
  public boolean action (Event e, Object arg)
  {
    String s;
    if (e.target == getMoleculeFile || e.target == fileList)
      {
	fileUrl = fileList.getSelectedItem();
	System.err.println(fileUrl);
	if((grp = readMoleculeURL(drawingArea)) != null)
	{
	  grp.setDefaultZoomFactor();
	  needToRepaint = true;
	  repaint();
	}
	return true;
      }
    else if(e.target == resizeAtoms)
    {
      sizeOfAtoms = inputWindow2.getText();
      double p = Double.valueOf(sizeOfAtoms).doubleValue();
      if(p < 0 || p > 750)
      {
	atomInfoBlab.setBackground (Color.orange);
	atomInfoBlab.setText ("Warning - atom size parameter should probably be between 0 and 750");
      }
      grp.resizeAtoms(p);
      needToRepaint = true;
      repaint();
    }
    else if (e.target == showAxes)
      {
	needToRepaint = true;
	resized_axes = true;
	repaint();
	return true;
      }
    /*
    else if (e.target == clearStruct)
      {
	grp = new group ();
	grp.setShowForces (showForces.getState());
	clearScreen ();
	return true;
      }
    else if (e.target == showForces)
      {
	grp.setShowForces (showForces.getState());
	needToRepaint = true;
	repaint();
	return true;
      }
    */
    return false;
  }
  private void constrain (Container container, Component component,
			  int gridX, int gridY, int gridW, int gridH)
  {
    constrain (container, component, gridX, gridY, gridW, gridH,
	       GridBagConstraints.BOTH, GridBagConstraints.NORTHWEST,
	       0.5, 0.5, 0, 0, 0, 0);
  }
  private void constrain (Container container, Component component,
			  int gridX, int gridY, int gridW, int gridH,
			  int fill, int anchor, double weightX,
			  double weightY, int top, int left, int bottom,
			  int right)
  {
    GridBagConstraints c = new GridBagConstraints ();
    c.gridx = gridX; c.gridy = gridY;
    c.gridwidth = gridW; c.gridheight = gridH;
    c.fill = fill; c.anchor = anchor;
    c.weightx = weightX; c.weighty = weightY;
    if (top + bottom + left + right > 0)
      c.insets = new Insets (top, left, bottom, right);
    ((GridBagLayout) container.getLayout()).setConstraints(component, c);
  }
  private group readMoleculeURL(Panel not_used)
  {
    URL url;
    try {
      url = new URL(fileUrl);
    } catch (MalformedURLException e) {
      System.err.println("MalformedURLException");
      return null;
    }
    //this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
    InputStream instream;
    URLConnection connect;
    try {
	connect = url.openConnection();
	instream = connect.getInputStream();
    } catch (IOException e) {
        System.err.println("couldn't connect to url");
	return null;
    }
    atomInfoBlab.setBackground (this.getBackground ());
    String file_type = connect.getContentType();
    if(!file_type.equals("chemical/x-pdb") && !file_type.equals("chemical/x-xyz"))
    {
      if(fileUrl.endsWith(".pdb") || fileUrl.endsWith(".ent"))
	file_type = "chemical/x-pdb";
      else if(fileUrl.endsWith(".xyz"))
	file_type = "chemical/x-xyz";
      else
      {
	atomInfoBlab.setText ("I don't know what to do with encoding type " + file_type);
	return null;
      }
    }
    atomInfoBlab.setText ("reading " + connect.getContentLength()
			  + " bytes, type " + file_type);
    group r;
    if(file_type.equals("chemical/x-xyz"))
      r = xyzreader.read(instream);
    else r = pdbreader.read(instream);
    if(r == null)
      atomInfoBlab.setText ("Unable to read file");
    else atomInfoBlab.setText ("Transfer complete");
    return r;
  }
  public void init ()
  {
    controls = new Panel ();
    drawingArea = new Panel ();

    setLayout(new BorderLayout());
    drawingArea.setLayout(new BorderLayout());
    drawingArea.setBackground(Color.black);

    GridBagLayout gridbag = new GridBagLayout ();
    controls.setLayout (gridbag);

    String spaces = "                              "; /* 30 spaces */
    atomInfoBlab = new Label (spaces + spaces + spaces + spaces + spaces);
    constrain (controls, atomInfoBlab, 0, 1, 5, 1);
    controls.add(atomInfoBlab);
    this.add ("North",drawingArea);
    fileList = new Choice ();
    String urls = getParameter("url");
    StringTokenizer toker = new StringTokenizer(urls," ;\n\r");
    while(toker.hasMoreTokens())
    {
      String u = toker.nextToken();
      if(fileUrl == null)
	fileUrl = u;
      fileList.addItem(u);
    }

    grp = readMoleculeURL(drawingArea);
    if(grp != null)
    {
      String astr = getParameter("atomsize");
      if(astr != null)
      {
	double atom_size = Double.valueOf(astr).doubleValue();
	if(atom_size > 0.01)
	  grp.resizeAtoms(atom_size);
      }
    }

    constrain (controls, fileList, 0, 0, 1, 1);
    controls.add (fileList);

    showAxes = new Checkbox ("Show axes");
    showAxes.setState (false);
    constrain (controls, showAxes, 2, 0, 1, 1);
    controls.add (showAxes);

    sizeOfAtoms = PDBAtom.atomsize_parm + "";
    inputWindow2 = new TextArea (sizeOfAtoms, 1, 4);
    inputWindow2.setEditable (true);
    constrain (controls, inputWindow2, 3, 0, 1, 1);
    controls.add (inputWindow2);

    resizeAtoms = new Button ("atom size");
    constrain (controls, resizeAtoms, 4, 0, 1, 1);
    controls.add (resizeAtoms);

    /*
    getMoleculeFile = new Button ("Get new file");
    constrain (controls, getMoleculeFile, 1, 0, 1, 1);
    controls.add (getMoleculeFile);
    */

    instrucs = new TextArea (5, 80);
    constrain (controls, instrucs, 0, 2, 5, 1);
    instrucs.setText (
"Mouse operations (S=shift, C=control) / pan: S-drag air\n"+
"rotate: drag air / zoom: C-drag air horiz / move atom: drag atom\n"+
"atom info: click atom / recenter: C-click air / perspective: C-drag air vert\n"+
"Use atom size of 0 to 750 to control the radii of each atom.\n"+
"Use \"Show axes\" button to control whether axes are displayed\n"+
"\n"+
"==========================================================================\n"+
"DisMol 0.13  April 2000\n"+
"Copyright (c) 2000 Peter McCluskey, all rights reserved.\n"+
"based on code from Will Ware's NanoCAD and Roger Sayle's RasMol.\n"+
"\n"+
"Redistribution and use in source and binary forms, with or without\n"+
"modification, are permitted provided that the following conditions\n"+
"are met:\n"+
"1. Redistributions of source code must retain the above copyright\n"+
"   notice, this list of conditions and the following disclaimer.\n"+
"2. Redistributions in binary form must reproduce the above copyright\n"+
"   notice, this list of conditions and the following disclaimer in the\n"+
"   documentation and other materials provided with the distribution.\n"+
"3. All advertising materials mentioning features or use of this software\n"+
"   or its derived works must display the following acknowledgement:\n"+
"	This product includes software developed by Will Ware.\n"+
"\n"+
"This software is provided \"as is\" and any express or implied warranties,\n"+
"including, but not limited to, the implied warranties of merchantability\n"+
"or fitness for any particular purpose are disclaimed. In no event shall\n"+
"the authors be liable for any direct, indirect, incidental, special,\n"+
"exemplary, or consequential damages (including, but not limited to,\n"+
"procurement of substitute goods or services; loss of use, data, or\n"+
"profits; or business interruption) however caused and on any theory of\n"+
"liability, whether in contract, strict liability, or tort (including\n"+
"negligence or otherwise) arising in any way out of the use of this\n"+
"software, even if advised of the possibility of such damage.\n"
);
    instrucs.setEditable (false);
    controls.add(instrucs);

    this.add ("South",controls);

    if(grp != null)
    {
      int ht = drawingArea.size().height;
      int wd = drawingArea.size().width;
      if(ht == 0)
      {
	ht = size().height - controls.size().height;
	wd = size().width - controls.size().width;
      }
      grp.setPanelSize(new Dimension(wd,ht));
      grp.setDefaultZoomFactor();
    }
    this.repaint();
    this.show();
  }
}
