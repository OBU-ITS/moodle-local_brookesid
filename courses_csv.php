<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brookes ID - Certificates
 *
 * @package    local_brookesid
 * @copyright  2017, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once('../../config.php');
require_once('./db_update.php');
require_once('./courses_form.php');


require_login();
$context = context_system::instance();
require_capability('local/brookesid:admin', $context);

$home = new moodle_url('/');
$url = $home . 'local/brookesid/index.php';


$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('courses', 'local_brookesid'));

$message = '';

$mform = new courses_form(null, array());

if ($mform->is_cancelled()) {
    redirect($home);
} 
else if ($mform_data = $mform->get_data()) {
	$activities = get_activities(); // Get all selected achievements
	//$activities = get_activities();
	if (empty($activities)) {
		$message = get_string('no_activities', 'local_brookesid');
	} else {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename=activities.csv');
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array('faculty', 'fullname', 'shortname', 'visible', 'badgename', 'category'));

		foreach ($activities as $activity) {
		
			//faculty, c.fullname, c.shortname, c.visible, b.name as badgename, category
			$fields = array();
			$fields[0] = $activity->faculty;
			$fields[1] = $activity->fullname;
			$fields[2] = $activity->shortname;
			$fields[3] = $activity->visible;
			$fields[4] = $activity->badgename;
			$fields[5] = $activity->category;
			
			fputcsv($fp, $fields);
		
		}
		
		fclose($fp);
		
		exit();
	}
}	 

echo $OUTPUT->header();
echo '<img src="images/brookesid-logo.png" width="120" alt="BrookesID" style="float: right"/>';

if ($message) {
    notice($message, $url);    
}
else {
    $mform->display();
}

echo $OUTPUT->footer();

exit();
?>

	
