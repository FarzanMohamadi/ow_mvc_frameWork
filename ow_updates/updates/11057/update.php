<?php
    $sitemapURL = OW_URL_HOME . 'sitemap.xml';
    $filename= OW_DIR_ROOT . 'robots.txt';
    $contents = OW::getStorage()->fileGetContent($filename);
    if (strpos($contents, $sitemapURL) == false) {
        $sitemapURL = "Sitemap: " . $sitemapURL . "\r\n";
        $contents = $contents . "\r\n" . $sitemapURL;
        file_put_contents($filename,$contents);
    }