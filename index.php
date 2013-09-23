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


require_once("../../config.php");
require_once("locallib.php");

$id = required_param('id', PARAM_INT);

if (! $course = $DB->get_record('course', array('id'=>$id))) {
    print_error('coursemisconf');
}

$url = new moodle_url('/mod/dualpane/index.php', array('id' => $id));
$PAGE->set_url($url);

// Support for OU shared activities system, if installed.
$grabindex=$CFG->dirroot.'/course/format/sharedactv/grabindex.php';
if (file_exists($grabindex)) {
    require_once($grabindex);
}

require_course_login($course);

$PAGE->set_pagelayout('incourse');

add_to_log($course->id, "dualpane", "view all", "index.php?id=$course->id", "");


$strweek = get_string('week');
$strtopic = get_string('topic');
$strname = get_string('name');
$strdata = get_string('modulename', 'dualpane');
$strdataplural  = get_string('modulenameplural', 'dualpane');

$PAGE->navbar->add($strdata, new moodle_url('/mod/dualpane/index.php', array('id'=>$course->id)));
$PAGE->set_title($strdata);
echo $OUTPUT->header();

// Print the list of dualpanes.
if (!$dualpanes = get_all_instances_in_course('dualpane', $course)) {
    $strthereareno = get_string('thereareno', 'moodle', $strdataplural);
    notice($strthereareno, "$CFG->wwwroot/course/view.php?id=$course->id");
}

// Get the post count.
$sql = "SELECT D.id, COUNT(DS.id) as postcount
        FROM {dualpane} D
        INNER JOIN {dualpane_steps} DS ON (DS.dualpaneid = D.id)
        WHERE D.course = ?
        GROUP BY D.id ";
$counts = $DB->get_records_sql($sql, array($course->id));

$usesections = course_format_uses_sections($course->format);

if ($usesections) {
    $modinfo = get_fast_modinfo($course);
    $sections = $modinfo->get_section_info_all();
}

$timenow  = time();
$strname  = get_string('name');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strdescription = get_string('blogsummary', 'oublog');
$strentries = get_string('steps', 'dualpane');
$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strentries);
    $table->align = array ('center', 'left', 'center');
} else {
    $table->head  = array ($strname, $strentries);
    $table->align = array ('left', 'center');
}

$currentsection = '';

foreach ($dualpanes as $dualpane) {

    $printsection = '';

    if ($usesections) {
        if ($dualpane->section !== $currentsection) {
            if ($dualpane->section) {
                $printsection = get_section_name($course, $sections[$dualpane->section]);
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $dualpane->section;
        }
    }

    // Calculate the href.
    $name = format_string($dualpane->name, true);
    if (!$dualpane->visible) {
        // Show dimmed if the mod is hidden.
        $link = "<a class=\"dimmed\" href=\"view.php?id=$dualpane->coursemodule\">".$name."</a>";
    } else {
        // Show normal if the mod is visible.
        $link = "<a href=\"view.php?id=$dualpane->coursemodule\">".$name."</a>";
    }

    $numsteps = isset($counts[$dualpane->id]) ? $counts[$dualpane->id]->postcount : 0;

    if ($usesections) {
        $row = array ($printsection, $link, $numsteps);
    } else {
        $row = array ($link, $count);
    }

    $table->data[] = $row;
}

echo "<br />";
echo html_writer::table($table);
echo $OUTPUT->footer();