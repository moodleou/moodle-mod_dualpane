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

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/dualpane/locallib.php');
require_once($CFG->dirroot.'/lib/resourcelib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$pagename = optional_param('page', '', PARAM_TEXT);

$url = new moodle_url('/mod/dualpane/view.php', array('id' => $id, 'page' => $pagename));
$PAGE->set_url($url);

/*
 * Get the course module from the id given
 */
if (! $cm = get_coursemodule_from_id('dualpane', $id)) {
    print_error('invalidcoursemodule');
}
/*
 * Get the course from the course module
 */
if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}
/*
 * Get the course context from the cm id
 */
if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
    print_error('badcontext');
}

require_course_login($course->id, true, $cm);

/*
 * Initialise dualpane
 */
$dualpane = mod_dualpane::get_from_cmid($cm);
/*
 * Set required moodle vars
 */
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($dualpane->get_name()));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('base');
// Update completion state
// Mark as viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);
if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC) {
    $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
}
add_to_log($course->id, 'dualpane', 'view', '', '', $cm->id);
/*
 * Set up the renderer ready for use
 */
$renderer = $PAGE->get_renderer('mod_dualpane');
/*
 * Output standard Header
 */
echo $renderer->header();
/*
 * Output the dualpane to teh screen using the renderer
 */
echo $renderer->dualpane_header($dualpane);
echo $renderer->dualpane_leftpane($dualpane, $dualpane->get_steps_format());
echo $renderer->dualpane_rightpane($dualpane);
echo $renderer->dualpane_footer();
/*
 * Setup YUI3 for use on the page
 */
$PAGE->requires->js_init_call('M.dualpane.init', array(''), true, mod_dualpane_get_js_module());
if ($dualpane->get_steps_format() == 1) {
    $PAGE->requires->js_init_call('M.dualpanestepsscreen.init', null, true, mod_dualpane_get_js_module());
}
/*
 * Out put standard Footer
 */
echo $OUTPUT->footer();