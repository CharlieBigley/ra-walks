<?php
/**
 * @version    1.0.0
 * @package    com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 21/08/23 CB show link to JSON feed, pretty date
 * 04/03/24 leave space before phone number
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\JsonHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;
use Ramblers\Component\Ra_walks\Site\Helpers\WalkHelper;

$canEdit = Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_ra_walks');

if (!$canEdit && Factory::getApplication()->getIdentity()->authorise('core.edit.own', 'com_ra_walks')) {
    $canEdit = Factory::getApplication()->getIdentity()->id == $this->item->created_by;
}
//==================================================================
$JsonHelper = new JsonHelper;
$toolsHelper = new ToolsHelper;
$objTable = new ToolsTable;
$objWalk = new WalkHelper($this->item);
$objTable->add_column("Walk ID", "R");
$walk_id = $this->item->walk_id;
$days_to_go = (int) $objWalk->getDaystoGo();
$walks_follow = ComponentHelper::isEnabled('com_ra_wf', true);

$walks_follow = false;

if ($walks_follow) {
// see if the user is already following this walk
    $already_following = 0;
    $followers = $objWalk->getFollowers();
    foreach ($followers as $follower) {
//    echo 'User=' . $this->user->id . ', ' . $follower->user_id . ', ' . $follower->member . '<br>';
        if ($follower->user_id == $this->user->id) {
            $already_following = 1;
        }
    }
}
// Determine the id of the current user, and if SuperUser
$isSuperuser = $toolsHelper->isSuperuser();

$details = $walk_id;
if ($days_to_go > 0) {
    // If the walk has not yet taken place, show the content of the JSON feed
    $details .= $JsonHelper->showWalk($this->item->walk_id);
}
if ($isSuperuser) {
    $details .= " (internal id=" . $this->item->id . ")";
}

$objTable->add_column($details, "L");
$objTable->generate_header();

$objTable->add_item("Group ");
$group_code = $this->item->group_code;
$details = $group_code . ' ' . $toolsHelper->lookupGroup($group_code);
$objTable->add_item($details);
$objTable->generate_line();

$objTable->add_item("Date ");
//$pretty_date = JHtml::_('date', $this->item->walk_date, 'd/mm/yy');
//$details = JHtml::_('date', $this->item->walk_date, 'd-m-Y') . ', ';
$details = '<b>' . date_format(date_create($this->item->walk_date), 'D j F y') . '</b>, ';
//$details = $this->objWalk->getDate(3) . ', ';
if ($days_to_go == 0) {
    $details .= 'today';
} else {
    $details .= abs($days_to_go) . ' days ';
    if ($days_to_go > 0) {
        $details .= 'to go';
    } else {
        $details .= 'ago';
    }
}
$objTable->add_item($details);
$objTable->generate_line();

$objTable->add_item("Details ");
$details = "<b>" . $this->item->title . "</b>";
if ($this->item->description != $this->item->title) {
    $details .= "<br>" . $this->item->description;
}
if ($this->item->notes > "") {
    $details .= "<br>" . $this->item->notes;
}
//}
$objTable->add_item($details);
$objTable->generate_line();

$objTable->add_item("Leader ");
$details = $this->item->contact_display_name;
if ($walks_follow) {
    if ($this->user->id > 0) {
        $leader_user_id = $this->item->leader_user_id;
        if ($isSuperuser) {
            $details .= ' (' . $leader_user_id . ')';
        }
        if ($leader_user_id == 0) {
            $details .= " (This leader is not registered, and is not able to send emails)";
        } elseif ($leader_user_id == $this->user->id) {
            $details .= " (You are the leader, and are able to send emails to all followers)";
        } else {
            $details .= " (This leader is registered, and is able to send emails)";
        }
        //    if (($this->user->id == 0) AND ($days_to_go < 0)) {
//        $details = 'Please login to see contact details';
//    }
    }
}
//$contact = $objWalk->contactDetails();
//if ($contact != '') {
//    $details .= ' ' . $contact;
//}

$objTable->add_item($details);
$objTable->generate_line();

if ($this->item->state != 1) {
    $objTable->add_item("State ");
    if ($this->item->state == 0) {
        $objTable->add_item("Draft");
        $line_colour = "#ffffaa";
    } elseif ($this->item->state == 2) {
        $objTable->add_item("Cancelled");
        $line_colour = "#ff00ff";
    } else {
        $objTable->add_item("Status" . $this->item->state);
        $line_colour = "#b0e0e6";
    }
    $objTable->generate_line($line_colour);
}

if ($details = $objWalk->meetingDescription()) {
    $objTable->add_item("Meet ");
    if ($button = $objWalk->meetingLocation()) {
        $details .= ' ' . $button;
    }
    $objTable->add_item($details);
    $objTable->generate_line();
}

if ($details = $objWalk->startDescription()) {
    $objTable->add_item("Start ");
    if ($button = $objWalk->startLocation()) {
        $details .= ' ' . $button;
    }
    $objTable->add_item($details);
    $objTable->generate_line();
}

if ($walks_follow) {

}

$details = $this->item->difficulty;
if (!$details == "") {
    $objTable->add_item("Difficulty ");
    $objTable->add_item($details);
    $objTable->generate_line();
}

$details = $this->item->grade_local;
if (!$details == "") {
    $objTable->add_item("Local Grade ");
    $objTable->add_item($details);
    $objTable->generate_line();
}
$details = $this->item->pace;
if (!$details == "") {
    $objTable->add_item("Pace ");
    $objTable->add_item($details);
    $objTable->generate_line();
}

$objTable->add_item("Distance ");
$details = $this->item->distance_miles . " miles, ";
$details .= $this->item->distance_km . " km";
$objTable->add_item($details);
$objTable->generate_line();

$details = $this->item->ascent_feet;
if ($details != "") {
    $objTable->add_item("Ascent ");
    $details .= " feet, ";
    $details .= $this->item->ascent_metres . " metres";
    $objTable->add_item($details);
    $objTable->generate_line();
}

$details = $this->item->finish_time;
if ($details != "") {
    $objTable->add_item("Finish time ");
    $objTable->add_item($details);
    $objTable->generate_line();
}


if ($walks_follow) {
    $objTable->generate_line();
}

$objTable->generate_table();
$canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_ra_walks.' . $this->item->id) || $this->item->checked_out == Factory::getApplication()->getIdentity()->id;
// Should use buttons: darkgreen for Edit, red for Delete
?>
<?php if ($canEdit && $this->item->checked_out == 0): ?>

    <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_ra_walks&task=walk.edit&id=' . $this->item->id); ?>"><?php echo Text::_("Edit"); ?></a>
<?php elseif ($canCheckin && $this->item->checked_out > 0) : ?>
    <a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_ra_walks&task=walk.checkin&id=' . $this->item->id . '&' . Session::getFormToken() . '=1'); ?>"><?php echo Text::_("JLIB_HTML_CHECKIN"); ?></a>

<?php endif; ?>

<?php if (Factory::getApplication()->getIdentity()->authorise('core.delete', 'com_ra_walks.walk.' . $this->item->id)) : ?>

    <a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
        <?php echo Text::_("Delete"); ?>
    </a>

    <?php
    echo HTMLHelper::_(
            'bootstrap.renderModal',
            'deleteModal',
            array(
                'title' => Text::_('Delete Walk'),
                'height' => '50%',
                'width' => '20%',
                'modalWidth' => '50',
                'bodyHeight' => '100',
                'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_ra_walks&task=walk.remove&id=' . $this->item->id, false, 2) . '" class="btn btn-danger">' . Text::_('Delete') . '</a>'
            ),
            Text::sprintf('Confirm', $this->item->id)
    );
    ?>

<?php endif; ?>
<?php
if ($this->callback == 'walks') {
    $back = 'index.php?option=com_ra_walks&view=walks';
} else {
//  the return destination may have been set up in user state
    $back = Factory::getApplication()->getUserState('com_ra_walks.callback_list', '');
}
echo $toolsHelper->backButton($back);
