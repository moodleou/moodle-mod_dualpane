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
 * Help block is a block for adding a help link which points to the
 * moodle docs website using the same link creation as the footer moodle
 * docs link, but with a different link title, it also hides the blocks
 * header.
 *
 * @package module
 * @subpackage dualpane
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */

/**
 * Define the complete choice structure for backup, with file and id annotations
 */
class backup_dualpane_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated.
        $dualpane = new backup_nested_element('dualpane', array('id'), array(
            'name', 'intro', 'introformat', 'course', 'starturl', 'backbutton', 'stepsformat'));

        $steps = new backup_nested_element('steps');

        $step = new backup_nested_element('step', array('id'), array(
            'dualpaneid', 'stepnum', 'title', 'xhtml'));

        // Build the tree.
        $dualpane->add_child($steps);
        $steps->add_child($step);

        // Define sources.
        $dualpane->set_source_table('dualpane', array('id' => backup::VAR_ACTIVITYID));

        $step->set_source_sql('
            SELECT *
              FROM {dualpane_steps}
             WHERE dualpaneid = ?
          ORDER BY id',
            array(backup::VAR_PARENTID));

        // Define file annotations.
        $dualpane->annotate_files('mod_dualpane', 'intro', null); // This file area hasn't itemid.
        $step->annotate_files('mod_dualpane', 'dualpane_steps', 'id');
        // Return the root element (choice), wrapped into standard activity structure.
        return $this->prepare_activity_structure($dualpane);
    }
}
