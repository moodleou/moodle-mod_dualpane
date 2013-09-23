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

class mod_dualpane{

    private $_id;
    private $_cmid;
    private $_course;
    private $_name;
    private $_starturl;
    private $_backbutton;
    private $_stepsformat;
    private $_enablehttpswarnings;

    /**
     * Initialises and Returns the dualpane object
     * @param object $cm
     * @return object
     */
    static public function get_from_cmid($cm) {
        global $DB;

        if (!$dualpane = $DB->get_record('dualpane', array('id'=>$cm->instance))) {
            print_error('invalidcoursemodule');
        }
        return new mod_dualpane((object)$dualpane);
    }

    /**
     * Setup the dualpane variables ready for use
     * @param object $cm
     * @param object  $dualpane
     */
    public function __construct($dualpane, $cmid = null) {
        $this->_id = $dualpane->id;
        if (empty($cmid)) {
            $this->_cmid = required_param('id', PARAM_INT);
        } else {
            $this->_cmid = $cmid;
        }
        $this->_course = $dualpane->course;
        $this->_name = $dualpane->name;
        $this->_starturl = $dualpane->starturl;
        $this->_backbutton = $dualpane->backbutton;
        $this->_stepsformat = $dualpane->stepsformat;
        $this->_enablehttpswarnings = $dualpane->enablehttpswarnings;
    }

    /**
     * Return the dualpane id
     * @return int
     */
    public function get_id() {
        return $this->_id;
    }

    /**
     * Return the Course ID
     * @return int
     */
    public function get_course() {
        return $this->_course;
    }

    /**
     * Return the dualpane Name
     * @return string
     */
    public function get_name() {
        return $this->_name;
    }

    /**
     * Parse and Return the start url
     * @return string
     */
    public function get_start_url() {
        $url = $this->_starturl;
        $url = $this->addhttp($url);
        return $url;
    }

    /**
     * Return the dualpane back button setting
     * @return int
     */
    public function get_back_button() {
        return $this->_backbutton;
    }

    /**
     * Return the dualpane steps format setting
     * @return int
     */
    public function get_steps_format() {
        return $this->_stepsformat;
    }

    /**
     * Return the dualpane https warning setting
     * @return int
     */
    public function get_enable_https_warnings() {
        return $this->_enablehttpswarnings;
    }

    /**
     * Work out what the back url and title should be.
     * @return array
     */
    public function get_back_details() {
        global $COURSE;

        $modinfo = get_fast_modinfo($COURSE);
        $cm = $modinfo->get_cm($this->_cmid);
        $section = $cm->sectionnum;

        $back['title'] = '';
        $back['url'] = '';

        if ($section >= 100) {
            foreach ($modinfo->get_instances_of('subpage') as $subpageid => $module) {
                // Get sectionsids array stored in the customdata.
                $cmdata = $module->get_custom_data();
                if (!$cmdata) {
                    $cmdata = (object)array('sectionids' => array(),
                            'sectionstealth' => array());
                }
                foreach ($cmdata->sectionids as $sectionid) {
                    if ($sectionid == $cm->section) {
                        $subpage = $module;
                        break;
                    }
                }
                if (isset($subpage)) {
                    break;
                }
            }
            if (isset($subpage)) {
                $back['title'] = get_string('back', 'dualpane', $subpage->name);
                $back['url'] = new moodle_url('/mod/subpage/view.php',
                                                array('id' => $subpage->id));
            }
        } else {
            $back['title'] = get_string('back', 'dualpane', $COURSE->shortname);
            $back['url'] = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        }

        return $back;
    }

