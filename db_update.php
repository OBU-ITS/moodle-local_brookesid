<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more settings.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brookes ID - db updates
 *
 * @package    local_brookesid
 * @copyright  2017, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
 
function get_achievements($date_from, $date_to) {
    global $DB;
	
	$time_to = $date_to + 86399; // 1 second before midnight

	$sql = 'SELECT u.idnumber AS studentnumber, u.firstname, u.lastname, u.email, bi.dateissued, b.name AS badge_name, b.description AS badge_description, c.idnumber AS activity_id, SUBSTRING(c.idnumber FROM 9 FOR 4) AS category
		FROM {badge_issued} bi 
		JOIN {badge} b ON b.id = bi.badgeid 
		JOIN {course} c ON c.id = b.courseid 
		JOIN {user} u ON u.id = bi.userid 
		WHERE c.idnumber LIKE "CCA~%" 
		AND bi.dateissued >= ? 
		AND bi.dateissued <= ? 
		ORDER BY studentnumber, category, bi.dateissued';
		

	return $DB->get_records_sql($sql, array($date_from, $time_to));
}

function get_enrolments($date_from, $date_to) {
	global $DB;
	
	$time_to = $date_to + 86399; // 1 second before midnight
	
	$sql = 'SELECT u.id as user_id, u.firstname, u.lastname, u.idnumber as student_number, c.idnumber AS course_id, c.shortname AS activity_name, c.fullname AS activity_full_name
	FROM {user} u
	JOIN {user_enrolments} ue ON ue.userid = u.id
	JOIN {enrol} e ON e.id = ue.enrolid
	JOIN {role_assignments} ra ON ra.userid = u.id
	JOIN {context} ct ON ct.id = ra.contextid
	AND ct.contextlevel =50
	JOIN {course} c ON c.id = ct.instanceid
	AND e.courseid = c.id
	JOIN {role} r ON r.id = ra.roleid
	AND r.shortname =  "student"
	WHERE c.idnumber LIKE "CCA%"
	AND ue.timecreated >= ?
	AND ue.timecreated <= ?
	order by u.lastname, u.firstname';

	return $DB->get_records_sql($sql, array($date_from, $time_to));
}


function get_activities() {
	global $DB;
			
	$sql = 'select c.fullname, c.shortname, c.visible, b.name as badgename, SUBSTRING(c.idnumber,5,3) as faculty, SUBSTRING(c.idnumber,9,4) as category
			from {course} c
			join {badge} b on b.courseid = c.id
			where c.idnumber like "CCA%"
			order by faculty, c.shortname';

	return $DB->get_records_sql($sql);
}
