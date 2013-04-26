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
 * @package mod
 * @subpackage dualpane
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_dualpane_upgrade($oldversion=0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012081001) {
        $table = new xmldb_table('dualpane');
        $field = new xmldb_field('backbutton');
        $type = XMLDB_TYPE_INTEGER;
        $field->set_attributes($type, '10', null, null, null, '2', "starturl");

        $dbman->add_field($table, $field);
    }

    if ($oldversion < 2012102304) {
        // Update any links in single square brackets, replacing with dual square brackets.
        if ($steps = $DB->get_records('dualpane_steps')) {
            foreach ($steps as $step) {
                // Make sure we do not over do it with the double brackets.
                if (strpos($step->xhtml, '[[') === false) {
                    $pattern[0] = "/\[([^\s\]]*)\]/";
                    $replace[0] = "[[$1]]";
                    $pattern[1] = "/\[([^\s\]]*)\s([^\]]*)\]/";
                    $replace[1] = "[[$1 $2]]";
                    $step->xhtml = preg_replace($pattern, $replace, $step->xhtml);
                    $DB->update_record('dualpane_steps', $step);
                }
            }
        }
    }

    if ($oldversion < 2012122004) {
        // Define field to hold steps format.
        $table = new xmldb_table('dualpane');
        $field = new xmldb_field('stepsformat');
        $type = XMLDB_TYPE_INTEGER;
        $field->set_attributes($type, '10', null, true, null, '0', "backbutton");
        $dbman->add_field($table, $field);
    }

    if ($oldversion < 2013042400) {
        // Define field to hold steps format.
        $table = new xmldb_table('dualpane');
        $field = new xmldb_field('enablehttpswarnings');
        $type = XMLDB_TYPE_INTEGER;
        $field->set_attributes($type, '10', null, true, null, '1', 'stepsformat');
        $dbman->add_field($table, $field);
    }

    return true;
}
