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
 * @copyright  2019, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once('../../config.php');
require_once('./locallib.php');
require_once('./enrolled_form.php');

require_login();

$home = new moodle_url('/');
if (!is_brookesid_authorised()) {
	redirect($home);
}

$brookesid_course = get_brookesid_course();
require_login($brookesid_course);
$back = $home . 'course/view.php?id=' . $brookesid_course;

$context = context_system::instance();
$url = $home . 'local/brookesid/enrolled_csv.php';

$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('enrolled', 'local_brookesid'));
$PAGE->navbar->add(get_string('enrolled', 'local_brookesid'));

$message = '';

$mform = new enrolled_form(null, array());

if ($mform->is_cancelled()) {
    redirect($back);
} 
else if ($mform_data = $mform->get_data()) {
	$enrolments = get_enrolments($mform_data->date_from, $mform_data->date_to); // Get all selected achievements
	if (empty($enrolments)) {
		$message = get_string('no_enrolments', 'local_brookesid');
	} else {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename=enrolments_' . date('d-m-Y', $mform_data->date_from) . '_' . date('d-m-Y', $mform_data->date_to) . '.csv');
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array('user id', 'firstname', 'lastname', 'student number', 'course id', 'activity name', 'activity full name'));

		foreach ($enrolments as $enrolment) {
		
			//user_id, u.firstname, u.lastname, u.idnumber as student_number, c.idnumber AS course_id, c.shortname AS activity_name, c.fullname AS activity_full_name
			$fields = array();
			$fields[0] = $enrolment->user_id;
			$fields[1] = $enrolment->firstname;
			$fields[2] = $enrolment->lastname;
			$fields[3] = $enrolment->student_number;
			$fields[4] = $enrolment->course_id;
			$fields[5] = $enrolment->activity_name;
			$fields[6] = $enrolment->activity_full_name;
			
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
