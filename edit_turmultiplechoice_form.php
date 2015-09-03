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
 * Defines the editing form for the multiple choice question type.
 *
 * @package    qtype
 * @subpackage turmultiplechoice
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Multiple choice editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_turmultiplechoice_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {

        $mform->addElement('advcheckbox', 'autoplay',
                get_string('autoplay', 'qtype_turmultiplechoice'), null, null, array(0, 1));
        $mform->addHelpButton('autoplay', 'autoplay', 'qtype_turmultiplechoice');
        $mform->setDefault('autoplay', 0);

        $menu = array(
            get_string('answersingleno', 'qtype_turmultiplechoice'),
            get_string('answersingleyes', 'qtype_turmultiplechoice'),
        );
        $mform->addElement('select', 'single',
                get_string('answerhowmany', 'qtype_turmultiplechoice'), $menu);
        $mform->setDefault('single', 0);

        $question_difficulties = array();
        $question_difficulties[0] = get_string('q_easy1', 'qtype_turmultiplechoice');
        $question_difficulties[1] = get_string('q_easy2', 'qtype_turmultiplechoice');
        $question_difficulties[2] = get_string('q_easy3', 'qtype_turmultiplechoice');
        $question_difficulties[3] = get_string('q_medium1', 'qtype_turmultiplechoice');
        $question_difficulties[4] = get_string('q_medium2', 'qtype_turmultiplechoice');
        $question_difficulties[5] = get_string('q_medium3', 'qtype_turmultiplechoice');
        $question_difficulties[6] = get_string('q_hard1', 'qtype_turmultiplechoice');
        $question_difficulties[7] = get_string('q_hard2', 'qtype_turmultiplechoice');
        $question_difficulties[8] = get_string('q_hard3', 'qtype_turmultiplechoice');
        $mform->addElement('select', 'qdifficulty',
                get_string('qdifficulty', 'qtype_turmultiplechoice'), $question_difficulties);
        $mform->setDefault('qdifficulty', 0);

        $mform->addElement('hidden', 'shuffleanswers', 1);

        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_turmultiplechoice', '{no}'),
                question_bank::fraction_options_full(), max(4, QUESTION_NUMANS_START));

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        $this->add_interactive_settings(true, true);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('header', 'answerhdr', $label);
        $repeated[] = $mform->createElement('editor', 'answer',
                get_string('answer', 'question'), array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('select', 'fraction',
                get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'feedback',
                get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->qdifficulty = $question->options->qdifficulty;
        }

        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $answercount = 0;

        $totalfraction = 0;
        $maxfraction = -1;

        foreach ($answers as $key => $answer) {
            //check no of choices
            $trimmedanswer = trim($answer['text']);
            $fraction = (float) $data['fraction'][$key];
            if ($trimmedanswer === '' && empty($fraction)) {
                continue;
            }
            if ($trimmedanswer === '') {
                $errors['fraction['.$key.']'] = get_string('errgradesetanswerblank', 'qtype_turmultiplechoice');
            }

            $answercount++;

            //check grades
            if ($data['fraction'][$key] > 0) {
                $totalfraction += $data['fraction'][$key];
            }
            if ($data['fraction'][$key] > $maxfraction) {
                $maxfraction = $data['fraction'][$key];
            }
        }

        if ($answercount == 0) {
            $errors['answer[0]'] = get_string('notenoughanswers', 'qtype_turmultiplechoice', 2);
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_turmultiplechoice', 2);
        } else if ($answercount == 1) {
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_turmultiplechoice', 2);

        }

        /// Perform sanity checks on fractional grades
        if ($data['single']) {
            if ($maxfraction != 1) {
                $errors['fraction[0]'] = get_string('errfractionsnomax', 'qtype_turmultiplechoice',
                        $maxfraction * 100);
            }
        } else {
            $totalfraction = round($totalfraction, 2);
            if ($totalfraction != 1) {
                $errors['fraction[0]'] = get_string('errfractionsaddwrong', 'qtype_turmultiplechoice',
                        $totalfraction * 100);
            }
        }
        return $errors;
    }

    public function qtype() {
        return 'turmultiplechoice';
    }
}
