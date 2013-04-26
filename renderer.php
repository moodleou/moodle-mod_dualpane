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
class mod_dualpane_renderer extends plugin_renderer_base {
    public function header() {
        global $OUTPUT;
        $headdata = $OUTPUT->header();
        $pattern[0] = "/^\<!DOCTYPE html PUBLIC(.*)\>/";
        $replace[0] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
                \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
        $headdata = preg_replace($pattern, $replace, $headdata);
        return $headdata;
    }
    /**
     * Returns HTML of all step in a dualpane
     * @param object $dualpane
     * @param bool $returnarray
     * @return string
     */
    public function print_steps($dualpane, $hide = false) {
        $content = '';

        $steps = $dualpane->get_steps();

        $count = 0;
        foreach ($steps as $step) {
            $html = '';
            if ($hide) {
                $html .= '<div id="dualpane_step_'.$count.'" class="dualpane_step_hidden">';
            }
            $html .= '<h3>'.$step->title.'</h3>';
            if (!strpos($step->xhtml, 'text_to_html')) {
                $html .= '<p>'.$step->xhtml.'</p>';
            } else {
                $html .= $step->xhtml;
            }
            if ($hide) {
                $html .= '</div>';
            }
            $content .= $html;
            $count++;
        }
        // Sort out links to http if using HTTPS.
        if ($dualpane->get_enable_https_warnings() && mod_dualpane_check_https()) {
            $pattern = "/(<a href=\"http:\/\/[^<]*<\/a>)/";
            $replace = "sdsd d $1*";
            $content = preg_replace($pattern, $replace, $content);
        }
        return $content;
    }

    /**
     * Returns HTML of the modules header
     * @param object $dualpane
     * @return string
     */
    public function dualpane_header($dualpane) {
        $content = html_writer::start_tag('div', array('class'=>'dualpane_wrap'));
        return $content;
    }

    /**
     * Returns HTML of the left pane including the steps
     * @param object $dualpane
     * @return string
     */
    public function dualpane_leftpane($dualpane, $format) {
        $content = html_writer::tag('h2', $dualpane->get_name(), array('class'=>'main'));
        $content .= $this->dualpane_back_button($dualpane);
        $content .= html_writer::empty_tag('br');
        $content .= html_writer::start_tag('p');
        $content .= html_writer::tag('a', get_string('startpage', 'dualpane'),
                array('href'=>$dualpane->get_start_url(), 'title'=>$dualpane->get_start_url(),
                'class'=>'dualpane_rightpane_link dualpane_startlink', 'onclick'=>'return false;'));
        $content .= html_writer::end_tag('p');
        if ($format == 1) {
            $content .= $this->separate_steps_screens($dualpane);
        } else {
            $content .= $this->print_steps($dualpane);
        }

        $content .= html_writer::end_tag('div');
        if ($dualpane->get_enable_https_warnings() && strpos($content, '</a>*')) {
            $strwarning = get_string('httpsstepwarning', 'dualpane');
            $content = html_writer::tag('div', $strwarning, array('id' => 'dualpane_securitywarning')) . $content;
        }
        $content = html_writer::start_tag('div', array('class'=>'dualpane_left')) . $content;
        return $content;
    }

    /**
     * Returns the HTML and JS for having buttons to move between steps.
     * @param object $dualpane
     * @return string
     */
    public function separate_steps_screens($dualpane) {
        $content = $this->print_steps($dualpane, true);
        $content .= html_writer::start_tag('div', array('class'=>'dualpane_currentstep'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('ul', array('class'=>'dualpane_step_buttons'));
        $content .= html_writer::start_tag('li');
        $content .= html_writer::tag('a', get_string('previous', 'dualpane'),
                array('href'=>'javascript: void(0)', 'id'=>'dualpane_backsteplink'));
        $content .= html_writer::tag('span', get_string('previous', 'dualpane'),
                array('id'=>'dualpane_backsteplink_grey'));
        $content .= html_writer::end_tag('li');
        $content .= html_writer::start_tag('li');
        $content .= html_writer::tag('a', get_string('next', 'dualpane'),
                array('href'=>'javascript: void(0)', 'id'=>'dualpane_nextsteplink'));
        $content .= html_writer::tag('span', get_string('next', 'dualpane'),
                array('id'=>'dualpane_nextsteplink_grey'));
        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');
        return $content;
    }
    /**
     * Returns the back button
     * @param object $dualpane
     * @return string
     */
    public function dualpane_back_button($dualpane) {
        $content = '';
        $backbutton = $dualpane->get_back_button();
        if ($backbutton == 0) {
            return $content;
        }

        if ($backbutton == 1) {
            $backdets = $dualpane->get_back_details();
            if (empty($backdets['title'])) {
                return $content;
            }
            $action = $backdets['url'];
            $title = $backdets['title'];
        } else if ($backbutton == 2) {
            if (!isset($_SERVER['HTTP_REFERER'])) {
                return $content;
            }
            $action = $_SERVER['HTTP_REFERER'];
            $title = get_string('back', 'dualpane');
        }

        $content .= html_writer::start_tag('form', array('method'=>'post',
            'action'=>$action));
        $content .= html_writer::empty_tag('input', array('type'=>'submit',
            'value'=>$title, 'class'=>'backbutton'));
        $content .= html_writer::end_tag('form');

        return $content;
    }
    /**
     * Returns HTML of the right pane initilising with the start url
     * @param object $dualpane
     * @return string
     */
    public function dualpane_rightpane($dualpane) {
        $starturl = $dualpane->get_start_url();
        $content = '';
        $content .= html_writer::start_tag('div', array('class'=>'dualpane_right', 'id'=>'right'));
        $attrs = array('type'=>'hidden', 'id'=>'starturl', 'value'=>$starturl);
        $content .= html_writer::empty_tag('input', $attrs);
        $content .= resourcelib_embed_general($starturl, 'rightpane', '', 'text/html');
        $content .= html_writer::end_tag('div');
        return $content;
    }

    /**
     * Returns HTML ofg the module footer
     * @return string
     */
    public function dualpane_footer() {
        $content = html_writer::end_tag('div');
        return $content;
    }

    public function output_rss_form() {
        $content = '';
        $content = html_writer::tag('h2', get_string('loadrss', 'dualpane'));
        $content .= html_writer::start_tag('form', array('action'=>$_SERVER['HTTP_REFERER'],
               'method'=>'post'));
        $content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'rssfeed',
                'id'=>'reefeed', 'value'=>'true'));
        $content .= html_writer::start_tag('div');
        $content .= html_writer::start_tag('div', array('class'=>'field'));
        $content .= html_writer::tag('label', get_string('uploadrss_rss', 'dualpane'),
               array('for'=>'rss'));
        $content .= html_writer::empty_tag('input', array('type'=>'text', 'size'=>'50',
                'name'=>'rss', 'id'=>'rss'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class'=>'button'));
        $content .= html_writer::empty_tag('input', array('type'=>'submit',
                'value'=>get_string('uploadrss_save', 'dualpane')));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('form');
        return $content;
    }
}