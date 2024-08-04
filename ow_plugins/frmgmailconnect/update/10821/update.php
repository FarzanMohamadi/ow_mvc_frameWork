<?php
$authorization = OW::getAuthorization();
$groupName = 'frmgmailconnect';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view_newsfeed', true);

$languageService = Updater::getLanguageService();
$value='<p>
برای فعال‌سازی اتصال حساب کاربری Gmail به سامانه مراحل زیر را اجرا کنید:
</p>
<ul class="ow_regular ow_stdmargin">
<li>
<b><a href="https://docs./doku.php?id=%D8%B1%D8%A7%D9%87_%D8%A7%D9%86%D8%AF%D8%A7%D8%B2%DB%8C_%D9%88%D8%B1%D9%88%D8%AF_%D9%88_%DB%8C%D8%A7_%D8%AB%D8%A8%D8%AA_%D9%86%D8%A7%D9%85_%D8%A8%D8%A7_%D8%AD%D8%B3%D8%A7%D8%A8_%DA%A9%D8%A7%D8%B1%D8%A8%D8%B1%DB%8C_gmail" target="_blank">Google Application خود را بسازید</a></b>  و نام آن را هم‌نام نام سامانه خود وارد کنید
</li>
<li>به شما دو اطلاعات <b>Client ID</b> و <b>Client secret</b> داده می‌شود. با استفاده از آن‌ها اطلاعات زیر را کامل کنید
</li>
</ul>';

$languageService->addOrUpdateValueByLanguageTag('fa-IR', 'frmgmailconnect', 'register_app_desc', $value);
