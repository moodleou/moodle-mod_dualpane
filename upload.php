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
require_once($CFG->dirroot.'/mod/dualpane/lib.php');

$url = new moodle_url('/mod/dualpane/upload.php');
$PAGE->set_url($url);

$pagetitle = get_string('uploadrss', 'dualpane');
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname . ' - ' . $pagetitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url($FULLME);
/*
 * Set up the renderer ready for use
 */
$renderer = $PAGE->get_renderer('mod_dualpane');
/*
 * Output standard Header
 */
echo $OUTPUT->header();
/*
 *  Output upload page
 */
echo $renderer->output_rss_form();
/*
 * Out put standard Footer
 */
echo $OUTPUT->footer();