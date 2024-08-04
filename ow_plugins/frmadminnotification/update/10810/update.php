<?php
$languageService = Updater::getLanguageService();
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','user_save_success','تنظیمات با موفقیت ذخیره شد');
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','registration_notice_subject','کاربری عضو «{$site_name}» شد');

$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','registration_notice','کاربری با نام کاربری «{$username}» و اسم واقعی «{$realname}»، در تاریخ «{$join_date}»، عضو «{$site_name}» شد');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','registration_notice','User registered in {$site_name} (username: {$username} and real name: {$realname}) at {$join_date}');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','emailSendTo','Send to (If you set this field empty, the email of site will be used)');
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','emailSendTo','ارسال به (در صورت خالی گذاشتن این فیلد، از فیلد رایانامه وب‌گاه استفاده خواهد شد)');

$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','topic_forum_add_subject','کاربری در انجمن وب‌گاه «{$site_name}»، موضوعی ایجاد کرد');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','topic_forum_add_subject','User creates a topic of forum in {$site_name}');


$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','topic_forum_edit_description','    کاربری با نام کاربری «{$username}»، در انجمن وب‌گاه «{$site_name}»، موضوعی تحت عنوان «{$topic_title}» را ویرایش کرد (نشانی اینترنتی موضوع:
            <a href="{$topicUrl}">
            پیوند
            </a>
            )');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','topic_forum_edit_description','A user with username of {$username}, edit a topic of forum with name of {$topic_title} in {$site_name}. Link of topic is <a href="{$topicUrl}">here</a>');

$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','topic_forum_add_description','A user with username of {$username}, creates a topic of forum with name of {$topic_title} in {$site_name}. Link of topic is <a href="{$topicUrl}">here</a>');
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','topic_forum_add_description','     کاربری با نام کاربری «{$username}»، در انجمن وب‌گاه «{$site_name}»، موضوعی تحت عنوان «{$topic_title}» ایجاد کرد (نشانی اینترنتی موضوع:
            <a href="{$topicUrl}">
                پیوند
            </a>
            )');
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','comment_topic_forum_add_description','     کاربری با نام کاربری «{$username}»، در انجمن وب‌گاه «{$site_name}»، پاسخی در موضوعی تحت عنوان «{$topic_title}» درج کرد (نشانی اینترنتی موضوع:
            <a href="{$topicUrl}">
            پیوند
            </a>
            )');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','comment_topic_forum_add_description','A user with username of {$username}, creates a post of topic of forum with name of {$topic_title} in {$site_name}. Link of topic is <a href="{$topicUrl}">here</a>');

$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','comment_topic_forum_add_subject','User creates a post of topic of forum in {$site_name}');
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','comment_topic_forum_add_subject','کاربری در انجمن وب‌گاه «{$site_name}»، پاسخی در موضوع درج کرد');

$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','comment_news_add_subject','User creates a comment of news in {$site_name}');
$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','comment_news_add_subject',' کاربری در اخبار وب‌گاه «{$site_name}»، نظری در خبر درج کرد');

$languageService->addOrUpdateValueByLanguageTag('fa-IR','frmadminnotification','comment_news_add_description','     کاربری با نام کاربری «{$username}»، در اخبار وب‌گاه «{$site_name}»، یک نظر در خبری تحت عنوان «{$news_title}» درج کرد (نشانی اینترنتی خبر:
            <a href="{$newsUrl}">
            پیوند
            </a>
            )');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','comment_news_add_description','A user with username of {$username}, creates a comment of news with name of {$news_title} in {$site_name}. Link of news is <a href="{$newsUrl}">here</a>');

$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','newsCommentNotification','Send notification on creating comment in news');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','topicForumNotification','Send notification on creating post and comment in forum');
$languageService->addOrUpdateValueByLanguageTag('en','frmadminnotification','registerNotification','Send notification after registration');


