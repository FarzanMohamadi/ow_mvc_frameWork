<?php
Updater::getLanguageService()->deleteLangKey("photo", "meta_title_photo_view");
Updater::getLanguageService()->importPrefixFromDir(__DIR__ . DS . 'langs');


