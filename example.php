<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Scott Mattocks                                    |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Scott Mattocks <scottmattocks@php.net>                       |
// +----------------------------------------------------------------------+
//
// $Id$
/**
 * Example script to build and show Gtk_Styled components.
 *
 * Using PEAR::Gtk_Styled components, instead of their normal PHP-GTK 
 * counter parts, allows for more control over the look and feel of an
 * application. This example script shows how two Gtk_Styled_ScrollBars
 * can be used to achieve the same result as two GtkScrollBars but with
 * the ability to control exactly how the scroll bars look. In this 
 * script, both the "natural" and Gtk_Styled scrollbars are added to the
 * main window so that the user can see exactly how the Gtk_Styled
 * version reacts to different changes in the application as compared to
 * the normal scroll bars. 
 *
 * @author     Scott Mattocks <scottmattocks@php.net>
 * @version    @VER@
 * @category   Gtk
 * @package    Gtk_Styled
 * @subpackage Documentation
 * @license    PHP version 3.0
 * @copyright  Copyright &copy; 2005 Scott Mattocks
 */

// Load the extension.
if (!extension_loaded('gtk')) {
    dl( 'php_gtk.' . PHP_SHLIB_SUFFIX);
}

// Create a window
$window =& new GtkWindow;
$window->realize();

// Create the vertical styled adjustment.
require_once 'Gtk/Styled/VAdjustment.php';
$vStyleAdj =& new Gtk_Styled_VAdjustment(0, 0, 50, 1, 20, 10);

// Set the bar style...
$gtkStyle = new GtkStyle;
$gtkStyle->bg[GTK_STATE_NORMAL]   = new GdkColor('#0000CC');
$gtkStyle->bg[GTK_STATE_PRELIGHT] = new GdkColor('#00CC00');
$vStyleAdj->setStyle('bar', $gtkStyle);

// Set the post track style...
$gtkStyle2 = new GtkStyle;
$gtkStyle2->bg[GTK_STATE_NORMAL]   = new GdkColor('#CC00CC');
$vStyleAdj->setStyle('postTrack', $gtkStyle2);

// Create an horizontal adjustment.
require_once 'Gtk/Styled/HAdjustment.php';
$hStyleAdj =& new Gtk_Styled_HAdjustment(0, 0, 60, 5, 15, 50);

// Turn the adjustments into scroll bars.
require_once 'Gtk/Styled/ScrollBar.php';
$hStyleScroll =& new Gtk_Styled_ScrollBar($hStyleAdj);
$vStyleScroll =& new Gtk_Styled_ScrollBar($vStyleAdj);

// Give the horizontal scrollbar's more button a different shape...
$plus = array(
                 "15 13 3 1",
                 "   c none", "@  c #0000CC", ".  c #6666FF",
                 "               ",
                 "     .....     ",
                 "   .........   ",
                 " .....@@@..... ",
                 "......@@@......",
                 "......@@@......",
                 "..@@@@@@@@@@@..",
                 "..@@@@@@@@@@@..",
                 "......@@@......",
                 "......@@@......",
                 " .....@@@..... ",
                 "   .........   ",
                 "     .....     "
                 );
$transparentTest = new GdkColor('#ABCDEF');
$plusTest =& gdk::pixmap_create_from_xpm_d($window->window, $transparentTest, $plus);
$pxm =& new GtkPixmap($plusTest[0], $plusTest[1]);

// Set the more button's mask and image.
$hStyleScroll->setButtonContents($hStyleScroll->getMoreButton(), $pxm);
$hStyleScroll->setButtonMask($hStyleScroll->getMoreButton(), $plusTest[1], 0, 1);

// Create a box to put everything in.
$Hbox =& new GtkHBox();
$Vbox =& new GtkVBox();

// Create a cList with enough data to scroll. (Example from PHP-GTK docs)
$list =& new GtkCList(2, array('City', 'Inhabitants (M)'));
$data = array(array('Paris', 9),
              array('Moscow', 9),
              array('London', 6),
              array('Rome', 3),
              array('Berlin', 4),
              array('Athens', 3)
              );
              
foreach ($data as $key => $val) {
    $list->insert(0, $val);
}

$list->set_column_width(0, 90);

// Put the CList in a ScrolledWindow.
$scWin =& new GtkScrolledWindow();
// Make the "natural" scrollbars show for comparison.
$scWin->set_policy(GTK_POLICY_ALWAYS, GTK_POLICY_ALWAYS);

// Start packing away.
$scWin->add($list);
$Hbox->pack_start($scWin);
$Hbox->pack_start($vStyleScroll->getWidget(), false, false, 0);
$Vbox->pack_start($Hbox);

$Hbox2 =& new GtkHBox();
$Vbox2 =& new GtkVBox();
$Vbox2->pack_start($hStyleScroll->getWidget(), false, false, 0);

$spacerBox =& new GtkVBox();
$spacerBox->set_usize(30, 15);
$Hbox2->pack_start($Vbox2);
$Hbox2->pack_start($spacerBox, false, false, 0);
$Vbox->pack_start($Hbox2, true, true, 0);

// Add everything to the window and start the main loop.
$window->add($Vbox);
$window->show_all();
$window->connect_object('destroy', array('gtk', 'main_quit'));

// After the main window has been realized, set the style scroll
// to control the "real" scrollbar.
// If this is done before realization, you will get all sorts of
// warnings and errors because some needed properties aren't
// available yet.
$vStyleScroll->addWidgetToScroll($scWin->get_vadjustment());
$hStyleScroll->addWidgetToScroll($scWin->get_hadjustment());

gtk::main();
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>