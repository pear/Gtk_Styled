<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Scott Mattocks                                    |
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

// Define the error code if it isn't already.
if (!defined('GTK_STYLED_ERROR')) {
    define('GTK_STYLED_ERROR', 1);
}

/**
 * Class for creating a pseudo-scrollbar whose style can be controlled
 * more easily than a regular scrollbar.
 *
 * While it is possible to control some style elements of a GtkScrollBar,
 * other elements cannot be controlled so easily. Items such as the images
 * at the begining and end (usually arrows) and the scroll bar that is 
 * dragged to scroll the element cannot be changed. This leads to 
 * applications that either must conform to the windowing systems look
 * and feel or appear incomplete. The goal of this family of PHP-GTK 
 * classes is to provide all the same functionality as a normal scroll
 * bar but allow the user to have better control over the look and feel.
 *
 * There are several steps to undertake inorder to make this family of
 * classes mimic a GtkScrollBar:
 * Done: Define what needs to be done.
 * Done: Create a pseudo widget that looks like a scroll bar.
 * Done: Make the pseudo widget act like a scroll bar (move up and down)
 * Done: Make the pseudo widget control another widget
 * Done: Make the bar dragable within the track only.
 * Done: Make the pseudo widget "styleable"
 *
 * @author     Scott Mattocks <scottmattocks@php.net>
 * @version    @VER@
 * @category   Gtk
 * @package    StyleObjects
 * @license    PHP version 3.0
 * @copyright  Copyright &copy; 2004 Scott Mattocks
 */
class Gtk_Styled_ScrollBar {
  
    /**
     * The useable widget
     * @var object
     */
    var $widget;
    /**
     * The widget that the scroll bar will control.
     * @var object
     */
    var $widgetToScroll;
    /**
     * The pseudo widget that defines the basic properties of the scroll bar.
     * @var object
     */
    var $styleAdjustment;
    /**
     * The the button that makes the value of the scroll less.
     * @var object
     */
    var $lessButton;
    /**
     * The the button that makes the value of the scroll more.
     * @var object
     */
    var $moreButton;
    /**
     * The time out tag that indicates the user is pressing and holding
     * a part of the scroll bar.
     * @var integer
     */
    var $pressing;
    /**
     * The number of microseconds to wait before incrementing the value
     * again while the user is holding a button.
     * @var integer
     */
    var $delay = 200;
	/**
	 * The width of a button.
	 * @var integer
	 */
	var $width = 15;
	/**
	 * The height of a button.
	 * @var integer
	 */
	var $height = 15;

    /**
     * Constructor. Check parameters an call helper methods.
     * 
     * A Gtk_Styled_ScrollBar requires a Gtk_StyleAdjustment on construction.
     * The style adjustment can be either an H or V style adjustment but
     * Whichever is passed controls the look and behavior of the scroll
     * bar.
     *
     * The widget to be scrolled is added later.
     *
     * @access public
     * @param  object &$styleAdjustment
     * @return void
     */
    function Gtk_Styled_ScrollBar(&$styleAdjustment)
    {
        // Check that a StyleAdjustment was passed.
        if (!is_a($styleAdjustment, 'Gtk_Styled_Adjustment')) {
            $this->_handleError('Gtk_Styled_ScrollBar expects a Gtk_Styled_Adjustment. Given a ' . get_class($styleAdjustment) . '.');
        }
        
        $this->styleAdjustment =& $styleAdjustment;

        // Create the scroll bar look and feel.
        $this->_createScrollBar();
    }

    /**
     * Create the scroll bar parts and put them together.
     *
     * A scroll bar consists of the adjustment and two buttons that
     * control the value of the adjustment. Depending on what type of
     * adjustment is passed, the scroll bar will be either a 
     * horizontal or vertical scoll bar.
     *
     * @access private
     * @param  none
     * @return void
     */
    function _createScrollBar()
    {
        // Create a container to hold the scroll.
        if (is_a($this->styleAdjustment, 'Gtk_Styled_HAdjustment')) {
            $this->widget =& new GtkHBox;
        } else {
            $this->widget =& new GtkVBox;
        }

        // Create the controls.
        $this->lessButton =& new GtkButton(NULL);
        $this->moreButton =& new GtkButton(NULL);

        $this->lessButton->set_usize($this->width, $this->height);
        $this->moreButton->set_usize($this->width, $this->height);
        
        // Add the default images to the buttons.
        require 'data/Gtk_Styled/Buttons.php';
        if (is_a($this->styleAdjustment, 'Gtk_Styled_HAdjustment')) {
            $this->setButtonContents($this->lessButton, $this->_createPixmap($buttons['horizontal_less']));
            $this->setButtonContents($this->moreButton, $this->_createPixmap($buttons['horizontal_more']));
        } else {
            $this->setButtonContents($this->lessButton, $this->_createPixmap($buttons['vertical_less']));
            $this->setButtonContents($this->moreButton, $this->_createPixmap($buttons['vertical_more']));
        }

        // Add everything to the container.
        $this->widget->pack_start($this->lessButton, false, false, 0);
        $this->widget->pack_start($this->styleAdjustment->getWidget(), true, true, 0);
        $this->widget->pack_start($this->moreButton, false, false, 0);
    }

