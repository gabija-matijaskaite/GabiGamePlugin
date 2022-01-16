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
 * Prints a particular instance of gabigame
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_gabigame
 * @copyright  2022 Gabija Matijaškaitė <gabija.matijaskaite@mif.stud.vu.lt>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
//require_once($CFG->dirroot . '/mod/gabigame/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Either course_module ID, or ...
$n  = optional_param('q', 0, PARAM_INT);  // ...gabigame instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('gabigame', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $gabigame  = $DB->get_record('gabigame', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $gabigame  = $DB->get_record('gabigame', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $gabigame->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('gabigame', $gabigame->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception('invalidcmorid', 'gabigame');
}

$cm = cm_info::create($cm);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Print the page header.

$PAGE->set_url('/mod/gabigame/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($gabigame->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_focuscontrol('mod_gabigame_game');
$renderer = $PAGE->get_renderer('mod_gabigame');

// Output starts here.
echo $OUTPUT->header();

if ($gabigame->intro) {
    echo $OUTPUT->box(format_module_intro('gabigame', $gabigame, $cm->id), 'generalbox mod_introbox', 'gabigameintro');
}

// Output header and directions.
echo $OUTPUT->heading_with_help(get_string('modulename', 'mod_gabigame'), 'howtoplay', 'mod_gabigame');

// Game here.
echo "<iframe width=800 height=600 src='game/index.html' name='targetframe' allowTransparency='true' scrolling='yes' frameborder='100' ></iframe>";

// Finish the page.
echo $OUTPUT->footer();
