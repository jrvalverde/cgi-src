<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html" 
    charset="UTF-8">
  <title>Testing PEAR HTML_QuickForm</title>
</head>
<body>
<?
    /**
     * HTML_QuickForm class use
     *
     * Is mandatory to instantiate an HTML_QuickForm object. This defines the 
     * name of the form (frmTest) and the method used to pass the form variables 
     * back to the PHP script. By default, the form's action -- the URI to 
     * which to submit the form data -- is the same URI that displayed the form 
     * in the first place. This may seem unusual at first, but it is actually 
     * very useful, as we shall see.
     *
     * The next four lines add four elements to the form. Elements are either 
     * orthodox HTML form elements, such as text boxes or radio buttons, or 
     * PEAR-specific pseudo-elements. The first parameter to the addElement 
     * method is mandatory; it defines the element type. Here, header is a 
     * pseudo-element that just places a heading bar on the form. The second 
     * parameter of each element is a name by which code can refer to it, if 
     * necessary. In the case of the header, the name is hdrQuickformtest. 
     * The third parameter is the text label associated with each element. 
     * For the header, this is the text to display. For the text element, a 
     * text box, it is the prompt before the text box. For the two buttons, 
     * it is the text on the button face.
     *
     * Finally, the line $form->display(); renders the form to the screen.
     * When displayed, this page shows a header, a text box and two buttons: 
     * so far, so good. However, clicking the submit button appears to do 
     * nothing. The next step is to add the code to process the form after 
     * submission.
     * @package pagepackage
     */
    require_once "HTML/QuickForm.php";

    $form = new HTML_QuickForm('frmTest', 'get');

    $form->addElement('header', 'hdrQuickformtest',
                    'QuickForm Example 1');
    $form->addElement('text',   'txtName',
                    'What is your name?');
    $form->addElement('reset',  'btnClear',
                    'Clear');
    $form->addElement('submit', 'btnSubmit',
                    'Submit');

    $form->display();
?>
</body>
</html>