    /**
     * Create a pixmap from an array.
     *
     * Turn an array into a pixmap. This method is used to turn the arrays
     * in the buttons data file into images. The images are then used for
     * the scroll buttons. 
     *
     * @access private
     * @param  array   &$imgArray The image array.
     * @return &array             The new GtkPixmap.
     */    
    function &_createPixmap(&$imgArray)
    {
        $tmpWindow =& new GtkWindow;
        $tmpWindow->realize();
        $transparentColor = new GdkColor('#FFFFFF');
        $pieces =& gdk::pixmap_create_from_xpm_d($tmpWindow->window, $transparentColor, $imgArray);
        $pxm =& new GtkPixmap($pieces[0], $pieces[1]);
        unset($tmpWindow);

        return $pxm;
    }

    /**
     * Set the pixmap image of a button.
     *
     * The method allows the user to set the contents of the button.
     * Each button should have an image visually indicating what
     * affect it will have on the value of the adjustment. By default
     * these image are triangles pointing in the direction that they
     * will move the scroll bar. The user may supply any image they
     * desire and may also change the shape of the button using the
     * setButtonMask() method.
     *
     * @access public
     * @param  object &$button The button to set the image for.
     * @param  object &$widget The new contents of the button.
     * @return widget          The previous widget in the button.
     */    
    function setButtonContents(&$button, &$widget)
    {
        $prevChild = $button->child;
        $button->remove($prevChild);
        $button->add($widget);

        return $prevChild;
    }

    /**
     * Set the style of one of the buttons.
     *
     * The whole purpose of this class is to allow you to set the
     * style of a the widget. The track and bar styles are set in
     * the Adjustment. The button styles can be set here.
     *
     * @access public
     * @param  object &$button
     * @param  object &$style
     * @return void
     */
    function setButtonStyle(&$button, &$style)
    {
        $button->set_style($style);
    }

    /**
     * Set the pix mask of one of the buttons.
     *
     * The user may control the shape of the scrollbar buttons by
     * adding a pix mask. By default the buttons are grey squares
     * but by using this method and setButtonContents it is possible
     * to make the button any shape and/or color desired.
     *
     * The button that passed to this method should be the return
     * value from one of getLessButton() or getMoreButton().The x
     * and y parameters will offset the mask from the upper left
     * corner.
     *
     * @access public
     * @param  object  &$button The button whose shape is to be changed.
     * @param  object  &$mask   The mask to apply to the button.
     * @param  integer $x       The offset from the left edge.
     * @param  integer $y       The offset from the top edge.
     */
    function setButtonMask(&$button, &$mask, $x = 0, $y = 0)
    {
        $button->shape_combine_mask($mask, $x, $y);
    }
 
    /**
     * Return the 'more' button.
     *
     * @access public
     * @param  none
     * @return &object
     */
    function &getMoreButton()
    {
        return $this->moreButton;
    }

    /**
     * Return the 'less' button.
     *
     * @access public
     * @param  none
     * @return &object
     */
    function &getLessButton()
    {
        return $this->lessButton;
    }

