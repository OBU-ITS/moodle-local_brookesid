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


require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$home = new moodle_url('/');
$url = $home . 'local/brookesid/index.php';

$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('brookesid:admin', 'local_brookesid'));

	
echo $OUTPUT->header();

echo '<img src="images/brookesid-logo.png" width="120" alt="BrookesID" style="float: right"/>
<h2><strong>Brookes<span style="color: #d10373">ID</span></strong> reports and certificates</h2>
<h3>Certificates (PDF)</h3>
 <ul>
 	<li><a href="certificates.php">Generate certificates</a></li>
 </ul>
 <h3>Reports (CSV)</h3>
 <ul>	
 	<li><a href="enrolled_csv.php">List of students enrolled on activities</a></li>
 	<li><a href="courses_csv.php">List of activities and associated achievements</a></li>
 	<li><a href="certificates_csv.php">Certificates report</a></li>
 </ul>
';

echo $OUTPUT->footer();

exit();

?>