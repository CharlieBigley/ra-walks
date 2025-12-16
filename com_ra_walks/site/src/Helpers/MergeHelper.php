<?php

/**
 * Helper to copy walks from one database to another
 * Has to be run ad-hoc from WalksController
 *
 * @author charles
 * 13/12/24 CB created
 */

namespace Ramblers\Component\Ra_walks\Site\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseFactory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class MergeHelper {

    public $host;
    public $database;
    public $user;
    public $password;
    public $prefix;
    public $table = '#__ra_walks';
    protected $db;
    protected $dbExternal;
    protected $app;
    protected $toolsHelper;
    private $walksfound = 0;
    private $walksupdated = 0;
    private $counter = 0;
    private $rows;

    public function __construct() {

        $this->db = Factory::getDbo();
        $this->toolsHelper = new ToolsHelper;
        $this->app = Factory::getApplication();
    }

    private function doesWalkExist($walkid) {
        // checks if the walk is already present in the current database

        $sql = 'SELECT id FROM #__ra_walks WHERE walk_id' . (int) $walkid;

        $results = $this->toolsHelper->getValue($sql);

// See if we got anything back - ie does the walk exist
        if (count($results) == 0) {
            return false;
        } else {
            return true;
        }
    }

// doesWalkExist ($walkid)

    /**
     *   Get the walks data from the feed (either a test file or the ramblers JSON feed);
     */
    private function getWalksData($code) {

//        $this->showMessage('Getting walks for ' . $code);
// this gets the data from the external database
        $sql = 'SELECT * FROM ' . $this->table;
        $sql .= ' WHERE group_code="' . $code . '" ';
        $sql .= 'ORDER BY id';
        try {
            $query = $this->dbExternal->getQuery(true);
            $this->dbExternal->setQuery($sql);
            $this->dbExternal->execute();
            $this->rows = $this->dbExternal->getNumRows();
//            print_r($this->rows);
            $walks = $this->dbExternal->loadObjectList();
            return $rows;
        } catch (Exception $ex) {
            $this->rows = 0;
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
            $this->showMessage($this->error, '1');
            return false;
        }
        return $walks;
    }

// getWalksData()

    /**
     *   Store a log entry
     */
    public function logit($text, $record_type = 'WM') {

        $query = $this->db->getQuery(true);

        $query->insert('#__ra_logfile')
                ->set("record_type = " . $this->db->quote($record_type))
                ->set("message = " . $this->db->quote($text))
                ->set("ref = " . $this->db->quote('LoadWalks'))
        ;

        $result = $this->db->setQuery($query)->execute();
    }

// logit ($text , $code = 0)

    public function merge() {
        $this->showMessage('WalksMerge started');
// set up connection to remote database
        $options = array();
        $options['driver'] = 'mysqli';           // Database driver name
        $options['host'] = $this->host;         // Database host name
        $options['database'] = $this->database; // Database name
        $options['user'] = $this->user;         // User for database authentication
        $options['password'] = $this->password;  // Password for database authentication
        $options['prefix'] = $this->prefix;     // Database prefix
// external database connection, used to get the remote walks
        $dbFactory = new DatabaseFactory();
        var_dump($options);
        $this->dbExternal = $dbFactory->getDriver('mysqli', $options);

        $sql .= 'SELECT code from #__ra_areas ';
        $sql .= ' ORDER BY code';

        $rows = $this->toolsHelper->getRows($sql);
        foreach ($rows as $row) {
            $message = 'Processing ' . $row->code . ' ';
            $walks = $this->getWalksData($row->code);

            if ($this->rows == 0) {
                $message .= ': Failed to get data';
                $this->showMessage($message, '1');
            } else {
                $message .= $this->rows . ' records found';
                $this->showMessage($message);
                foreach ($walks as $walk) {
                    $this->counter++;
//                    if ($this->counter == 100) {
//                        return;
//                    }
                    $this->writeWalk($walk);
                }
            }
        }
//        $this->showMessage('Walks created=' . $this->walkscreated);
//        $this->showMessage('Walks cUpdated=' . $this->walksupdated);
        $this->showMessage("Walks in feed = $this->counter , Walks created = $this->walkscreated , Walks updated = $this->walksupdated ");
    }

    private function showMessage($message, $type = '3') {
        $this->logit($message, $type);
        if ($type == '1') {
            echo 'Error: ' . $message . '<br>';
        } elseif ($type == '2') {
            echo 'Warning: ' . $message . '<br>';
        } else {
            echo $message . '<br>';
        }
    }

