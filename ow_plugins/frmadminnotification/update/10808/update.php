<?php
$languageService = Updater::getLanguageService();

$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','topic_forum_edit_description','            کاربری با نام کاربری «{$username}»، در انجمن وب‌گاه «{$site_name}»، موضوعی تحت عنوان «{$topic_title}» را ویرایش کرد. (نشانی اینترنتی موضوع:
            <a href="{$topicUrl}">
            پیوند
            &lt;/a&gt;
            )');

$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','topic_forum_edit_description','A user with username of {$username}, edit a topic of forum with name of {$topic_title} in {$site_name}. Link of topic is <a href="{$topicUrl}">here</a>.');
