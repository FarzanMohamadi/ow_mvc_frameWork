<?php
/**
 * FRM Challenge
 */
if (OW::getConfig()->configExists('frmchallenge', 'solitary_question_count'))
    OW::getConfig()->deleteConfig('frmchallenge', 'solitary_question_count');
if (OW::getConfig()->configExists('frmchallenge', 'solitary_answer_time'))
    OW::getConfig()->deleteConfig('frmchallenge', 'solitary_answer_time');
if (OW::getConfig()->configExists('frmchallenge', 'universal_question_count'))
    OW::getConfig()->deleteConfig('frmchallenge', 'universal_question_count');
if (OW::getConfig()->configExists('frmchallenge', 'universal_answer_time'))
    OW::getConfig()->deleteConfig('frmchallenge', 'universal_answer_time');