    /**
     * Add a widget to be controlled by the Gtk_Styled_ScrollBar.
     *
     * It doesn't make much sense to have a scroll bar with nothing to
     * scroll. This method puts the Gtk_Styled_ScrollBar incharge of the
     * $widgetToScroll. The widget to scroll should obviously be 
     * scrollable. The Gtk_Styled_ScrollBar will only control scrolling in
     * one direction. Which direction depends on what type of 
     * Gtk_StyleAdjustment was passed on construction. To fully control
     * all directions of scrolling, you must add the widget to scroll 
     * to two different Gtk_Styled_ScrollBars.
     *
     * @access public
     * @param  object  &$widgetToScroll
     * @return void
     */
    function addWidgetToScroll(&$widgetToScroll)
    {
        // Set the member variable.
        $this->widgetToScroll = $widgetToScroll;

        // Set the values for the adjustment based off of the
        // widget to scroll.
        $this->setAdjFromAdj($this->widgetToScroll);
        
        // Connect the style adjustment pieces to the right signals.
        $this->_connectStyleAdjustment();

        // We need to keep the styleAdjustment values current with
        // the "real" adjustment values. The values can be changed
        // by the scrollable widget or other objects. It is important
        // to keep things consistent and up-to-date.
        $this->widgetToScroll->connect('value-changed', array(&$this, 'setAdjValueFromAdj'));
        $this->widgetToScroll->connect('changed', array(&$this, 'setAdjFromAdj'));
    }

    /**
     * Connect the styleAdjustment pieces to value setting methods.
     *
     * To have control over the scrollable widget, the user must be
     * able to change the value of the styleAdjustment. The track
     * and the buttons must be clickable.
     * 
     * Eventually, the bar must also be dragable.
     *
     * @access private
     * @param  none
     * @return void
     */
    function _connectStyleAdjustment()
    {
        // Connect the pre and post tracks to change the value by one page.
        $this->styleAdjustment->preTrack->connect_object('button-press-event',
                                                         array(&$this, 'setAdjValue'),
                                                         (0 - $this->styleAdjustment->pageSize), true);
        $this->styleAdjustment->preTrack->connect_object('button-release-event',
                                                         array(&$this, 'stopValue'));
        $this->styleAdjustment->preTrack->connect_object('leave-notify-event',
                                                         array(&$this, 'stopValue'));

        $this->styleAdjustment->bar->connect_object('pressed', 
                                                    array(&$this, 'setAdjValueMouse'));
        $this->styleAdjustment->bar->connect_object('released',
                                                    array(&$this, 'stopValue'));

        $this->styleAdjustment->postTrack->connect_object('button-press-event',
                                                          array(&$this, 'setAdjValue'),
                                                          $this->styleAdjustment->pageSize, true);
        $this->styleAdjustment->postTrack->connect_object('button-release-event',
                                                          array(&$this, 'stopValue'));
        $this->styleAdjustment->postTrack->connect_object('leave-notify-event',
                                                          array(&$this, 'stopValue'));

        // Make the controls have an effect on the adjustment value.
        $this->lessButton->connect_object('pressed', 
                                          array(&$this, 'setAdjValue'),
                                          (0 - $this->styleAdjustment->stepIncrement), true);
        $this->lessButton->connect_object('released', 
                                          array(&$this, 'stopValue'));

        $this->moreButton->connect_object('pressed',
                                          array(&$this, 'setAdjValue'),
                                          $this->styleAdjustment->stepIncrement, true);
        $this->moreButton->connect_object('released', 
                                          array(&$this, 'stopValue'));
    }

    /**
     * Set the adjustment value based on the mouse position.
     *
     * When the scrollbar is being dragged to change its value 
     * the change in mouse position should influence an equal 
     * change in bar position. In other words one pixel change
     * in mouse position does not necessarily translate to one
     * pixel change in bar position.
     *
     * @access public
     * @param  none
     * @return void
     */
    function setAdjValueMouse()
    {
        // Figure out which coordinate to use.
        if (is_a($this->styleAdjustment, 'Gtk_Styled_HAdjustment')) {
            $coord = 0;
        } else {
            $coord = 1;
        }

        // Get the current mouse position.
        $newPos = $this->styleAdjustment->widget->window->pointer[$coord];

        // Make sure the mouse is still within the bar.
        if(!$this->styleAdjustment->checkMouseBounds()) {
            return true;
        }


        // Set the adjustment value based on the difference between the
        // old and new mouse position.
        if (isset($this->mPosition)) {
            if (isset($this->widgetToScroll)) {
                $this->widgetToScroll->set_value($this->styleAdjustment->setValue($this->styleAdjustment->getValueFromPosition($newPos - $this->mPosition)));
            } else {
                $this->styleAdjustment->setValue($this->styleAdjustment->getValueFromPosition($newPos - $this->mPosition));
            }
            //$this->setAdjValue($this->styleAdjustment->value + ($newPos - $this->mPosition), false);
        }

        // Make the current position the old position for next time.
        $this->mPosition = $newPos;

        // Keep calling the method to update the value.
        $this->delay = 50;
        $this->pressing = gtk::timeout_add($this->delay, array(&$this, 'setAdjValueMouse'));
    }

