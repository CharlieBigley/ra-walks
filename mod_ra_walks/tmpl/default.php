<?php

/**
 * @module	mod_ra_sidebar RA Walks Sidebar
 * @author	Charlie Bigley
 * version  0.0.2
 * @website	https://demo.stokeandnewcastleramblers.org.uk
 * @copyleft	Copyleft 2021 Charlie Bigley webmaster@stokeandnewcastleramblers.org.uk All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl.html GNU/GPL
 * 16/01/21 CB released
 */
// no direct access
defined("_JEXEC") or die("Restricted access");

$tools_params = JComponentHelper::getParams('com_ra_tools');
$group_type = $params->get('group_type');
$max = (int) $params->get('max');
$display_type = $params->get('display_type');
$header_tag = $params->get('header_tag');

//var_dump($tools_params);
if ($group_type == "single") {
    $group = $tools_params->get('default_group');
} else {
    $group = $tools_params->get('group_list');
}
//echo $display_type;
$feedurl = "http://www.ramblers.org.uk/api/lbs/walks?groups=" . $group;

if ($display_type == "Calendar") {
    $objFeed = new RJsonwalksFeed($feedurl . "&limit=" . $max);
    $events = new REventGroup();
    $events->addWalks($objFeed); // add walks to the group of events
    $objCalendar = new REventCalendar(250); // code to display the walks in a particular format, size: 250 or 400
    $objCalendar->setMonthFormat("Y M");    // optional format of Month/Year
    $objCalendar->Display($events);
} elseif ($display_type == "Nextwalks") {
    $objFeed = new RJsonwalksFeed($feedurl);
    $display = new RJsonwalksStdNextwalks();
    $display->displayGradesIcon = false;
    $objFeed->Display($display);  // display the information
} else {
    $display = new RJsonwalksStdWalkscount();

    // Could probably use a loop here
    echo '<' . $header_tag . '>' . "Monday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Monday");
    $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Tuesday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Tuesday");
    $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Wednesday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Wednesday");
    $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Thursday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Thursday");
    $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Friday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Friday");
    $message = $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Saturday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Saturday");
    $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Sunday Walks" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl . "&days=Sunday");
    $message = $objFeed->Display($display);

    echo '<' . $header_tag . '>' . "Walks in Total" . '</' . $header_tag . '>';
    $objFeed = new RJsonwalksFeed($feedurl);
    $objFeed->Display($display);
}
//echo "group=" . $group  . ", max=" . $max . "<br>";
//echo  "<br>";

