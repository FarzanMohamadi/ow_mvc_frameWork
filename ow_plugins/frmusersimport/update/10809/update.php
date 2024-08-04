<?php
$languageService = Updater::getLanguageService();

$languages = $languageService->getLanguages();
$langFaId = null;
$langEn = null;
foreach ($languages as $lang) {
    if ($lang->tag == 'fa-IR') {
        $langFaId = $lang->id;
    }
    if ($lang->tag == 'en') {
        $langEn = $lang->id;
    }
}

if ($langFaId != null) {
    $languageService->addOrUpdateValue($langFaId,'frmusersimport', 'import_user_guideline','            ابتدا می‌بایست یک فایل Excel ایجاد شود.  فایل مذکور می‌بایست دارای ۳ ستون با نام‌های: ۱- رایانامه  ۲- نام و نام‌خانوادگی ۳- تلفن همراه باشد. واردسازی اطلاعات می‌بایست دقیقا مانند شکل 1 انجام شود‌. فرمت واردسازی شماره تلفن همراه به صورت 989XXXXXXXXX+ باشد.
            <br/>
            {$image1}
            <br/>
            برای واردسازی شماره تلفن همراه به فرمت 989XXXXXXXXX+ بایستی کل ستون مربوطه‌ (برای مثال ستون C در شکل 2) انتخاب شود و بعد کلیک راست ماوس فشرده شود، از میان گزینه‌های نمایش داده شده گزینه Format Cells انتخاب گردد.
            <br/>
            {$image2}
            <br/>
            در ابزارک Format Cells بایستی  در بخش Number گزینه Text انتخاب شده و دکمه ok زده شود‌ (شکل 3).
            <br/>
            {$image3}
            <br/>
            پس از واردسازی اطلاعات فایل مورد نظر را save as نموده، در بخش File name نام فایل را وارد کرده و  سپس در قسمت save as type  گزینه Unicode Text انتخاب شود(شکل 4).
            <br/>
            {$image4}
            <br/>
            روی گزینه Tools کلیک کرده و گزینه Web Options انتخاب شود(شکل 5).
            <br/>
            {$image5}
            <br/>
            در ابزارک  Web Options بخش Encoding، برای Save this documents as گزینه (Unicode (UTF-8 انتخاب شده و دکمه ok زده شود(شکل 6).
            <br/>
            {$image6}
            <br/>
            سپس با زدن دکمه save فایل ذخیره شود. توجه شود که فایل می‌بایست با فرمت txt. ایجاد شده باشد.  شکل 7 نمونه فایل ذخیره شده را نشان می‌دهد که با برنامه Notepad باز شده است.
            <br/>
            {$image7}
            <br/>
            در مرحله آخر فایل ذخیره شده با برنامه ++Notepad باز شود و از منوی بالا روی گزینه Encoding کلیک کرده و گزینه Encode in UTF-8 انتخاب شود، سپس فایل دوباره ذخیره (save) شود(شکل 8).
            <br/>
            {$image8}
            <br/>');

    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption1','شکل 1 نحوه واردسازی اطلاعات در excel');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption2','شکل 2 انتخاب تنظیم Format برای یک ستون');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption3','شکل 3 انتخاب Text در بخش Number');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption4','شکل 4 انتخاب Unicode Text در بخش save as type');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption5','شکل 5 انتخاب Web Options از Tools');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption6','شکل 6  انتخاب (Unicode (UTF-8 برای save this document as');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption7','شکل 7 نمونه داده‌های فایل ذخیره شده در نمایش با برنامه Notepad');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','help_image_caption8','شکل 8 انتخاب گزینه Encode in UTF-8 در بخش Encoding مربوط به نرم افزار ++Notepad');
    $languageService->addOrUpdateValue($langFaId,'frmusersimport','guideline_heading','راهنمای ایجاد فایل با فرمت متنی');
}
