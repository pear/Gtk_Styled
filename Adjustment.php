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
 * A styleable adjustment pseudo widget.
 *
 * The interface for this should mimic a GtkAdjustment.
 *
 * @abstract
 * @author     Scott Mattocks <scottmattocks@php.net>
 * @version    @VER@
 * @category   Gtk
 * @package    Styled
 * @subpackage Adjustment
 * @license    PHP version 3.0
 * @copyright  Copyright &copy; 2004 Scott Mattocks
 */
class Gtk_Styled_Adjustment {

    /**
     * The useable widget
     * @var object
     */
    var $widget;
    /**
     * The track that the bar sits in.
     * @var object
     */
    var $track;
    /**
     * The portion of the track before the bar.
     * @var object
     */
    var $preTrack;
    /**
     * The visual representation of the adjustment.
     * @var object
     */
    var $bar;
    /**
     * The portion of the track after the bar.
     * @var object
     */
    var $postTrack;

    /**
     * The value that the adjustment represents.
     * @var double
     */
    var $value;
    /**
     * The lower limit of the value.
     * @var double
     */
    var $lower;
    /**
     * The upper limit of the value.
     * @var double
     */
    var $upper;
    /**
     * The amount that the value should change with one step.
     * @var double
     */
    var $stepIncrement;
    /**
     * The amount that the value should change with one page.
     * @var double
     */
    var $pageIncrement;
    /**
     * The size of one page's worth of data.
     * @var double
     */
    var $pageSize;
	/**
	 * The widget width/height (depending on the orientation)
	 * @var integer
	 */
    var $width = 15;

