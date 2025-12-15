<?php

/**
 * Custom Field class for filtering Walks
 *
 * @package		com_ramblers
 * @since		1.3.3
 */
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldRange extends JFormFieldList {

    protected $type = 'Range';

    /**
     * Method to get the field options.
     *
     * @return	array	The field option objects.
     */
    public function getOptions() {
        $options = array(
            'mine' => 'My range',
            'more8' => '8 miles or more',
            'less8' => 'Less than 8 miles',
            'all' => 'All ranges',
        );
        return $options;
    }

}
