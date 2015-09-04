<?php

define('CLI_SCRIPT', 1);

require_once('../../../../config.php');

$audiofolder = $CFG->dataroot . '/' . $CFG->tursound . '/audio/';
$imagefolder = $CFG->dataroot . '/' . $CFG->turimage . '/image/';

$fs = get_file_storage();
$file_record = array(
    'contextid' => 1,
    'component' => 'question',
    'filepath' => '/',
    'userid' => 2,
    'author' => 'Admin User',
    'license' => 'allrightsreserved'
);

$sql = "SELECT q.id, q.image AS questionimage
          FROM {question} q
         WHERE q.qtype = ?";
$params = array('turmultiplechoice');
$questions = $DB->get_records_sql($sql, $params);

foreach ($questions as $question) {

    $file_record['itemid'] = $question->id;

    $filename = substr($question->questionimage, 6);
    $file_record['filearea'] = 'questionimage';
    $file_record['filename'] = $filename;
    $file_record['timecreated'] = time();
    $file_record['timemodified'] = time();
    $fs->create_file_from_pathname($file_record, $imagefolder . $filename);
}
