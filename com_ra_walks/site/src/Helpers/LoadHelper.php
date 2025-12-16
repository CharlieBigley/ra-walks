<?php

/**
 * Helper to load walks from WalksManager feed
 * Usually run from a batch job via cron, but can be invoked from the dashboard
 * with menu option "Refresh walks".
 *
 * If run on-line, messages are display directly; if in batch mode, messages are
 * stored in arrays for later display
 *
 * @author charles
 * 13/12/24 CB created
 */

namespace Ramblers\Component\Ra_walks\Site\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class LoadHelper {

    protected $db;
    protected $app;
    protected $toolsHelper;
    private $walksfound = 0;
    private $walksupdated = 0;
    private $walkscreated = 0;
    private $counter = 0;
    public $online_mode = false;
    public $comments;
    public $comment_count;
    public $errors;
    public $error_count;
    public $warnings;
    public $warning_count;

    public function __construct() {

        $this->db = Factory::getDbo();
        $this->toolsHelper = new ToolsHelper;
        $this->app = Factory::getApplication();
    }

    private function doesWalkExist($walkid) {

        $query = $this->db->getQuery(true);

// Select everything from the table that matches the walk id.
        $query->select(' a.*')
                ->from('`#__ra_walks` AS a')
                ->where('a.walk_id = ' . (int) $walkid)
        ;

        $this->db->setQuery((string) $query);

        $results = $this->db->loadObjectList();

// See if we got anything back - ie does the walk exist
        if (count($results) == 0) {
            return false;
        } else {
            return true;
        }
    }

// doesWalkExist ($walkid)

    function getRows($sql) {
        try {
            $query = $this->db->getQuery(true);
            $this->db->setQuery($sql);
            $this->db->execute();
            $this->rows = $this->db->getNumRows();
//            print_r($this->rows);
            $rows = $this->db->loadObjectList();
            return $rows;
        } catch (Exception $ex) {
            $this->error = $ex->getCode() . ' ' . $ex->getMessage();
            $this->showMessage($this->error, '1');
            return false;
        }
    }

    /**
     *   Get the walks data from the feed (either a test file or the ramblers JSON feed);
     */
    private function getWalksData($code) {
//        if ($code == 'ER') {
//            return;
//        }
//        $this->showMessage('Getting walks for ' . $code);
        $url = 'https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?types=group-walk';
        $url .= '&api-key=742d93e8f409bf2b5aec6f64cf6f405e';
        $url .= '&groups=' . $code;
//        $url .= '&limit=3';
//        $url .= '&dow=7';
//        $url = 'https://www.ramblers.org.uk/api/lbs/walks?groups=NS01&dow=1&limit=3';
//      set up maximum time of 10 minutes
        $max = 10 * 60;
        set_time_limit($max);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false); // do not include header in output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // do not follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // do not output result
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  // allow xx seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);  // allow xx seconds for timeout
//			curl_setopt($ch, CURLOPT_REFERER, JURI::base()); // say who wants the feed

        curl_setopt($ch, CURLOPT_REFERER, "com_ra_wf"); // say who wants the feed

        $data = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            print('Error code: ' . $error . "\n");
            print('Http return: ' . $httpCode . "\n");
            $this->showMessage('Access failed', '3');

            $this->logit("Feed access failed for feed : " . $url, $httpCode);
            return;
        }

        $temp = json_decode($data);
        $summary = $temp->summary;
//        print('Limit=', $summary->limit . ', count=' . $summary->count. "\n");
        return $temp->data;
    }

