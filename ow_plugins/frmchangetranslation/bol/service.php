<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/14/2017
 * Time: 11:40 AM
 */
class FRMCHANGETRANSLATION_BOL_Service
{
    use OW_Singleton;

    private $languageService;
    private $importPath;

    private function __construct()
    {
        $this->languageService = BOL_LanguageService::getInstance();
        if (isset($this->languageService))
            $this->importPath = $this->languageService->getImportDirPath();
    }

    public function importUploadedFile($uploadedFilePath)
    {
        $importPackage = array();
        if (preg_match('/\.xml/', $uploadedFilePath)) {
            $xmlInfo = $this->getXmlInfo($uploadedFilePath);
            if (!isset($xmlInfo) || empty($xmlInfo))
                return false;
            $importPackage[$xmlInfo['tag']][] = $xmlInfo['prefix'];
        } elseif (preg_match('/\.zip/', $uploadedFilePath)) {
            if (!$this->extractPackageTo($uploadedFilePath, $this->importPath)) {
                return false;
            }
            $extractedFiles = glob("{$this->importPath}*");
            if (!empty($extractedFiles)) {
                foreach ($extractedFiles as $index => $dir) {
                    $dh = opendir($dir);
                    while (false !== ($file = readdir($dh))) {
                        if ($file == '.' || $file == '..')
                            continue;
                        if (!OW::getStorage()->isDir("{$dir}/{$file}")) {
                            if (!preg_match('/\.xml/', $file) || $file == 'language.xml') {
                                continue;
                            }
                            $xmlInfo = $this->getXmlInfo($dir . DS . $file);
                            if (!isset($xmlInfo) || empty($xmlInfo))
                                continue;
                            $importPackage[$xmlInfo['tag']][] = $xmlInfo['prefix'];
                        }
                    }
                    closedir($dh);
                }
            }
        } else {
            return false;
        }
        $this->importTranslation($importPackage);
        return true;
    }

    private function getXmlInfo($xmlPath)
    {
        $xml = simplexml_load_file($xmlPath);
        $tag = strval($xml->attributes()->language_tag);
        $prefix = strval($xml->attributes()->name);
        return array(
            'tag' => $tag,
            'prefix' => array(
                'xml' => $xml,
                'prefix' => $prefix
            )
        );
    }

    /**
     * @param string $packagePath
     * @param string $destination
     * @return bool
     */
    private function extractPackageTo($packagePath, $destination)
    {
        if (!isset($packagePath) || !isset($destination))
            return false;

        $zip = new ZipArchive();
        $opened = $zip->open($packagePath);
        if (!$opened) {
            OW::getStorage()->removeFile($packagePath, true);
            return false;
        }
        $zip->extractTo($destination);
        $zip->close();
        OW::getStorage()->removeFile($packagePath, true);
        return true;
    }

    private function importTranslation(array $importedPackage)
    {
        foreach ($importedPackage as $tag => $prefixes) {
            $languageHasChange = false;
            foreach ($prefixes as $prefixItem) {
                if (isset($prefixItem['xml'])) {
                    $xml = $prefixItem['xml'];
                } else {
                    continue;
                }
                $this->languageService->importPrefix($xml, false, true, true);
                $languageHasChange = true;
            }
            if ($languageHasChange)
                $this->regenerateCache($tag);
        }
    }

    private function regenerateCache($tag)
    {
        $language = $this->languageService->findByTag($tag);
        $this->languageService->generateCache($language->getId());
    }

    /**
     * @return string
     */
    public function getImportPath()
    {
        return $this->importPath;
    }
}