    /**
     *   Write a walk to the database
     */
    private function writeWalk($walk) {
        $date = Factory::getDate('now', Factory::getConfig()->get('offset'))->toSql(true);
        $walk_date = substr($walk->start_date_time, 0, 10);
        if ($this->doesWalkExist($walk->id)) {
            return;
        }
        if (($walk->distance_miles > 99.9) OR ($walk->distance_km > 99.9)) {
            $error = 'Group=' . $walk->group_code;
            $error .= ': Out of range: id ' . $walk->id;
            $error .= ', date ' . $walk_date;
            $error .= ', km ' . $walk->distance_km;
            $error .= ', mi ' . $walk->distance_miles;
            $this->showMessage($error, '2');
            $distance_km = 99.9;
            $distance_miles = 99.9;
        } elseif (($walk->distance_miles == '') OR ($walk->distance_km == '')) {
            $error = 'Group=' . $walk->group_code;
            $error .= ': Blank distance: id ' . $walk->id;
            $error .= ', date ' . $walk_date;
//            $error .= ', km ' . $walk->distance_km;
//            $error .= ', mi ' . $walk->distance_miles;
//            $this->showMessage($error, '2);
            $distance_km = 0;
            $distance_miles = 0;
            return;
        } else {
            $distance_km = $walk->distance_km;
            $distance_miles = $walk->distance_miles;
        }

        if ((int) $walk->id == 0) {
            $error = "Walk $this->counter has blank id field";
            $this->showMessage($error, '2');
        } else {
            $query = $this->db->getQuery(true);

            if (is_null($walk->description)) {
                $description = '(blank)';
            } else {
                $description = $walk->description;
            }
//                $difficulty = substr($walk->difficulty, 0, 10);
//            $title = substr($walk->title, 0, 120);
//            $title = substr(iconv(($walk->title, mb_detect_order(), true), "UTF-8", $walk->title), 0, 120);

            $title = substr($walk->title, 0, 120);

//            if ((is_null($walk->walk_leader)) or (is_array($walk->walk_leader))) {
//                $phone = '';
//                $leader_name = '';
//            } else {
//                $phone = substr(preg_replace('/\D/', '', $walk->contact_tel1), 0, 15);
//            }

            $query->set("walk_id = " . $this->db->quote($walk->id))
                    ->set("walk_date = " . $this->db->quote($walk->date))
                    ->set("group_code = " . $this->db->quote($walk->group_code))
                    ->set("contact_display_name = " . $this->db->quote($walk->contact_display_name))
// must increase size of database field
//                ->set('contact_email = ' . $this->db->quote($walk->walk_leader->email_form))
                    ->set('contact_tel1 = ' . $this->db->quote($walk->contact_tel1))
                    ->set("title = " . $this->db->quote($walk->title))
                    ->set("description = " . $this->db->quote($walk->description))
                    ->set("start_time = " . $this->db->quote($walk->start_time))
                    ->set("start_gridref = " . $this->db->quote($walk->start_grid_ref))
                    ->set("start_latitude = " . $this->db->quote($walk->start_latitude))
                    ->set("start_longitude = " . $this->db->quote($walk->start_longitude))
                    ->set("start_postcode = " . $this->db->quote($walk->start_postcode))
                    ->set("start_details = " . $this->db->quote($swalk->tart_details))
                    ->set("distance_km = " . $this->db->quote($walk->distance_km))
                    ->set("distance_miles = " . $this->db->quote($walk->distance_miles))
                    ->set("difficulty = " . $this->db->quote($walk->difficulty))
//                ->set("pace = " . $this->db->quote($walk->pace))
                    ->set("ascent_feet = " . $this->db->quote($walk->ascent_feet))
                    ->set("ascent_metres = " . $this->db->quote($walk->ascent_metres))
                    ->set("circular_or_linear = " . $this->db->quote($walk->circular_or_linear))
                    ->set("finish_time = " . $this->db->quote($walk->finish_time))
                    ->set("state=1")
                    ->set("modified = " . $this->db->quote($date));

            $query->update('#__ra_walks')
                    ->where('walk_id=' . $walk->id);

            $result = $this->db->setQuery($query)->execute();
            $this->walksupdated++;
        }
    }

// writeWalk
}
