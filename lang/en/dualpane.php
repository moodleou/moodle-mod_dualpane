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

$string['pluginname'] = 'Dual Pane';
$string['pluginadministration'] = 'Dual Pane administration';
$string['modulename'] = 'Dual Pane';
$string['modulenameplural'] = 'Dual Panes';
$string['dualpane'] = 'Dual Pane';
$string['dualpanename'] = 'Dual Pane name';
$string['dualpane:addinstance'] = 'Add a new Dual Pane';
$string['starturl'] = 'Start URL';
$string['starturl_help'] = 'Specify the URL of the first webpage you want the user to see in the right pane.  ';
$string['step'] = 'Step';
$string['text'] = 'Text';
$string['back'] = 'Back';
$string['backto'] = 'Back to {$a}';
$string['backbutton'] = 'Back button';
$string['backbutton_help'] = 'Back button type. None = no back button. Back to course = Back to the course study planner or subpage in which this appears. Back to last screen = Returns to the last screen the user looked at.';
$string['none'] = 'None';
$string['backtocourse'] = 'Back to course';
$string['backtolastscreen'] = 'Back to last screen';
$string['information'] = 'Information';
$string['intro'] = 'Introduction';
$string['steps'] = 'Steps';
$string['steps_help'] = 'Here is where you specify the title and instructions of each step participants are to follow.

You can fill in any number of these. If you leave some of the options blank, they will not be displayed. If you need more than 3 options, click the "Add 3 fields to form" button.';
$string['addlinks'] = 'Adding links';
$string['addlinksdesc'] = 'Within step text, to add links that make the right pane change, please use the format [[URL]] or [[URL Title]].  For example, for a link to www.bbc.co.uk you could use either [[www.bbc.co.uk]] or [[www.bbc.co.uk Click Here]].';
$string['to'] = 'To';
$string['startpage'] = 'Return to the start';
$string['restrictedsites'] = 'Restricted sites';
$string['restrictedsitestext'] = 'Some websites, such as Yahoo and Google, impose restrictions that stop them being displayed with other websites.  Consequently, these cannot be used in Dual Pane.';
$string['previous'] = 'Previous step';
$string['next'] = 'Next step';
$string['stepsformat'] = 'Steps format';
$string['stepsformat_help'] = 'Allows all steps to be shown on one screen or displayed across different screens with previous and next buttons.';
$string['allononescreen'] = 'All on one screen';
$string['multiplescreens'] = 'Multiple screens';
/*
 * Upload Strings
 */
$string['loadrss'] = 'Migrate from Moodle 1.9';
$string['clickhere'] = 'Click here';
$string['uploadrss'] = 'Upload RSS document';
$string['uploadrss_rss'] = 'URL for RSS feed';
$string['uploadrss_save'] = 'Convert';

$string['neverseen'] = 'Never seen';
/*
 * HTTPS
 */
$string['https'] = 'HTTPS';
$string['httpsformwarning'] = 'Please note that this Moodle instance uses HTTPS.  If you include a link to a site that does not use HTTPS, users may see a warning message in some browsers.';
$string['httpsstepwarning'] = 'Please note that when you click on links marked with an asterisk you may see a security warning.  This is because our site uses HTTPS security and the site being displayed may not need to do so.';
$string['enablehttpswarnings'] = 'Enable HTTPS warnings';
$string['enablehttpswarnings_help'] = 'Show HTTPS warnings if this site use HTTPS and the exercise contains links to sites that don\'t.  Shows the user a warning to expect possible security warnings.';