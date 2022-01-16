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
 * The main renderer for mod_gabigame
 *
 * @package    mod_gabigame
 * @copyright  2022 Gabija Matijaškaitė <gabija.matijaskaite@mif.stud.vu.lt>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The main renderer for mod_gabigame
 *
 * @package    mod_gabigame
 * @copyright  2022 Gabija Matijaškaitė <gabija.matijaskaite@mif.stud.vu.lt>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_gabigame_renderer extends plugin_renderer_base {
    /**
     * Initialises the game and returns its HTML code
     *
     * @param stdClass $gabigame The gabigame to be added
     * @param context $context The context
     * @return string The HTML code of the game
     */
    public function render_game($gabigame, $context) {
        global $DB;

        $categoryid = explode(',', $gabigame->questioncategory)[0];
        $questionids = array_keys($DB->get_records('question', array('category' => intval($categoryid)), '', 'id'));
        $questions = question_load_questions($questionids);

        $this->page->requires->strings_for_js(array(
                'score',
                'emptyquiz',
                'endofgame',
                'spacetostart'
            ), 'mod_gabigame');

        $qjson = [];
        foreach ($questions as $question) {
            if ($question->qtype == "multichoice" || $question->qtype == "truefalse") {
                $questiontext = gabigame_cleanup($question->questiontext);
                $answers = [];
                foreach ($question->options->answers as $answer) {
                    $answertext = gabigame_cleanup($answer->answer);
                    $answers[] = ["text" => $answertext, "fraction" => $answer->fraction];
                }

                // The "single" entry is used by multichoice to determine single or multi answer.
                if ($question->qtype == "truefalse") {
                    $qjson[] = ["question" => $questiontext, "answers" => $answers, "type" => $question->qtype];
                } else {
                    $qjson[] = ["question" => $questiontext, "answers" => $answers, "type" => $question->qtype,
                        "single" => $question->qtype == "multichoice" && $question->options->single == 1];
                }
            }
            if ($question->qtype == "match") {
                $subquestions = [];
                foreach ($question->options->subquestions as $subquestion) {
                    $questiontext = gabigame_cleanup($subquestion->questiontext);
                    $answertext = gabigame_cleanup($subquestion->answertext);
                    $subquestions[] = ["question" => $questiontext, "answer" => $answertext];
                }
                $qjson[] = ["question" => get_string("match", "quiz"), "stems" => $subquestions, "type" => $question->qtype];
            }
        }

        $this->page->requires->js_call_amd('mod_gabigame/gabigame', 'init', array($qjson, $gabigame->id));

        $display = '<canvas id="mod_gabigame_game"></canvas>';
        $display .= '<div id="button_container">';
        $display .= '<input id="mod_gabigame_fullscreen_button" class= "btn btn-secondary" type="button" value="' .
                    get_string('fullscreen', 'mod_gabigame') . '">';										  
        $display .= '</div>';

        return $display;
    }
}
