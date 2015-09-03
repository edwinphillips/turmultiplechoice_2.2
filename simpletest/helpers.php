<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class test_turmultiplechoice_question_maker extends test_question_maker {

    /**
     * Makes a turmultiplechoice question with choices 'A', 'B' and 'C' shuffled. 'A'
     * is correct, defaultmark 1.
     * @return qtype_turmultiplechoice_single_question
     */
    public static function make_a_turmultiplechoice_single_question() {
        question_bank::load_question_definition_classes('turmultiplechoice');
        $mc = new qtype_turmultiplechoice_single_question();
        self::initialise_a_question($mc);
        $mc->name = 'Multi-choice question, single response';
        $mc->questiontext = 'The answer is A.';
        $mc->generalfeedback = 'You should have selected A.';
        $mc->qtype = question_bank::get_qtype('turmultiplechoice');

        $mc->shuffleanswers = 1;
        $mc->qdifficulty = '0';

        $mc->answers = array(
            13 => new question_answer(13, 'A', 1, 'A is right', FORMAT_HTML),
            14 => new question_answer(14, 'B', -0.3333333, 'B is wrong', FORMAT_HTML),
            15 => new question_answer(15, 'C', -0.3333333, 'C is wrong', FORMAT_HTML),
        );

        return $mc;
    }

    /**
     * Makes a turmultiplechoice question with choices 'A', 'B', 'C' and 'D' shuffled.
     * 'A' and 'C' is correct, defaultmark 1.
     * @return qtype_turmultiplechoice_multi_question
     */
    public static function make_a_turmultiplechoice_multi_question() {
        question_bank::load_question_definition_classes('turmultiplechoice');
        $mc = new qtype_turmultiplechoice_multi_question();
        self::initialise_a_question($mc);
        $mc->name = 'Multi-choice question, multiple response';
        $mc->questiontext = 'The answer is A and C.';
        $mc->generalfeedback = 'You should have selected A and C.';
        $mc->qtype = question_bank::get_qtype('turmultiplechoice');

        $mc->shuffleanswers = 1;
        $mc->qdifficulty = '0';

        self::set_standard_combined_feedback_fields($mc);

        $mc->answers = array(
            13 => new question_answer(13, 'A', 0.5, 'A is part of the right answer', FORMAT_HTML),
            14 => new question_answer(14, 'B', -1, 'B is wrong', FORMAT_HTML),
            15 => new question_answer(15, 'C', 0.5, 'C is part of the right answer', FORMAT_HTML),
            16 => new question_answer(16, 'D', -1, 'D is wrong', FORMAT_HTML),
        );

        return $mc;
    }
}
