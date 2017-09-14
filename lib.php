<?php

// This file is part of Moodle - http://moodle.org/
//
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
 * Brookes ID - Provide left hand navigation links
 *
 * @package    local_brookesid
 * @copyright  2017, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_brookesid_extend_navigation($navigation) {
	
	if (!isloggedin() || isguestuser() || !has_capability('local/brookesid:admin', context_system::instance())) {
		return;
	}
	
	// Find the 'brookesid' node
	$nodeParent = $navigation->find(get_string('brookesid', 'local_brookesid'), navigation_node::TYPE_SYSTEM);
	
	// If necessary, add the 'brookesid' node to 'home'
	if (!$nodeParent) {
		$nodeHome = $navigation->children->get('1')->parent;
		if ($nodeHome) {
			$nodeParent = $nodeHome->add(get_string('brookesid', 'local_brookesid'), null, navigation_node::TYPE_SYSTEM);
		}
	}
	
	$node = $nodeParent->add(get_string('certificates_pdf', 'local_brookesid'), '/local/brookesid/certificates.php');
	$node = $nodeParent->add(get_string('enrolled', 'local_brookesid'), '/local/brookesid/enrolled_csv.php');
	$node = $nodeParent->add(get_string('courses', 'local_brookesid'), '/local/brookesid/courses_csv.php');
	$node = $nodeParent->add(get_string('certificates_csv', 'local_brookesid'), '/local/brookesid/certificates_csv.php');
}