// getWalksData()

    public function info() {
        return 'LoadHelper is in file ' . __FILE__;
    }

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

    /**
     *   Process the walks data
     */
    private function processwalks($walkslist) {
        foreach ($walkslist as $walk) {
            $this->counter++;
            $this->writeWalk($walk);
//            if ($this->counter == 100) {
//                return;
//            }
        }
    }

    public function refresh($areacode = 'ALL') {
        $message = 'WalksRefresh started in ';
        if ($this->online_mode) {
            $message .= 'online mode';
        } else {
            $message .= 'batch mode';
        }
        $params = ComponentHelper::getParams('com_ra_walks');
        $option = $params->get('option');
        $sql .= 'SELECT code from #__ra_areas ';
        if ($option == '1') {
            $code = $params->get('area');
            $message .= ' for Area ' . $code;
            $sql .= 'WHERE code="' . $code . '"';
        } else {
            $message .= ' for all Areas';
        }

        $this->showMessage($message);

        $sql .= ' ORDER BY code';
        $rows = $this->getRows($sql);
        foreach ($rows as $row) {
//            $message = 'Processing ' . $row->code . ' ';
            $walkslist = $this->getWalksData($row->code);

            if (is_null($walkslist)) {
                $message .= ': Failed to get data';
                $this->showMessage($message, '1');
            } else {
                $this->walksfound = count($walkslist);
                $message = $this->walksfound . ' records found for ' . $row->code;
                $this->showMessage($message);
                $this->processwalks($walkslist);
            }
        }
        var_dump($this->comments);
        $this->showMessage("Walks in feed = $this->counter , Walks created = $this->walkscreated , Walks updated = $this->walksupdated ");
        return true;
    }

    private function showMessage($message, $type = '3') {
        $this->logit($message, $type);
        if ($this->online_mode == true) {
            if ($type == '1') {
                echo 'Error: ' . $message . '<br>';
            } elseif ($type == '2') {
                echo 'Warning: ' . $message . '<br>';
            } else {
                echo $message . '<br>';
            }
        } else {
            if ($type == '1') {
                $this->errors[] = $message;
                $this->error_count++;
            } elseif ($type == '2') {
                $this->warnings[] = $message;
                $this->warning_count++;
            } else {
                $this->comments[] = $message;
                $this->comment_count++;
            }
        }
    }

    /**
     *   Write a walk to the database
     */
    private function writeWalk($walk) {
        $date = Factory::getDate('now', Factory::getConfig()->get('offset'))->toSql(true);
        $walk_date = substr($walk->start_date_time, 0, 10);

//        if ($walk->id == 100295864) {
//            var_dump($walk);
//            echo '<br>';
//            return;
//        }
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

            $walk_date = substr($walk->start_date_time, 0, 10);
            $start_time = substr($walk->start_date_time, 11, 5);
            $end_time = substr($walk->end_date_time, 11, 5);
            if (is_null($walk->description)) {
                $description = '(blank)';
            } else {
                $description = $walk->description;
            }
            $difficulty = substr($walk->difficulty->description, 0, 10);
            if ($walk->shape == 'linear') {
                $shape = 'L';
            } else {
                $shape = 'C';
            }

            if (is_null($walk->start_location)) {
                $start_details = '';
                $start_grid_ref = '';
                $start_latitude = 0;
                $start_longitude = 0;
                $start_postcode = '';
            } else {
                $start_details = $walk->start_location->description;
                $start_grid_ref = $walk->start_location->grid_reference_10;
                $start_latitude = (float) $walk->start_location->latitude;
                $start_longitude = (float) $walk->start_location->longitude;
                $start_postcode = $walk->start_location->postcode;
            }

//            $title = substr($walk->title, 0, 120);
//            $title = substr(iconv(($walk->title, mb_detect_order(), true), "UTF-8", $walk->title), 0, 120);

            $title = substr($walk->title, 0, 120);

            if ((is_null($walk->walk_leader)) or (is_array($walk->walk_leader))) {
                $phone = '';
                $leader_name = '';
            } else {
                $phone = substr(preg_replace('/\D/', '', $walk->walk_leader->telephone), 0, 15);
                $leader_name = $walk->walk_leader->name;
            }

            $query->set("walk_id = " . $this->db->quote($walk->id))
                    ->set("walk_date = " . $this->db->quote($walk_date))
                    ->set("group_code = " . $this->db->quote($walk->group_code))
                    ->set("contact_display_name = " . $this->db->quote($leader_name))
// must increase size of database field
//                ->set('contact_email = ' . $this->db->quote($walk->walk_leader->email_form))
                    ->set('contact_tel1 = ' . $this->db->quote($phone))
                    ->set("title = " . $this->db->quote(substr($title, 0, 120)))
                    ->set("description = " . $this->db->quote($description))
                    ->set("start_time = " . $this->db->quote($start_time))
                    ->set("start_gridref = " . $this->db->quote($start_grid_ref))
                    ->set("start_latitude = " . $this->db->quote($start_latitude))
                    ->set("start_longitude = " . $this->db->quote($start_longitude))
                    ->set("start_postcode = " . $this->db->quote($start_postcode))
                    ->set("start_details = " . $this->db->quote($start_details))
                    ->set("distance_km = " . $this->db->quote($distance_km))
                    ->set("distance_miles = " . $this->db->quote($distance_miles))
                    ->set("difficulty = " . $this->db->quote($difficulty))
//                ->set("pace = " . $this->db->quote($walk->pace))
                    ->set("ascent_feet = " . $this->db->quote($walk->ascent_feet))
                    ->set("ascent_metres = " . $this->db->quote($walk->ascent_metres))
                    ->set("circular_or_linear = " . $this->db->quote($shape))
                    ->set("finish_time = " . $this->db->quote($end_time))
                    ->set("state=1")
            ;

            if ($this->doesWalkExist($walk->id)) {
                $query->update('#__ra_walks')
                        ->set("modified = " . $this->db->quote($date))
                        ->where('walk_id=' . $walk->id);

                $result = $this->db->setQuery($query)->execute();
                $this->walksupdated++;
            } else {
                $query->insert('#__ra_walks')  // utf8mb4_unicode_ci
                        ->set("created = " . $this->db->quote($date));
                $result = $this->db->setQuery($query)->execute();
//                if (JDEBUG) {
//                $this->showMessage("walk $walk->id for $walk->group_code created");
//                echo $this->db->replacePrefix($query);
//                die;
//                }
                $this->walkscreated++;
            }
        }
    }

// writeWalk
}
