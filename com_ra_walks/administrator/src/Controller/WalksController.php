<?php

/**
 * @version    1.0.0
 * @package    com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 14/12/24 CB use LoadHelper to refresh walks
 */

namespace Ramblers\Component\Ra_walks\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_walks\Site\Helpers\LoadHelper;
use Ramblers\Component\Ra_walks\Site\Helpers\MergeHelper;

/**
 * Walks list controller class.
 *
 * @since  4.0.0
 */
class WalksController extends AdminController {

    protected $db;
    protected $app;
    protected $objHelper;
    private $walksfound = 0;
    private $walksupdated = 0;
    private $walkscreated = 0;
    private $counter = 0;

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDbo();
        $this->objHelper = new ToolsHelper;
        $this->app = Factory::getApplication();
        $this->back = 'administrator/index.php?option=com_ra_tools&view=reports';
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
    }

    public function cancel($key = null, $urlVar = null) {
        $this->setRedirect('index.php?option=com_ra_tools&view=dashboard');
    }

    /**
     * Method to clone existing Walks
     *
     * @return  void
     *
     * @throws  Exception
     */
    public function duplicate() {
        // Check for request forgeries
        $this->checkToken();

        // Get id(s)
        $pks = $this->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_RA_WALKS_NO_ELEMENT_SELECTED'));
            }

            ArrayHelper::toInteger($pks);
            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(Text::_('COM_RA_WALKS_ITEMS_SUCCESS_DUPLICATED'));
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_ra_walks&view=walks');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    Optional. Model name
     * @param   string  $prefix  Optional. Class prefix
     * @param   array   $config  Optional. Configuration array for model
     *
     * @return  object	The Model
     *
     * @since   4.0.0
     */
    public function getModel($name = 'Walk', $prefix = 'Administrator', $config = array()) {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function merge() {
//      set up maximum time of 10 minutes
        $max = 10 * 60;
        set_time_limit($max);

        $MergeHelper = new MergeHelper;
        $MergeHelper->host = 'shareddb-f.hosting.stackcp.net';       // Database host name
        $MergeHelper->database = 'cl10-www-03'; // Database name
        $MergeHelper->user = 'cl10-www-03';         // User for database authentication
        $MergeHelper->password = 'sY^wezYzD'; // Password for database authentication
        $MergeHelper->prefix = 'i1oj4_';     // Database prefix
        $MergeHelper->merge();
    }

    public function refresh() {
//      set up maximum time of 10 minutes
        $max = 10 * 60;
        set_time_limit($max);

        $loadHelper = new LoadHelper;
        $loadHelper->online_mode = true;
        $loadHelper->refresh();

        if ($loadHelper->error_count > 0) {
            echo '<b>Errors</b><br>';
            foreach ($loadHelper->errors as $error) {
                echo $error . '<br>';
            }
        }
        if ($loadHelper->warning_count > 0) {
            echo '<b>Warnings</b><br>';
            foreach ($loadHelper->warnings as $warning) {
                echo $warning . '<br>';
            }
        }
        if ($loadHelper->comment_count > 0) {
            echo '<b>Comments</b><br>';
            foreach ($loadHelper->comments as $comment) {
                echo $comment . '<br>';
            }
        }
        echo $this->objHelper->buildButton('administrator/index.php?option=com_ra_walks&task=reports.showLogfile', "Show Logfile", False, 'grey');
        echo $this->objHelper->backButton('administrator/index.php?option=com_ra_walks&view=reports');
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * @throws  Exception
     */
    public function saveOrderAjax() {
        // Get the input
        $pks = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        Factory::getApplication()->close();
    }

}
