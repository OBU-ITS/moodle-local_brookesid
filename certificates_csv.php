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
require_once('./certificates_form.php');

require_login();

$home = new moodle_url('/');
if (!is_brookesid_authorised()) {
	redirect($home);
}

$brookesid_course = get_brookesid_course();
require_login($brookesid_course);
$back = $home . 'course/view.php?id=' . $brookesid_course;

$context = context_system::instance();
$url = $home . 'local/brookesid/certificates_csv.php';

$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('certificates', 'local_brookesid') . ' (CSV)');
$PAGE->navbar->add(get_string('certificates', 'local_brookesid') . ' (CSV)');

$message = '';

$mform = new certificates_form(null, array());

if ($mform->is_cancelled()) {
    redirect($back);
} 
else if ($mform_data = $mform->get_data()) {
	$achievements = get_achievements($mform_data->date_from, $mform_data->date_to); // Get all selected achievements
	if (empty($achievements)) {
		$message = get_string('no_achievements', 'local_brookesid');
	} else {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename=certificates_' . date('d-m-Y', $mform_data->date_from) . '_' . date('d-m-Y', $mform_data->date_to) . '.csv');
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array('Student number', 'First name', 'Last name', 'Email', 'Conf', 'EntC', 'Conn', 'GenS', 'Category', 'Date issued', 'Achievement name', 'Achievement description'));

		$studentnumber = 0;
		$contact = array();
		$categories = array();
		$badges = array();

		foreach ($achievements as $achievement) {
			if ($achievement->studentnumber != $studentnumber) {
				output($fp, $studentnumber, $contact, $categories, $badges);
				$studentnumber = $achievement->studentnumber;
				$contact = array('firstname' => $achievement->firstname, 'lastname' => $achievement->lastname, 'email' => $achievement->email);
				$categories = array('confCount' => 0, 'entcCount' => 0, 'connCount' => 0, 'gensCount' => 0);
				$badges = array();
			}

			switch ($achievement->category) {
				case 'CONF':
					$categories['confCount'] += 1;
					break;
				case 'ENTC':
					$categories['entcCount'] += 1;
					break;
				case 'CONN':
					$categories['connCount'] += 1;
					break;
				case 'GENS':
					$categories['gensCount'] += 1;
					break;
				default:
					break;
			}

			$badges[] = array(
				'badge_category' => $achievement->category,
				'dateissued' => $achievement->dateissued,
				'badgename' => $achievement->badge_name,
				'badgedescription' => $achievement->badge_description
			);
		}
		output($fp, $studentnumber, $contact, $categories, $badges); // Clear the chamber
		
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

function output($fp, $studentnumber, $contact, $categories, $badges) {

	if ($studentnumber == 0) { // Wait for it...
		return;
	}
	
	foreach ($badges as $badge) {
		$fields = array();
		$fields['Student number'] = $studentnumber;
		$fields['First name'] = $contact['firstname'];
		$fields['Last name'] = $contact['lastname'];
		$fields['Email'] = $contact['email'];
		$fields['Conf'] = $categories['confCount'];
		$fields['EntC'] = $categories['entcCount'];
		$fields['Conn'] = $categories['connCount'];
		$fields['GenS'] = $categories['gensCount'];
		$fields['Category'] = $badge['badge_category'];
		$fields['Date issued'] = date('d-M-Y', $badge['dateissued']);
		$fields['Achievement name'] = $badge['badgename'];
		$fields['Achievement description'] = $badge['badgedescription'];
		fputcsv($fp, $fields);
		
	}
}
