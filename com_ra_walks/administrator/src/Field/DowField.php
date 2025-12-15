<?php

/**
 * Custom Field class for filtering Walks
 *
 * @package		com_ramblers
 * @since		1.3.3
 */

namespace Ramblers\Component\Ra_walks\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Firm\Field\FormField;

//use \Joomla\CMS\Form\Field\ListField;
//FormHelper::loadFieldClass('list');

class DowField extends ListField {

    protected $type = 'Dow';

    /**
     * Method to get the field options.
     *
     * @return	array	The field option objects.
     */
    public function getOptions() {
        $options = array(
            '2' => 'Monday',
            '3' => 'Tuesday',
            '4' => 'Wednesday',
            '5' => 'Thursday',
            '6' => 'Friday',
            '7' => 'Saturday',
            '1' => 'Sunday',
        );
        return $options;
    }

}