    /**
     * Set the value of the adjustment and styleAdjustment.
     *
     * Sets the value of the "real" adjustment and the style adjustment.
     * By setting the value of the adjustment, the scrolling widget will
     * move as if it has been scrolled. This makes it easier to fake the
     * scrolling action as if it were done by the style adjustment.
     *
     * @access public
     * @param  object $event The GdkEvent that occured to make the value change
     * @param  double $value The new value for the adjustments.
     * @return void
     */
    function setAdjValue($event, $value, $continue = false)
    {
        // Adjust for the fact that some events pass the event also.
        if (is_numeric($event)) {
            $continue = $value;
            $value    = $event;
        }

        // Set the value of the widget to scroll also.
        // This is a cheat for making the scrollable widget scroll.
        if (isset($this->widgetToScroll)) {
            // Because the widget to scroll is connected to other methods,
            // it will change the value of the style adjustment when its
            // value is changed.
            $this->widgetToScroll->set_value($this->styleAdjustment->setValue($this->styleAdjustment->value + $value));
        } else {
            $this->styleAdjustment->setValue($this->styleAdjustment->value + $value);
        }

        // Keep going if the user is holding down the button.
        if ($continue) {
            $this->pressing = gtk::timeout_add($this->delay, array(&$this, 'setAdjValue'), $value, $continue);
            // Reduce the delay.
            $this->delay = 100;
        }
    }

    /**
     * Stop incrementing the value.
     *
     * This method kills the timeout that is calling the setAdjValue 
     * method. This will stop the scrollbar from moving after the 
     * user has released the button or moved the mouse outside of the
     * track.
     * 
     * @access public
     * @param  none
     * @return void
     */
    function stopValue()
    {
        // Kill the timeout.
        if (isset($this->pressing)) {
            gtk::timeout_remove($this->pressing);
        }
        // The user must hold down the button for a little while
        // before the scrolling starts.
        $this->delay = 200;

        if (isset($this->mPosition)) {
            unset($this->mPosition);
        }
    }

    /**
     * Set the styleAdjustment value based on the value of a 
     * GtkAdjustment object.
     *
     * In order to keep the styleAdjustment in sync with the scrolling
     * widget, the value must be taken from the GtkAdjustment that 
     * controls the GtkScrollbar.
     *
     * @access public
     * @param  object &$adj The GtkAdjustment to get the value from.
     * @return void
     */
    function setAdjValueFromAdj(&$adj)
    {
        $this->styleAdjustment->setValue($adj->value);
    }

    /**
     * Set the styleAdjustment values based off of a GtkAdjustment.
     *
     * @access public
     * @param  object &$adj The GtkAdjustment to get the values from.
     * @return void
     */
    function setAdjFromAdj(&$adj)
    {
        $this->styleAdjustment->setUpper($adj->upper);
        $this->styleAdjustment->setLower($adj->lower);
        $this->styleAdjustment->setPageSize($adj->page_size);
        $this->styleAdjustment->setPageIncrement($adj->page_increment);
        $this->styleAdjustment->setStepIncrement($adj->step_increment);        
        $this->styleAdjustment->setValue($adj->value);
    }

    /**
     * Get the final product that the user can use.
     *
     * To make things easier for the user, and to keep some 
     * consistency with other PEAR Gtk classes, the final usable
     * object is called widget and is returned from the getWidget
     * method. 
     * 
     * @access public
     * @param  none
     * @return &object
     */
    function &getWidget()
    {
        return $this->widget;
    }

    /**
     * Error handling method.
     *
     * Errors should be handled with PEAR::Error_Stack
     *
     * @access private
     * @param  string  $message
     * @param  integer $level
     * @return mixed
     */
    function _handleError($msg, $code = GTK_STYLED_ERROR, $pearMode = PEAR_ERROR_PRINT)
    {
        // Require the pear class so that we can use its error functionality.
        require_once ('PEAR.php');
        
        // Check whether or not we should print the error.
        PEAR::raiseError($msg . "\n", $code, $pearMode);
    }
}
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>