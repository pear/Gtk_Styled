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
 * A horizontal styleable adjustment pseudo widget.
 *
 * This adjustments bar moves from left to right when the value
 * is changed. All components are packed into an hBox.
 *
 * This class inherits from Gtk_Styled_Adjustment.
 *
 * @author     Scott Mattocks <scottmattocks@php.net>
 * @version    @VER@
 * @category   Gtk
 * @package    Gtk_Styled
 * @subpackage Adjustment
 * @license    PHP version 3.0
 * @copyright  Copyright &copy; 2005 Scott Mattocks
 * @see        Gtk_Styled_Adjustment
 */
require_once 'Gtk/Styled/Adjustment.php';
class Gtk_Styled_HAdjustment extends Gtk_Styled_Adjustment {

    function &_getTrackContainer()
    {
        $container =& new GtkHBox;
        $container->set_usize($this->pageSize, $this->width);
        
        return $container;
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
     * the scrolling widget that is currently show compared to its total
     * size.
     *
     * @access private
     * @param  none
     * @return void
     */
    function _setBarSize()
    {
        if ($this->pageSize >= $this->upper) {
            $this->bar->set_usize($this->widget->allocation->width, -1);
            $this->postTrack->set_usize(0, -1);
        } else {
            $ts = $this->widget->allocation->width;
            $this->bar->set_usize($ts * ($this->pageSize / $this->upper), -1);
        }
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
     * @access private
     * @param  none
     * @return void
     */
    function _setBarPosition()
    {
        // tb = Top of Bar
        $tb = ceil($this->value * (($this->widget->allocation->width) / $this->upper));
        $this->preTrack->set_usize($tb, -1);
    }

    /**
     * Get the adjustment value based on a new bar position.
     *
     * Sometimes it is necessary to first set the position of the bar
     * and then the value of the adjustment. This method calculates the
     * value based on the current bar position and a change in the bar's
     * position. This method is mostly used for dragging the bar within
     * the track while scrolling.
     *
     * @access public
     * @param  double $changeInPosition The amount the position should change
     * @return double
     */
    function getValueFromPosition($changeInPosition)
    {
        // tbc = Top of Bar Current
        $tbc   = $this->preTrack->allocation->width;
        $value = ($tbc + $changeInPosition) / (($this->widget->allocation->width) / $this->upper); 

        return $value;
    }

    /**
     * Set the size of the entire track.
     *
     * Sets the size of the entire track. The track contains the preTrack
     * the bar and the postTrack.
     *
     * @access   private
     * @param    none
     * @return   void
     */
    function _setTrackSize()
    {
        if (isset($this->widget->allocation->width)) {
            $this->track->set_usize($this->widget->allocation->width, $this->width);
        }
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
        $pointer = $this->widget->window->pointer;
        $bar     = $this->widget->allocation;

        if ($pointer[0] > $bar->x + $bar->width) {
            return false;
        } elseif ($pointer[0] < $bar->x) {
            return false;
        } else {
            return true;
        }
    }

}
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>