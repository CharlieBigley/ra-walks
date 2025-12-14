<?php

/*
 * Installation script
 * 01/08/23 CB Create from MailMan script
 * 21/08/23 CB copy ra_read_feed.php
 * 31/11/23 CB use Factory::getContainer()->get('DatabaseDriver');
 * 04/12/23 CB use factory
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

class Com_Ra_walksInstallerScript {

    private $component;
    private $minimumJoomlaVersion = '4.0';
    private $minimumPHPVersion = JOOMLA_MINIMUM_PHP;

    public function getDatabaseVersion($component = 'com_ra_walks') {
// Get the extension ID
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $eid = $this->getExtensionId($component);

        if ($eid != null) {
// Get the schema version
            $query = $db->getQuery(true);
            $query->select('version_id')
                    ->from('#__schemas')
                    ->where('extension_id = ' . $db->quote($eid));
            $db->setQuery($query);
            $version = $db->loadResult();

            return $version;
        }

        return null;
    }

    /**
     * Loads the ID of the extension from the database
     *
     * @return mixed
     */
    public function getExtensionId($component = 'com_ra_walks') {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true);
        $query->select('extension_id')
                ->from('#__extensions')
                ->where($db->qn('element') . ' = ' . $db->q($component) . ' AND type=' . $db->q('component'));
        $db->setQuery($query);
        $eid = $db->loadResult();
        if (is_null($eid)) {
            echo 'Can\'t find Extension id for ' . $component . '<br>';
            echo $db->replacePrefix($query) . '<br>';
        }
        return $eid;
    }

    public function getVersion($component = 'com_ra_walks') {
        $version = '';
        $extension_id = $this->getExtensionId($component);
        if ($extension_id) {
            $sql = 'SELECT version_id from #__schemas WHERE extension_id=' . $extension_id;
//            echo 'Seeking  ' . $sql . '<br>';
//        echo $objHelper->getValue($sql) . ')</a></li>' . '<br>';
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select('version_id')
                    ->from('#__schemas')
                    ->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
            $db->setQuery($query);

            $version = $db->loadResult();
            if ($version == false) {
                echo 'Can\'t find version for ' . $component . '<br>';
                echo $db->replacePrefix($query) . '<br>';
            }
        }
        return $version;
    }

    public function install($parent): bool {
        echo '<p>Installing RA Walks (com_ra_walks) ' . '</p>';

        return true;
    }

    public function uninstall($parent): bool {
        echo '<p>Uninstalling RA Walks (com_ra_walks)</p>';

        return true;
    }

    public function update($parent): bool {
        echo '<p>Updating RA Walks (com_ra_walks)</p>';

// You can have the backend jump directly to the newly updated component configuration page
// $parent->getParent()->setRedirectURL('index.php?option=com_ra_walks');
        return true;
    }

    public function preflight($type, $parent): bool {
        echo '<p>Preflight RA Walks (type=' . $type . ')</p>';

        if ($type !== 'uninstall') {
            if (!empty($this->minimumPHPVersion) && version_compare(PHP_VERSION, $this->minimumPHPVersion, '<')) {
                Log::add(
                        Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPHPVersion),
                        Log::WARNING,
                        'jerror'
                );
                return false;
            }

            if (!empty($this->minimumJoomlaVersion) && version_compare(JVERSION, $this->minimumJoomlaVersion, '<')) {
                Log::add(
                        Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomlaVersion),
                        Log::WARNING,
                        'jerror'
                );
                return false;
            }
        }
        if (ComponentHelper::isEnabled('com_ra_tools', true)) {
            echo 'com_ra_tools found, version=' . $this->getVersion('com_ra_tools');
            return true;
        } else {
            Log::add('com_ra_tools not found - this must be installed first',
                    Log::WARNING,
                    'jerror'
            );
            echo 'com_ra_tools not found - this must be installed first';
            return false;
        }
    }

    public function postflight($type, $parent) {

        '<p>Postflight RA Walks (com_ra_walks)</p>';
        if ($type == 'uninstall') {
            return 1;
        }
        echo '<p>com_ra_walks is now at version ' . $this->getVersion() . '</p>';

        $new_script = JPATH_SITE . "/components/com_ra_walks/ra_read_feed.php";
        $target = JPATH_SITE . '/cli/ra_read_feed.php';
        if (file_exists($new_script)) {
            echo 'Copying ' . $new_script . ' to ' . $target;
            copy($new_script, $target);
            if (file_exists($target)) {
                echo ' Success<br>';
            } else {
                echo ' Failed<br>';
            }
        } else {
            echo $new_script . ' not found<br>';
        }

// ALTER TABLE `j4_ra_walks` CHANGE `distance_miles` `distance_miles` DECIMAL(4,1) NULL DEFAULT '0.0'; 

//        if ($this->getDatabaseVersion() == $this->release) {
//            echo '<p>RA Walks Repeating upgrade of com_ra_walks to ' . $this->release . '</p>';
//        } else {
//            echo '<p>RA Walks Postflight from ' . $this->current_version . ' to ' . $this->release . '</p>';
//        }
//        $sql = "INSERT INTO `dev_ra_mail_access` (`id`, `name`)";
//        $sql .= "VALUES ('1', 'Subscriber'), ('2', 'Author') ,('3', 'Owner') ";
        return true;
    }

    private function ra_feedback_summary() {
        $details = 'CREATE TABLE ra_feedback_summary`(id` INT UNSIGNED NOT NULL AUTO_INCREMENT, '
                . '`walk_id` INT NOT NULL, '
                . '`date_created` VARCHAR(10) NOT NULL, '
                . ' PRIMARY KEY (`id`)'
                . ') ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;
        ';
        $details2 = 'ADD KEY `idx_wfs_walk_id` (`walk_id`);';
        $this->checkTable('ra_feedback_summary', $details, $details2);
    }
}