    /**
     * Constructor.
     *
     * The constructor should set the basic properties of the pseudo
     * widget and then create the more advanced properties. The advanced
     * properties include the bar and the track that it moves in.
     *
     * @access public
     * @param  double $value
     * @param  double $lower
     * @param  double $upper
     * @param  double $step_inc
     * @param  double $page_inc
     * @param  double $page_size
     * @return void
     */
    function Gtk_Styled_Adjustment($value, $lower, $upper, 
                                   $step_inc, $page_inc, $page_size)
    {
        // Check the passed values.
        if (!is_numeric($value)) {
            return $this->_handleError('Gtk_Styled_Adjustment::value is expected to be a number.');
        } else {
            $this->value = $value;
        }
        if (!is_numeric($lower)) {
            return $this->_handleError('Gtk_Styled_Adjustment::lower is expected to be a number.');
        } else {
            $this->lower = $lower;
        }
        if (!is_numeric($upper)) {
            return $this->_handleError('Gtk_Styled_Adjustment::upper is expected to be a number.');
        } else {
            $this->upper = $upper;
        }
        if (!is_numeric($step_inc)) {
            return $this->_handleError('Gtk_Styled_Adjustment::stepIncrement is expected to be a number.');
        } else {
            $this->stepIncrement = $step_inc;
        }
        if (!is_numeric($page_inc)) {
            return $this->_handleError('Gtk_Styled_Adjustment::page increment is expected to be a number.');
        } else {
            $this->pageIncrement = $page_inc;
        }
        if (!is_numeric($page_size)) {
            return $this->_handleError('Gtk_Styled_Adjustment::pageSize is expected to be a number.');
        } else {
            $this->pageSize = $page_size;
        }
        
        // Create the track and the bar.
        $this->_createTrack();
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
     * Get the container that will hold the adjustment track.
     *
     * The properties of the track container determin the direction
     * that the adjustment will be shown. The possible options are
     * GtkVBox or GtkHBox producing a vertical or horizontal adjustment
     * respectively. 
     *
     * @abstract
     * @access   private
     * @param    none
     * @return   widget  The container may not be a child of GtkBin.
     */
    function &_getTrackContainer()
    {
        // Abstract.
        $this->_handleError('Gtk_Styled_Adjustment is an abstract class and ' .
                             'should not be instantiated directly. Use ' .
                             'Gtk_HStyleAdjustment or Gtk_VStyleAdjustment ' .
                             'instead.');
    }
    
    /**
     * Create the track that the bar will live in.
     *
     * The track acts as the boundry for the bar. It contains the
     * widget and sets a limit on the values that the bar may 
     * represent. The properties of the bar may be influenced by
     * the properties of the track.
     *
     * Depending on the type of widget that uses the adjustment, the
     * bar may not have to move with in the track but just grow and
     * shrink within it. (ProgressBar) The track acts as a boundry for
     * the bar no matter how it is used.
     *
     * @access private
     * @param  none
     * @return void
     */
    function _createTrack()
    {
        //  ALL THIS MOVEMENT BELONGS IN THE SCROLL BAR!!!
        //  It is left here for demonstration purposes only.
        
        // Create the track that the bar will live in.
        $this->track =& $this->_getTrackContainer();
        $this->_setTrackSize();

        // Create an event box to represent the empty space to the left.
        $this->preTrack =& new GtkEventBox;
        $this->preTrack->set_usize($this->value, -1);

        // Create an event box to represnet the empty space to the right.
        $this->postTrack =& new GtkEventBox;
        
        // Add everything to the track.
        $this->track->pack_start($this->preTrack, false, false, 0);
        $this->track->pack_start($this->_createBar(), false, false, 0);
        $this->track->pack_start($this->postTrack, true, true, 0);

        // Add the track to the widget.
        $this->widget =& new GtkHBox();
        $this->widget->pack_start($this->track, true, true, 0);

        // Set the sizes.
        $this->_setBarSize();
        $this->_setTrackSize();
        $this->_setBarPosition();
    }
    
    /**
     * Create the bar that visually represents the adjustment.
     *
     * The bar that lives within the track is capable of chaning size
     * and position based on the values that the track represents. 
     *
     * @access private
     * @param  none
     * @return &$object
     */
    function &_createBar()
    {
        $this->bar =& new GtkButton(NULL);
        $this->_setBarSize();
        
        return $this->bar;
    }

    /**
     * Set the size of the bar.
     *
     * The bar size is configurable. Depending on how the adjustment is 
     * used, the bar size may depend entirely on its value or it may 
     * change with respect to its environment. If the adjustment is used
     * as a progress bar, the size of the bar is directly related to the
     * percentage of the task that is complete. If the adjustment is used
     * as a scroll bar, the size of the bar is related to the amount of
     * the scrolling widget that is currently shown compared to its total
     * size.
     *
     * @abstract
     * @access   private
     * @param    none
     * @return   void
     */
    function _setBarSize()
    {
        // Abstract.
        $this->_handleError('Gtk_Styled_Adjustment is an abstract class and ' .
                             'should not be instantiated directly. Use ' .
                             'Gtk_HStyleAdjustment or Gtk_VStyleAdjustment ' .
                             'instead.');
    }
    
    /**
     * Set the bars position within the track.
     *
     * Set the starting position of the bar with in the track. The bar
     * cannot start (length of track) - (length of bar). The position 
     * of the bar is mostly only relavent when the adjustment is being
     * used as a scroll bar or when being used as an activity progress
     * indicator.
     *
     * The bar position is determined by the width/height of the event
     * box that preceeds the bar.
     *
     * @abstract
     * @access   private
     * @param    none
     * @return   void
     */
    function _setBarPosition()
    {
        // Abstract
        $this->_handleError('Gtk_Styled_Adjustment is an abstract class and ' .
                             'should not be instantiated directly. Use ' .
                             'Gtk_HStyleAdjustment or Gtk_VStyleAdjustment ' .
                             'instead.');
    }

    /**
     * Set the size of the entire track.
     *
     * Sets the size of the entire track. The track contains the preTrack
     * the bar and the postTrack.
     *
     * This method is abstract and should be overwritten in the child
     * classes
     *
     * @abstract
     * @access   private
     * @param    none
     * @return   void
     */
    function _setTrackSize()
    {
        // Abstract.
        $this->_handleError('Gtk_Styled_Adjustment is an abstract class and ' .
                             'should not be instantiated directly. Use ' .
                             'Gtk_HStyleAdjustment or Gtk_VStyleAdjustment ' .
                             'instead.');
    }

    /**
     * Set the current adjustment value.
     *
     * Sets the value that the adjustment represents. The value has an
     * impact on the bar position and size.
     *
     * @access public
     * @param  double  $value The new adjustment value.
     * @return void
     */
    function setValue($value)
    {
        // Check to see if the widget has been realized first.
        $this->_checkRealized();
        
        // Set the new value
        $this->value = $value;
        
        // Don't let the value exceed the bounds.
        if ($this->value > $this->upper - $this->pageSize) {
            $this->value = $this->upper - $this->pageSize;
        }
        if ($this->value < $this->lower) {
            $this->value = $this->lower;
        }
        
        // Set the bar position.
        $this->_setBarPosition();

        return $this->value;
    }
    
    /**
     * Set the lower limit of the adjustment.
     *
     * Sets the lower limit of the adjustment widget. The lower limit
     * affects the bar size and position.
     *
     * @access public
     * @param  double $lower The new lower limit value.
     * @return void
     */
    function setLower($lower)
    {
        // Check to see if the widget has been realized first.
        $this->_checkRealized();

        if (!is_numeric($lower)) {
            return $this->_handleError('Lower expects a numeric value.');
        }
        
        $this->lower = $lower;
        $this->_setBarSize();
        $this->_setBarPosition();
    }
    
    /**
     * Set the upper limit of the adjustment.
     *
     * Sets the upper limit of the adjustment widget. The upper limit
     * affects the bar size and position.
     *
     * @access public
     * @param  double $upper The new upper limit value.
     * @return void
     */
    function setUpper($upper)
    {
        // Check to see if the widget has been realized first.
        $this->_checkRealized();

        if (!is_numeric($upper)) {
            return $this->_handleError('Upper expects a numeric value.');
        }
        
        $this->upper = $upper;

        $this->_setBarSize();
        $this->_setBarPosition();
    }
    
    /**
     * Set the size of one step.
     *
     * Sets the size of one step. One step is the amount a scroll bar
     * will move when the arrow at the begining or end of the scroll 
     * bar is pressed.
     *
     * @access public
     * @param  double $increment The new size of one step
     * @return void
     */
    function setStepIncrement($increment)
    {
        // Check to see if the widget has been realized first.
        $this->_checkRealized();

        if (!is_numeric($increment)) {
            return $this->_handleError('Step increment expects a numeric value.');
        }
        
        $this->stepIncrement = $increment;
    }
    
    /**
     * Set the size of one page movement.
     *
     * Sets the size of one page. One page is the amount a scroll bar
     * will move when the space around the bar of a scroll bar is 
     * pressed.
     *
     * @access public
     * @param  double $increment The new size of one page
     * @return void
     */
    function setPageIncrement($increment)
    {
        // Check to see if the widget has been realized first.
        $this->_checkRealized();

        if (!is_numeric($increment)) {
            return $this->_handleError('Page increment expects a numeric value.');
        }
        
        $this->pageIncrement = $increment;
    }
    
    /**
     * Set the size of one page.
     *
     * Sets the size of one page. One page is the size of the display
     * area. Changing the page size has an affect on the bar size and
     * position.
     *
     * @access public
     * @param  double $increment The new size of one page
     * @return void
     */
    function setPageSize($pageSize)
    {
        // Check to see if the widget has been realized first.
        $this->_checkRealized();

        if (!is_numeric($pageSize)) {
            return $this->_handleError('Page size expects a numeric value.');
        }
        
        $this->pageSize = $pageSize;
        $this->_setBarSize();
        $this->_setBarPosition();
    }
    
    /**
     * Notify the system that a change has been made.
     *
     * @access public
     * @param  none
     * @return void
     */
    function changed()
    {
        // Empty
    }
    
    /**
     * Notify the system that a value has changed.
     *
     * @access public
     * @param  none
     * @return void
     */
    function value_changed()
    {
        // Empty.
    }
    
    /**
     * Prevent the bound of the track from being exceeded by the bar.
     *
     * @access public
     * @param  double $lower The lower boundry.
     * @param  double $upper The upper boundry.
     * @return void
     */
    function clamp_page($lower, $upper)
    {
        // Empty.
    }
    
    /**
     * Alias of setValue. Added for API consistency.
     *
     * @see setValue
     */
    function set_value($value)
    {
        $this->setValue($value);
    }
    
    /**
     * Set the style for a portion of the adjustment.
     *
     * Each portion of the adjustment is styleable. The style that
     * is passed will be applied to the portion of the adjustment.
     *
     * @access public
     * @param  string $portion The portion to style.
     * @param  widget &$style  The style to apply.
     * @return void
     */
    function setStyle($portion, $style)
    {
        // Check for the portion.
        if (!isset($this->$portion)) {
            $this->_handleError('Undefined portion: ' . $portion . ' Cannot apply style.');
        }
        $this->$portion->set_style($style);
    }

    /**
     * Set the pix mask for the bar.
     *
     * Make the bar appear to be something other than a rectangle.
     * This lets you create images for the bar so that it can be
     * anything you want. This is the ultimate in adjustment 
     * customizatioin. 
     *
     * NOTE: I don't know what will happen if you change the size
     * of the bar when a mask is applied. Changing the page size
     * and upper and lower values could have undesired effects.
     *
     * @access public
     * @param  object &$mask The image to make the bar appear as.
     * @return void
     */
    function setBarMask(&$mask, $x = 0, $y = 0)
    {
        $this->bar->shape_combine_mask($mask, $x, $y);
    }

    /**
     * Set the contents of the bar to the given widget.
     *
     * This method makes it possible to put a label, pixmap, or
     * any other widget into the bar in order to alter the bar's
     * appearance. When combined with setBarMask(), this method
     * makes the style an shape of the adjustment bar completely
     * controllable by the programmer.
     *
     * The previous contents of the bar are returned after the 
     * new contents have been added.
     *
     * @access public
     * @param  object &$widget The new widget to put in the bar.
     * @return widget          The previous contents of the bar.
     */
    function &setBarContents(&$widget)
    {
        $prevChild = $this->bar->child;
        $this->bar->remove($prevChild);
        $this->bar->add($widget);

        return $prevChild;
    }
    
    /**
     * Get the adjustments value.
     *
     * @access public
     * @param  none
     * @return double
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Get the adjustments lower limit.
     *
     * @access public
     * @param  none
     * @return double
     */
    function getLower()
    {
        return $this->lower;
    }

    /**
     * Get the adjustments upper limit.
     *
     * @access public
     * @param  none
     * @return double
     */
    function getUpper()
    {
        return $this->upper;
    }

    /**
     * Get the adjustments step increment.
     *
     * @access public
     * @param  none
     * @return double
     */
    function getStepIncrement()
    {
        return $this->stepIncrement;
    }

    /**
     * Get the adjustments page increment.
     *
     * @access public
     * @param  none
     * @return double
     */
    function getPageIncrement()
    {
        return $this->pageIncrement;
    }

    /**
     * Get the adjustments page size.
     *
     * @access public
     * @param  none
     * @return double
     */
    function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Check to see that the mouse is within the boundries of the
     * bar.
     *
     * Check the current mouse position to see if it is within
     * the bounds of the adjustment bar. It is only necessary to
     * check if the mouse is with in one direction of the bar's
     * alloted space. For example, we only care if the mouse is
     * within the y-range of a vertical adjustment.
     *
     * @abstract
     * @access   public
     * @param    none
     * @return   boolean
     */
    function checkMouseBounds()
    {
        // Abstract.
        $this->_handleError('Gtk_Styled_Adjustment is an abstract class and ' .
                            'should not be instantiated directly. Use ' .
                            'Gtk_HStyleAdjustment or Gtk_VStyleAdjustment ' .
                            'instead.');
    }

    /**
     * Checks to make sure that the widgets have been realized.
     *
     * Many of the classes that use Gtk_Styled_Adjustment objects
     * expect that the widgets have been realized. This is 
     * becuase they use properties which are only set after
     * object realization. 
     *
     * @access private
     * @param  none
     * @return boolean true if realized, false if not
     */
    function _checkRealized()
    {
        if (!isset($this->widget->window)) {
            $this->_handleError('Setting values on a Gtk_Styled_Adjustment ' . 
                                'that has not yet been realized can have ' .
                                'unexpected results.');
            return false;
        } else {
            return true;
        }
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
        PEAR::raiseError($msg, $code, $pearMode);
    }
}
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>