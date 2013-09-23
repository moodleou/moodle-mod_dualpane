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

function dualpane_add_instance($dualpane) {
    global $DB;

    if (!isset($dualpane->enablehttpswarnings)) {
        $dualpane->enablehttpswarnings = 0;
    }
    $dualpane->id = $DB->insert_record("dualpane", $dualpane);
    $step = 1;

    foreach ($dualpane->option as $key => $value) {
        $value = trim($value);
        if (!empty($value) || !empty($dualpane->html[($step-1)])) {
            $emptystep = dualpane_get_empty_step();
            $stepid = $DB->insert_record("dualpane_steps", $emptystep);
            $option = dualpane_process_step($step, $value, $dualpane, $stepid);
            $DB->update_record("dualpane_steps", $option);
        }
        $step++;
    }

    return $dualpane->id;
}

function dualpane_update_instance($dualpane) {
    global $DB;

    // Update, delete or insert answers.
    $step = 1;
    foreach ($dualpane->option as $key => $value) {
        if (!isset($dualpane->optionid[$key]) || empty($dualpane->optionid[$key])) {
            $emptystep = dualpane_get_empty_step();
            $dualpane->optionid[$key] = $DB->insert_record("dualpane_steps", $emptystep);
        }

        $option = dualpane_process_step($step, $value, $dualpane,  $dualpane->optionid[$key]);

        if (!empty($value) || !empty($option->xhtml)) {
            $DB->update_record("dualpane_steps", $option);
        } else { // Empty old option - needs to be deleted.
            $DB->delete_records("dualpane_steps", array("id"=>$option->id));
        }

        $step++;
    }

    $dualpane->id = $dualpane->instance;
    if (!isset($dualpane->enablehttpswarnings)) {
        $dualpane->enablehttpswarnings = 0;
    }
    return $DB->update_record('dualpane', $dualpane);
}

function dualpane_process_step($stepno, $value, $dualpane, $id) {
    $cmid = $dualpane->coursemodule;
    $context = context_module::instance($cmid);
    $value = trim($value);
    $option = new stdClass();
    $option->id = $id;
    if (empty($dualpane->id)) {
        $option->dualpaneid = $dualpane->instance;
    } else {
        $option->dualpaneid = $dualpane->id;
    }
    $option->stepnum = $stepno;
    $option->title = $value;
    $option->xhtml = file_save_draft_area_files($dualpane->html[($stepno-1)]['itemid'],
                                                $context->id,
                                                'mod_dualpane',
                                                'dualpane_steps',
                                                $id,
                                                null,
                                                $dualpane->html[($stepno-1)]['text']);
    return $option;
}

function dualpane_get_empty_step() {
    $option = new stdClass();
    $option->dualpaneid = -1;
    $option->stepnum = -1;
    $option->title = 'empty step';
    $option->xhtml = 'empty step';
    return $option;
}

function dualpane_delete_instance($id) {
    global $DB;
    if (! $dualpane = $DB->get_record("dualpane", array('id'=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("dualpane_steps", array('dualpaneid'=>$dualpane->id))) {
        $result = false;
    }

    if (! $DB->delete_records("dualpane", array('id'=>$id))) {
        $result = false;
    }

    return $result;
}

/**
 * List of view style log actions
 * @return array
 */
function dualpane_get_view_actions() {
    return array('view');
}

/**
 * List of update style log actions
 * @return array
 */
function dualpane_get_post_actions() {
    return array('update', 'add');
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $resource
 */
function dualpane_user_complete($course, $user, $mod, $dualpane) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'dualpane',
                                              'action'=>'view', 'info'=>$dualpane->id),
                                              'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'resource');
    }
}

/**
 * Indicates API features that the forum supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function dualpane_supports($feature) {
    switch ($feature) {
        case FEATURE_IDNUMBER:                return true;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        default: return null;
    }
}

/**
 * Returns all other caps used in module
 */
function dualpane_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

function dualpane_save_array($rss) {
    foreach (array_shift($rss->array) as $key => $feed) {
        if ($key == 'channel') {
            $dualpane = new stdClass;
            $dualpane->name = $feed['title'];
            $dualpane->starturl = $feed['item'][0]['link'];
            $dualpane->steps = array();
            $i = 0;
            foreach ($feed['item'] as $stepkey => $step) {
                if ($stepkey == 0) {
                    continue;
                }
                $dualpane->steps[$i]->id = $i;
                $dualpane->steps[$i]->title = $step['title'];
                $pattern[0] = "/\<a (.*) href=\"(.*)\"(.*)\>(.*)<\/a>/";
                $replace[0] = "[$2 $4]";
                $pattern[1] = "/\<a href=\"(.*)\"\>(.*)<\/a>/";
                $replace[1] = "[$1 $2]";
                $description = preg_replace($pattern, $replace, $step['description']);
                $dualpane->steps[$i]->xhtml = $description;
                $i++;
            }
            return $dualpane;
        }
    }
}

function dualpane_count_items($rss, $instance) {
    foreach (array_shift($rss->array) as $key => $feed) {
        if ($key == 'channel') {
            return count($feed['item']);
        }
    }

    return 0;
}
/**
 * Serves associated files
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return mixed
 */
function dualpane_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    $postid = (int)array_shift($args);

    if (!$dualpane= $DB->get_record('dualpane', array('id'=>$cm->instance))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_dualpane/$filearea/$postid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true); // download MUST be forced - security!
    exit;
}

/**
 * File browsing support for dualpane.
 * @param object $browser
 * @param object $areas
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance Representing an actual file or folder (null if not found
 * or cannot access)
 */
function dualpane_get_file_info($browser, $areas, $course, $cm, $dpcontext, $filearea,
        $itemid, $filepath, $filename) {
    global $CFG, $DB, $dataplusfilehelper, $dataplus, $context;

    $context = context_module::instance($cm->id);

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }

    if ($filearea != 'dualpane_steps') {
        return null;
    }
    if (! $dualpane = $DB->get_record("dualpane", array("id"=>$cm->instance))) {
        print_error("Course module is incorrect");
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;

    if (!($storedfile = $fs->get_file($context->id, 'mod_dualpane', $filearea, $itemid,
            $filepath, $filename))) {
        return null;
    }

    $urlbase = $CFG->wwwroot . '/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $filearea,
            $itemid, true, true, false);
}