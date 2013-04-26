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

// Because it exists (must).
require_once($CFG->dirroot . '/mod/dualpane/backup/moodle2/backup_dualpane_stepslib.php');
// Because it exists (optional).
require_once($CFG->dirroot . '/mod/dualpane/backup/moodle2/backup_dualpane_settingslib.php');

/**
 * choice backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_dualpane_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step.
        $this->add_step(new backup_dualpane_activity_structure_step('dualpane_structure',
                'dualpane.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of dualpanes.
        $search="/(".$base."\/mod\/dualpane\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DUALPANEINDEX*$2@$', $content);

        // Link to choice view by moduleid.
        $search="/(".$base."\/mod\/dualpane\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@DUALPANEVIEWBYID*$2@$', $content);

        return $content;
    }
}
