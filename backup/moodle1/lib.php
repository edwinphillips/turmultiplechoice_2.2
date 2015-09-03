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
 * @package    qtype
 * @subpackage turmultiplechoice
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Multichoice question type conversion handler
 */
class moodle1_qtype_turmultiplechoice_handler extends moodle1_qtype_handler {

    /**
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'TURMULTIPLECHOICE',
        );
    }

    /**
     * Appends the turmultiplechoice specific information to the question
     */
    public function process_question(array $data, array $raw) {

        // convert and write the answers first
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // convert and write the turmultiplechoice
        if (!isset($data['turmultiplechoice'])) {
            // This should never happen, but it can do if the 1.9 site contained
            // corrupt data/
            $data['turmultiplechoice'] = array(array(
                'single'                         => 1,
                'shuffleanswers'                 => 1,
                'correctfeedback'                => '',
                'correctfeedbackformat'          => FORMAT_HTML,
                'partiallycorrectfeedback'       => '',
                'partiallycorrectfeedbackformat' => FORMAT_HTML,
                'incorrectfeedback'              => '',
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'qdifficulty'                    => '0',
            ));
        }
        $this->write_turmultiplechoice($data['turmultiplechoice'], $data['oldquestiontextformat']);
    }

    /**
     * Converts the turmultiplechoice info and writes it into the question.xml
     *
     * @param array $turmultiplechoices the grouped structure
     * @param int $oldquestiontextformat - {@see moodle1_question_bank_handler::process_question()}
     */
    protected function write_turmultiplechoice(array $turmultiplechoices, $oldquestiontextformat) {
        global $CFG;

        // the grouped array is supposed to have just one element - let us use foreach anyway
        // just to be sure we do not loose anything
        foreach ($turmultiplechoices as $turmultiplechoice) {
            // append an artificial 'id' attribute (is not included in moodle.xml)
            $turmultiplechoice['id'] = $this->converter->get_nextid();

            // replay the upgrade step 2009021801
            $turmultiplechoice['correctfeedbackformat']               = 0;
            $turmultiplechoice['partiallycorrectfeedbackformat']      = 0;
            $turmultiplechoice['incorrectfeedbackformat']             = 0;

            if ($CFG->texteditors !== 'textarea' and $oldquestiontextformat == FORMAT_MOODLE) {
                $turmultiplechoice['correctfeedback']                 = text_to_html($turmultiplechoice['correctfeedback'], false, false, true);
                $turmultiplechoice['correctfeedbackformat']           = FORMAT_HTML;
                $turmultiplechoice['partiallycorrectfeedback']        = text_to_html($turmultiplechoice['partiallycorrectfeedback'], false, false, true);
                $turmultiplechoice['partiallycorrectfeedbackformat']  = FORMAT_HTML;
                $turmultiplechoice['incorrectfeedback']               = text_to_html($turmultiplechoice['incorrectfeedback'], false, false, true);
                $turmultiplechoice['incorrectfeedbackformat']         = FORMAT_HTML;
            } else {
                $turmultiplechoice['correctfeedbackformat']           = $oldquestiontextformat;
                $turmultiplechoice['partiallycorrectfeedbackformat']  = $oldquestiontextformat;
                $turmultiplechoice['incorrectfeedbackformat']         = $oldquestiontextformat;
            }

            $this->write_xml('turmultiplechoice', $turmultiplechoice, array('/turmultiplechoice/id'));
        }
    }
}