    /**
     * Reads the steps from the database using a regex to parse all urls then returns the step
     * for a dualpane
     * @param bool $convertlinks
     * @return object
     */
    public function get_steps($convertlinks = true) {
        global $DB;

        $id = required_param('id', PARAM_INT);
        $cm = get_coursemodule_from_id('dualpane', $id);
        $context = context_module::instance($cm->id);

        $steps = $DB->get_records('dualpane_steps',
                array('dualpaneid'=>$this->get_id()), 'stepnum', 'id, stepnum, title, xhtml');

        $stepobj = new stdClass;
        $stepno = 1;
        foreach ($steps as $step) {
            $html = file_rewrite_pluginfile_urls($step->xhtml,
                                                 'pluginfile.php',
                                                 $context->id,
                                                 'mod_dualpane',
                                                 'dualpane_steps',
                                                 $step->id);
            $html = format_text($html, FORMAT_MOODLE);
            $stepid = $step->id;
            if ($convertlinks) {
                $pattern[0] = "/\[\[(http[s]{0,3}:\/\/)?([^\s\]]*)\]\]/";
                $replace[0] = "<a href=\"$1$2\" onclick=\"return false;\"
                        class=\"dualpane_rightpane_link\">$2</a>";
                $pattern[1] = "/\[\[(http[s]{0,3}:\/\/)?([^\s\]]*)\s([^\]]*)\]\]/";
                $replace[1] = "<a href=\"$1$2\" onclick=\"return false;\"
                        class=\"dualpane_rightpane_link\">$3</a>";
                $html = preg_replace($pattern, $replace, $html);
                // Lets check for any www without a protocol and assume it's http.
                $html = str_replace('href="www', 'href="http://www', $html);
            }
            $stepobj->$stepid = new stdClass();
            $stepobj->$stepid->stepnum = $step->stepnum;
            $stepobj->$stepid->title = $step->title;
            $stepobj->$stepid->xhtml = $html;
            $stepno++;
        }

        return $stepobj;
    }

    /**
     * Using a regex checks is http:// is before a url if not adds it and returns the url.
     * @param string $url
     * @return string
     */
    private function addhttp($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }
}

/**
 * Convert an xml file to an associative array (including the tag attributes):
 *
 * @param Str $xml file/string.
 */
class mod_dualpane_xml_to_array {
    /**
     * The array created by the parser which can be assigned to a variable with:
     * $varArr = $domObj->array.
     *
     * @var Array
     */
    public $array;
    private $parser;
    private $pointer;

    /**
     * $domObj = new xmlToArrayParser($xml);
     *
     * @param Str $xml file/string
     */
    public function __construct($xml) {
        $this->pointer =& $this->array;
        $this->parser = xml_parser_create("UTF-8");
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");
        xml_parse($this->parser, ltrim($xml));
    }

    private function tag_open($parser, $tag, $attributes) {
        $this->convert_to_array($tag, '_');
        $idx=$this->convert_to_array($tag, 'cdata');
        if (isset($idx)) {
            $this->pointer[$tag][$idx] = Array('@idx' => $idx, '@parent' => &$this->pointer);
            $this->pointer =& $this->pointer[$tag][$idx];
        } else {
            $this->pointer[$tag] = Array('@parent' => &$this->pointer);
            $this->pointer =& $this->pointer[$tag];
        }
        if (!empty($attributes)) {
            $this->pointer['_'] = $attributes;
        }
    }

    /**
     * Adds the current elements content to the current pointer[cdata] array.
     */
    private function cdata($parser, $cdata) {
        if (isset($this->pointer['cdata'])) {
            $this->pointer['cdata'] .= $cdata;
        } else {
            $this->pointer['cdata'] = $cdata;
        }
    }

    private function tag_close($parser, $tag) {
        $current = & $this->pointer;
        if (isset($this->pointer['@idx'])) {
            unset($current['@idx']);
        }
        $this->pointer = & $this->pointer['@parent'];
        unset($current['@parent']);
        if (isset($current['cdata']) && count($current) == 1) {
            $current = $current['cdata'];
        } else if (empty($current['cdata'])) {
            unset($current['cdata']);
        }
    }

    /**
     * Converts a single element item into array(element[0]) if a second element of the same name
     * is encountered.
     */
    private function convert_to_array($tag, $item) {
        if (isset($this->pointer[$tag][$item])) {
            $content = $this->pointer[$tag];
            $this->pointer[$tag] = array((0) => $content);
            $idx = 1;
        } else if (isset($this->pointer[$tag])) {
            $idx = count($this->pointer[$tag]);
            if (!isset($this->pointer[$tag][0])) {
                foreach ($this->pointer[$tag] as $key => $value) {
                    unset($this->pointer[$tag][$key]);
                    $this->pointer[$tag][0][$key] = $value;
                }
            }
        } else {
            $idx = null;
        }
        return $idx;
    }
}

/**
 * Initialises YUI3 libraries
 * @return array
 */
function mod_dualpane_get_js_module() {
    global $PAGE;
    return array(
        'name' => 'dualpane',
        'fullpath' => '/mod/dualpane/module.js',
        'requires' => array('base', 'dom', 'io', 'node', 'json',
        'node-event-simulate')
    );
}

/**
 * Checks to see if Moodle is hosted on a platform using HTTPS.
 */
function mod_dualpane_check_https() {
    global $CFG;
    if (strpos($CFG->wwwroot, 'https') !== false) {
        return true;
    }
    return false;
}