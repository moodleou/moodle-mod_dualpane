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

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/dualpane/locallib.php');
require_once($CFG->dirroot.'/mod/dualpane/lib.php');

class mod_dualpane_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB;

        $mform = &$this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('dualpanename', 'dualpane'),
                array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'starturl', get_string('starturl', 'dualpane'),
                array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('starturl', PARAM_TEXT);
        } else {
            $mform->setType('starturl', PARAM_CLEANHTML);
        }
        $mform->addRule('starturl', null, 'required', null, 'client');
        $mform->addHelpButton('starturl', 'starturl', 'dualpane');

        $this->add_intro_editor(true, get_string('intro', 'dualpane'));

        $mform->addElement('header', 'general', get_string('information', 'dualpane'));
        $mform->addElement('static', 'description', get_string('addlinks', 'dualpane'),
                get_string('addlinksdesc', 'dualpane'));
        $mform->addElement('static', 'description', get_string('restrictedsites', 'dualpane'),
                get_string('restrictedsitestext', 'dualpane'));

        if (mod_dualpane_check_https()) {
            $mform->addElement('static', 'httpsformwarning', get_string('https', 'dualpane'),
                    get_string('httpsformwarning', 'dualpane'));
        }

        $buttons = array(0 => get_string('none', 'dualpane'),
                         1 => get_string('backtocourse', 'dualpane'),
                         2 => get_string('backtolastscreen', 'dualpane'));

        $mform->addElement('select', 'backbutton', get_string('backbutton', 'dualpane'), $buttons);
        $mform->setDefault('backbutton', 2);
        $mform->addHelpButton('backbutton', 'backbutton', 'dualpane');

        $screens = array(0 => get_string('allononescreen', 'dualpane'),
                1 => get_string('multiplescreens', 'dualpane'));
        $mform->addElement('select', 'stepsformat', get_string('stepsformat', 'dualpane'), $screens);
        $mform->setDefault('stepsformat', 0);
        $mform->addHelpButton('stepsformat', 'stepsformat', 'dualpane');

        $mform->addElement('checkbox', 'enablehttpswarnings', get_string('enablehttpswarnings', 'dualpane'));
        $mform->addHelpButton('enablehttpswarnings', 'enablehttpswarnings', 'dualpane');
        $mform->setDefault('enablehttpswarnings', 1);

        $repeatarray = array();
        $repeatarray[] = &$mform->createElement('header', '',
                get_string('step', 'dualpane').' {no}');
        $repeatarray[] = &$mform->createElement('text', 'option',
                get_string('step', 'dualpane'));
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext'=>true, 'context'=>$this->context);
        $repeatarray[] = $mform->createElement('editor', 'html', get_string('text', 'dualpane'),
                array('rows'=>'15', 'cols'=>'50'), $editoroptions);
        $mform->setType('html', PARAM_RAW);

        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        $repeatno = 3;

        if ($this->_instance) {
            $repeatno = $DB->count_records('dualpane_steps',
                    array('dualpaneid'=>$this->_instance));
            $repeatno += 2;
        } else if (isset($_POST['rssfeed'])) {
            $xml = file_get_contents($_POST['rss']);
            $parsed = new mod_dualpane_xml_to_array($xml);
            $stepcount = dualpane_count_items($parsed, $this->_instance);
            if ($stepcount > 0) {
                $repeatno = $stepcount+2;
            }
        }

        $repeateloptions = array();

        $repeateloptions['option']['helpbutton'] = array('steps', 'dualpane');
        $mform->setType('option', PARAM_CLEAN);

        $mform->setType('optionid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3);
        if (isset($_GET['add'])) {
            $mform->addElement('html', '<a href="'.$CFG->wwwroot.'/mod/dualpane/upload.php">'.
                    get_string('loadrss', 'dualpane').'</a>');
        }

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$default_values) {
        global $DB;

        if (isset($_POST['rssfeed'])) {
            $xml = file_get_contents($_POST['rss']);
            $parsed = new mod_dualpane_xml_to_array($xml);
            $dualpane = dualpane_save_array($parsed);
            $steps = $dualpane->steps;
            $default_values['name'] = $dualpane->name;
            $default_values['starturl'] = $dualpane->starturl;

            foreach ($steps as $key => $step) {
                $default_values['option['.$key.']'] = $step->title;
                $default_values['html['.$key.']'] = array('text'=>$step->xhtml);
                $default_values['optionid['.$key.']'] = $step->id;
            }
        } else if (!empty($this->_instance) && ($steps = $DB->get_records('dualpane_steps',
                array('dualpaneid'=>$this->_instance), 'id'))) {
            $steps = array_values($steps);
            $id = required_param('update', PARAM_INT);
            $cm = get_coursemodule_from_id('dualpane', $id);
            $context = context_module::instance($cm->id);
            foreach ($steps as $key => $step) {
                $draftitemid = 0;
                $html = file_prepare_draft_area($draftitemid,
                                                $context->id,
                                                'mod_dualpane',
                                                'dualpane_steps',
                                                $step->id,
                                                null,
                                                $step->xhtml);

                $default_values['option['.$key.']'] = $step->title;
                $default_values['html['.$key.']'] = array('text'=>$html);
                $default_values['html['.$key.']']['itemid'] = $draftitemid;
                $default_values['optionid['.$key.']'] = $step->id;
            }
        }
    }
}