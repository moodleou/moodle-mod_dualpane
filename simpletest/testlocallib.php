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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

// Make sure the code being tested is accessible.
require_once($CFG->dirroot . '/mod/dualpane/locallib.php'); // Include the code to test.
require_once($CFG->dirroot . '/mod/dualpane/lib.php'); // Include the code to test.

/** This class contains the test cases for the functions in locallib.php. */
class dualpane_locallib_test extends UnitTestCaseUsingDatabase {

    public $dualpane;

    public static $includecoverage = array('mod/dualpane/locallib.php');
    public $tables = array('lib' => array(
                                'course_sections',
                                'course',
                                'modules',
                                'course_modules'
                                ),
                                'mod/dualpane' => array(
                                'dualpane',
                                'dualpane_steps')
                          );

    public $modules = array();

    /**
     * Create temporary test tables and entries in the database for these tests.
     * These tests have to work on a brand new site.
     */
    public function setUp() {
        global $CFG;

        parent::setup();

        // All operations until end of test method will happen in test DB.
        $this->switch_to_test_db();

        foreach ($this->tables as $dir => $tables) {
            $this->create_test_tables($tables, $dir); // Create tables
            foreach ($tables as $table) { // Fill them if load_xxx method is available.
                $function = "load_$table";
                if (method_exists($this, $function)) {
                    $this->$function();
                }
            }
        }
    }

    public function tearDown() {
        parent::tearDown(); // All the test tables created in setUp will be dropped by this.
    }

    public function load_modules() {
        $module = new stdClass();
        $module->name = 'dualpane';
        $module->id = $this->testdb->insert_record('modules', $module);
        $this->modules[] = $module;
    }

    /*
     Unit tests cover:
         * Adding a Dualpane instance
         * Updating a Dualpane Instance
         * Getting steps from a dualpane

     Unit tests do NOT cover:
         * Delete a Dualpane
    */

    // Tests for adding dualpane.
    public function test_dualpane_add_instance() {

        $course = $this->get_new_course();
        $dualpane = $this->get_new_dualpane($course->id);
        $options = $this->get_new_option();
        $dualpane->option = $options;
        $html = $this->get_new_html();
        $dualpane->html = $html;

        $this->dualpane = dualpane_add_instance($dualpane);
        $dualpane->id = $this->dualpane;
        $this->assertIsA($this->dualpane, 'integer');

        // Add the course module records.
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $dualpane->id, $coursesection->id);
        $dualpane->instance = $cm->instance;

        // Test updating.
        $dualpane->name = 'Test Update';
        $this->assertTrue(dualpane_update_instance($dualpane));
    }

    public function test_dualpane_update_instance() {

        $course = $this->get_new_course();
        $dualpane = $this->get_new_dualpane($course->id);
        $options = $this->get_new_option();
        $dualpane->option = $options;
        $html = $this->get_new_html();
        $dualpane->html = $html;
        $dualpane->id = $this->dualpane;

        // Add the course module records.
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $dualpane->id, $coursesection->id);
        $dualpane->instance = $cm->instance;

        // Test updating.
        $dualpane->name = 'Test Update';
        $this->assertTrue(dualpane_update_instance($dualpane));
    }

    public function test_get_course() {
        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $this->dualpane, $coursesection->id);

        $fakedp = $this->get_new_dualpane($course->id);
        $fakedp->id = $this->dualpane;

        $dualpane = new mod_dualpane($fakedp);

        $this->assertIsA($dualpane->get_course(), 'integer');
    }

    public function test_get_name() {
        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $this->dualpane, $coursesection->id);

        $fakedp = $this->get_new_dualpane($course->id);
        $fakedp->id = $this->dualpane;

        $dualpane = new mod_dualpane($fakedp);

        $this->assertIsA($dualpane->get_name(), 'string');
    }

    public function test_get_start_url() {
        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $this->dualpane, $coursesection->id);

        $fakedp = $this->get_new_dualpane($course->id);
        $fakedp->id = $this->dualpane;

        $dualpane = new mod_dualpane($fakedp);

        $this->assertIsA($dualpane->get_start_url(), 'string');
    }

    public function test_get_steps($convertlinks = true) {
        $course = $this->get_new_course();
        $coursesection = $this->get_new_course_section($course->id);
        $cm = $this->get_new_course_module($course->id, $this->dualpane, $coursesection->id);

        $fakedp = $this->get_new_dualpane($course->id);
        $fakedp->id = $this->dualpane;
        $this->get_new_steps($this->dualpane);

        $dualpane = new mod_dualpane($fakedp);

        $steps = $dualpane->get_steps();
        $stepid = 1;

        $this->assertEqual($steps->$stepid->stepnum, 1);
        $this->assertEqual($steps->$stepid->title, 'Step 1');
        $this->assertEqual($steps->$stepid->xhtml, '<p>Step Instructions Here</p>');
    }

    public function get_new_course() {
        $course = new stdClass();
        $course->category = 1;
        $course->fullname = 'Anonymous test course';
        $course->shortname = 'ANON';
        $course->summary = '';
        $course->modinfo = null;
        $course->id = $this->testdb->insert_record('course', $course);
        return $course;
    }

    public function get_new_course_section($courseid, $sectionid=1) {
        $section = new stdClass();
        $section->course = $courseid;
        $section->section = $sectionid;
        $section->name = 'Test Section';
        $section->id = $this->testdb->insert_record('course_sections', $section);
        return $section;
    }

    public function get_new_course_module($courseid, $dualpaneid, $section, $groupmode=0) {
        $cm = new stdClass();
        $cm->course = $courseid;
        $cm->module = $this->modules[0]->id;
        $cm->instance = $dualpaneid;
        $cm->section = $section;
        $cm->groupmode = $groupmode;
        $cm->groupingid = 0;
        $cm->id = $this->testdb->insert_record('course_modules', $cm);
        return $cm;
    }

    public function get_new_dualpane($courseid) {
        $dualpane = new stdClass();
        $dualpane->name = 'Test Pane';
        $dualpane->intro = '';
        $dualpane->introformat = 1;
        $dualpane->course = $courseid;
        $dualpane->starturl = 'http://www.open.ac.uk';
        return $dualpane;
    }

    public function get_new_steps($dualpaneid) {
        $steps = new stdClass();
        $steps->dualpaneid = $dualpaneid;
        $steps->stepnum = 1;
        $steps->title = 'Step 1';
        $steps->xhtml = '<p>Step Instructions Here</p>';
        $steps->id = $this->testdb->insert_record('dualpane_steps', $steps);
        return $steps;
    }

    public function get_new_option() {
        $option = array();
        $option[] = 'Step 1';
        return $option;
    }

    public function get_new_html() {
        $html = array();
        $html[] = '<p>Step Instructions Here</p>';
        return $html;
    }
}
