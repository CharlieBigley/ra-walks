<?php

/*
 * @version    1.1.0
 * @component  com_ra_walks
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 19/02/25 CB copied from com_ra_tools
 */
defined('_JEXEC') or die;

use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsTable;

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers.css', 'com_ta_tools/css/ramblers.css');

$heading = 'Ramblers Areas ';
if ($this->nation != '') {
    $heading .= 'for ' . $this->nation;
} else {
    if ($this->cluster != '') {
        $heading .= 'for Cluster ';
        switch ($this->cluster) {
            case 'ME';
                $heading .= 'Midlands and East';
                break;
            case 'SE';
                $heading .= 'South East';
                break;
            case 'SSW';
                $heading .= 'South and South West';
                break;
            case 'N';
                $heading .= 'North';
                break;
            default;
                $heading .= 'Not known';
        }
    }
}
echo '<h2>' . $heading . '</h2>';
$toolsHelper = new ToolsHelper;

$objTable = new ToolsTable();
$objTable->add_header("Code,Name,Chair");
$count = 0;
foreach ($this->items as $i => $item) {
    $count++;
    $objTable->add_item($item->code);
    $objTable->add_item($item->name);
    $objTable->add_item($item->chair);
    $objTable->generate_line();
}
$objTable->generate_table();
//
if ($this->nation != '') {
    echo 'Number of Areas = ' . $count;
} else {
    if ($this->cluster != '') {
        echo 'Number of Areas = ' . $count;
    }
}

$target = 'index.php?option=com_ra_tools&task=clusters.show';
echo $toolsHelper->backButton($target);
