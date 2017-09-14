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
 * Version info
 *
 * @package    local_brookesid
 * @copyright  2017, Oxford Brookes University {@link http://www.brookes.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2017091401;

$plugin->requires = 2012120301;//Optional - minimum version number of Moodle that this plugin requires
//(Moodle 1.9 = 2007101509; Moodle 2.0 = 2010112400; Moodle 2.1 = 2011070100; Moodle 2.2 = 2011120100; Moodle 2.4 = 2012120301)

$plugin->component = 'local_brookesid'; // Full name of the plugin (used for diagnostics): plugintype_pluginname

$plugin->maturity = MATURITY_STABLE;//Optional - how stable the plugin is:
//MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE (Moodle 2.0 and above)

$plugin->release = 'v1.0.8'; //Optional - Human-readable version name
?>