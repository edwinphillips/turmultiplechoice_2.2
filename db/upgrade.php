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
 * Multiple choice question type upgrade code.
 *
 * @package    qtype
 * @subpackage turmultiplechoice
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the multiple choice question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_turmultiplechoice_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2009021801) {

        // Define field correctfeedbackformat to be added to question_turmultiplechoice
        $table = new xmldb_table('question_turmultiplechoice');
        $field = new xmldb_field('correctfeedbackformat', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'correctfeedback');

        // Conditionally launch add field correctfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field partiallycorrectfeedbackformat to be added to question_turmultiplechoice
        $field = new xmldb_field('partiallycorrectfeedbackformat', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'partiallycorrectfeedback');

        // Conditionally launch add field partiallycorrectfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field incorrectfeedbackformat to be added to question_turmultiplechoice
        $field = new xmldb_field('incorrectfeedbackformat', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'incorrectfeedback');

        // Conditionally launch add field incorrectfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // In the past, the correctfeedback, partiallycorrectfeedback,
        // incorrectfeedback columns were assumed to contain content of the same
        // form as questiontextformat. If we are using the HTML editor, then
        // convert FORMAT_MOODLE content to FORMAT_HTML.
        $sql = "SELECT qtm.*, q.oldquestiontextformat
                  FROM {question_turmultiplechoice} qtm
                  JOIN {question} q ON qtm.question = q.id";
        $rs = $DB->get_recordset_sql($sql);

        foreach ($rs as $record) {
            if ($CFG->texteditors !== 'textarea' &&
                    $record->oldquestiontextformat == FORMAT_MOODLE) {
                $record->correctfeedback = text_to_html(
                        $record->correctfeedback, false, false, true);
                $record->correctfeedbackformat = FORMAT_HTML;
                $record->partiallycorrectfeedback = text_to_html(
                        $record->partiallycorrectfeedback, false, false, true);
                $record->partiallycorrectfeedbackformat = FORMAT_HTML;
                $record->incorrectfeedback = text_to_html(
                        $record->incorrectfeedback, false, false, true);
                $record->incorrectfeedbackformat = FORMAT_HTML;
            } else {
                $record->correctfeedbackformat = $record->oldquestiontextformat;
                $record->partiallycorrectfeedbackformat = $record->oldquestiontextformat;
                $record->incorrectfeedbackformat = $record->oldquestiontextformat;
            }
            $DB->update_record('question_turmultiplechoice', $record);
        }

        $rs->close();

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2009021801, 'qtype', 'turmultiplechoice');
    }

    // Add new shownumcorrect field. If this is true, then when the user gets a
    // multiple-response question partially correct, tell them how many choices
    // they got correct alongside the feedback.
    if ($oldversion < 2011011200) {

        // Define field shownumcorrect to be added to question_turmultiplechoice
        $table = new xmldb_table('question_turmultiplechoice');
        $field = new xmldb_field('shownumcorrect', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'qdifficulty');

        // Launch add field shownumcorrect
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2011011200, 'qtype', 'turmultiplechoice');
    }

    // Moodle v2.1.0 release upgrade line
    // Put any upgrade step following this

    // Moodle v2.2.0 release upgrade line
    // Put any upgrade step following this

    // Add new autoplay field. If this is true, autoplay the audio files
    // on question load
    if ($oldversion < 2013010100) {

        // Define field autoplay to be added to question_turmultiplechoice
        $table = new xmldb_table('question_turmultiplechoice');
        $field = new xmldb_field('autoplay', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'shownumcorrect');

        // Launch add field autoplay
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2013010100, 'qtype', 'turmultiplechoice');
    }

    // Migrate assets
    if ($oldversion < 2013010101) {

        $audiofolder = $CFG->dataroot . '/' . $CFG->tursound . '/audio/';
        $imagefolder = $CFG->dataroot . '/' . $CFG->turimage . '/image/';
        $fs = get_file_storage();
        $file_record = array(
            'contextid' => 1,
            'component' => 'question',
            'filepath' => '/'
        );

        $sql = "SELECT q.id, qtm.questionsound
                  FROM {question} q
                  JOIN {question_turmultiplechoice} qtm ON qtm.question = q.id
                 WHERE q.qtype = ?";
        $params = array('turmultiplechoice');
        $questions = $DB->get_records_sql($sql, $params);

        foreach ($questions as $question) {

            $file_record['itemid'] = $question->id;

            $filename = substr($question->questionsound, 6);
            $file_record['filearea'] = 'questionsound';
            $file_record['filename'] = $filename;
            $file_record['timecreated'] = time();
            $file_record['timemodified'] = time();
            $fs->create_file_from_pathname($file_record, $audiofolder . $filename);
        }

        $sql = "SELECT qa.id, qa.answersound, qa.feedbacksound
                  FROM {question_answers} qa
                  JOIN {question} q ON q.id = qa.question
                 WHERE q.qtype = ?";
        $params = array('turmultiplechoice');
        $answers = $DB->get_records_sql($sql, $params);

        foreach ($answers as $answer) {

            $file_record['itemid'] = $answer->id;

            $filename = substr($answer->answersound, 6);
            $file_record['filearea'] = 'answersound';
            $file_record['filename'] = $filename;
            $file_record['timecreated'] = time();
            $file_record['timemodified'] = time();
            $fs->create_file_from_pathname($file_record, $audiofolder . $filename);

            $filename = substr($answer->feedbacksound, 6);
            $file_record['filearea'] = 'feedbacksound';
            $file_record['filename'] = $filename;
            $file_record['timecreated'] = time();
            $file_record['timemodified'] = time();
            $fs->create_file_from_pathname($file_record, $audiofolder . $filename);
        }

        // TODO: Drop the mutant columns added for the 1.9 version

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2013010101, 'qtype', 'turmultiplechoice');
    }

    if ($oldversion < 2013010102) {

        // Define field answersound to be dropped from question_answers.
        $table = new xmldb_table('question_answers');
        $field = new xmldb_field('answersound');

        // Conditionally launch drop field answersound.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field feedbacksound to be dropped from question_answers.
        $field = new xmldb_field('feedbacksound');

        // Conditionally launch drop field feedbacksound.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2013010102, 'qtype', 'turmultiplechoice');
    }

    // Moodle v2.3.0 release upgrade line
    // Put any upgrade step following this.

    // Moodle v2.4.0 release upgrade line
    // Put any upgrade step following this.

    // Moodle v2.5.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2013092300) {

        // Find duplicate rows before they break the 2013092304 step below.
        $sql = "SELECT question, MIN(id) AS recordidtokeep
                  FROM {question_turmultichoice}
              GROUP BY question
                HAVING COUNT(1) > 1";
        $problemids = $DB->get_recordset_sql($sql);

        foreach ($problemids as $problem) {
            $DB->delete_records_select(
                    'question_turmultichoice',
                    'question = ? AND id > ?',
                    array(
                        $problem->question,
                        $problem->recordidtokeep
                    )
                );
        }
        $problemids->close();

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2013092300, 'qtype', 'turmultiplechoice');
    }

    if ($oldversion < 2013092301) {

        // Define table question_turmultichoice to be renamed to qtype_turmultiplechoice_options.
        $table = new xmldb_table('question_turmultichoice');

        // Launch rename table for question_turmultichoice.
        $dbman->rename_table($table, 'qtype_turmultiplechoice_options');

        // turmultiplechoice savepoint reached
        upgrade_plugin_savepoint(true, 2013092301, 'qtype', 'turmultiplechoice');
    }

    if ($oldversion < 2013092302) {

        // Define key question (foreign) to be dropped form qtype_turmultiplechoice_options
        $table = new xmldb_table('qtype_turmultiplechoice_options');
        $key = new xmldb_key('question', XMLDB_KEY_FOREIGN, array('question'), 'question', array('id'));

        // Launch drop key question.
        $dbman->drop_key($table, $key);

        // Record that qtype_match savepoint was reached.
        upgrade_plugin_savepoint(true, 2013092302, 'qtype', 'turmultiplechoice');
    }

    if ($oldversion < 2013092303) {

        // Rename field question on table qtype_turmultiplechoice_options to questionid.
        $table = new xmldb_table('qtype_turmultiplechoice_options');
        $field = new xmldb_field('question', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

        // Launch rename field question.
        $dbman->rename_field($table, $field, 'questionid');

        // Record that qtype_match savepoint was reached.
        upgrade_plugin_savepoint(true, 2013092303, 'qtype', 'turmultiplechoice');
    }

    if ($oldversion < 2013092304) {

        // Define key questionid (foreign-unique) to be added to qtype_multichoice_options.
        $table = new xmldb_table('qtype_turmultiplechoice_options');
        $key = new xmldb_key('questionid', XMLDB_KEY_FOREIGN_UNIQUE, array('questionid'), 'question', array('id'));

        // Launch add key questionid.
        $dbman->add_key($table, $key);

        // Record that qtype_match savepoint was reached.
        upgrade_plugin_savepoint(true, 2013092304, 'qtype', 'turmultiplechoice');
    }

    if ($oldversion < 2013092305) {

        // Define field answers to be dropped from qtype_multichoice_options.
        $table = new xmldb_table('qtype_turmultiplechoice_options');
        $field = new xmldb_field('answers');

        // Conditionally launch drop field answers.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Record that qtype_match savepoint was reached.
        upgrade_plugin_savepoint(true, 2013092305, 'qtype', 'turmultiplechoice');
    }

    // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.7.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
