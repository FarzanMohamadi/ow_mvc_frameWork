<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_utilities
 * @since 1.0
 */

class UTIL_Image
{
    CONST DIM_FULLSCREEN_HEIGHT = 1080;
    CONST DIM_FULLSCREEN_WIDTH = 1960;
    /**
     * We'll store image here
     *
     * @var WideImage\Image
     */
    protected $image;

    private $IMAGE_QUALITY;

    /**
     * Path to source image file
     *
     * @var string
     */
    protected $sourcePath;

    /**
     * @var boolean
     */
    protected $imageResized = false;

    /**
     * Class constructor
     *
     * @param string $sourcePath
     * @param string $format
     */
    public function __construct( $sourcePath, $format = 'JPEG' )
    {
        $this->sourcePath = $sourcePath;

        $this->image = WideImage\WideImage::load($sourcePath);

        $imageCompressionValue = OW::getConfig()->configExists('base', 'image_compression_percentage')?OW::getConfig()->getValue('base','image_compression_percentage'):'90';
        $this->IMAGE_QUALITY = intval($imageCompressionValue);
    }

    /**
     * Resizes image
     *
     * @param int $width
     * @param int $height
     * @param boolean $crop
     * 
     * @return UTIL_Image
     */
    public function resizeImage( $width, $height, $crop = false )
    {
        $iWidth = $this->image->getWidth();
        $iHeight = $this->image->getHeight();

        $this->imageResized = ($width <= $iWidth) || (isset($height) && $height <= $iHeight) ? true : false;

        if ( $width == null )
        {
            $width = $iWidth;
        }
        else
        {
            $width = $width > $iWidth ? $iWidth : $width;
        }

        if ( $height == null )
        {
            $height = $iHeight;
        }
        else
        {
            $height = $height > $iHeight ? $iHeight : $height;
        }

        if ( $crop )
        {
            $wHalf = ceil($width / 2);
            $hHalf = ceil($height / 2);

            $this->image = $this->image
                ->resize($width, $height, 'outside')
                ->crop('50%-' . $wHalf, '50%-' . $hHalf, $width, $height);
        }
        else
        {
            $this->image = $this->image->resize($width, $height);
        }

        return $this;
    }

    /**
     * Crops image
     *
     * @param int $left
     * @param int $top
     * @param int $width
     * @param int $height
     * 
     * @return UTIL_Image
     */
    public function cropImage( $left, $top, $width, $height )
    {
        $this->image = $this->image->crop($left, $top, $width, $height);

        return $this;
    }

    /**
     * Apply watermark to image
     *
     * @param string $wmPath
     * @param int $opacity
     * @param string $horPos
     * @param string $vertPos
     * @param int $margin
     *
     * @return UTIL_Image
     */
    public function applyWatermark( $wmPath, $opacity = 100, $horPos = 'right', $vertPos = 'bottom', $margin = 5 )
    {
        $wmImage = WideImage\WideImage::load($wmPath);

        $wmWidth = $wmImage->getWidth();
        $wmHeight = $wmImage->getHeight();

        switch ( $horPos )
        {
            case 'right':
                $horCoord = '100%-' . ($wmWidth + $margin);
                break;

            case 'left':
                $horCoord = '0%+' . $margin;
                break;

            default:
                $horCoord = '100%-' . ($wmWidth + $margin);
        }

        switch ( $vertPos )
        {
            case 'bottom':
                $vertCoord = '100%-' . ($wmHeight + $margin);
                break;

            case 'top':
                $vertCoord = '0%+' . $margin;
                break;

            default:
                $vertCoord = '100%-' . ($wmHeight + $margin);
        }

        $this->image = $this->image->merge($wmImage, $horCoord, $vertCoord, $opacity);

        return $this;
    }

    /**
     * Copies image
     *
     * @param string $destPath
     * 
     * @return UTIL_Image
     */
    public function saveImage( $destPath = null )
    {
        if ( !isset($destPath) )
        {
            $destPath = $this->sourcePath;
        }

        $format = \WideImage\MapperFactory::determineFormat($destPath);
        if ($format === 'PNG') {
            $this->image->saveToFile($destPath, round(9 - 9 * $this->IMAGE_QUALITY / 100));
        } elseif ($format === 'JPEG') {
            $this->image->saveToFile($destPath, $this->IMAGE_QUALITY);
        } else {
            $this->image->saveToFile($destPath);
        }

        return $this;
    }

    public function orientateImage()
    {
        try {
            if (!extension_loaded('exif')){
                return $this;
            }
            if(!function_exists('exif_read_data')) {
                return $this;
            }
            $exif = @exif_read_data($this->sourcePath);
        }catch (Exception $ex){
            return $this;
        }

        if ( !empty($exif['Orientation']) )
        {
            switch ( $exif['Orientation'] )
            {
                case 8:
                    $this->image = $this->image->rotate(-90);
                    break;

                case 3:
                    $this->image = $this->image->rotate(180);
                    break;

                case 6:
                    $this->image = $this->image->rotate(90);
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns image width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->image->getWidth();
    }

    /**
     * Returns image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->image->getHeight();
    }

    public function imageResized()
    {
        return $this->imageResized;
    }

    /**
     * Release memory allocated for image
     */
    public function __destruct()
    {
        $this->destroy();
    }

    public function destroy()
    {
        $this->image->destroy();
    }

    public function rotate( $angle, $bgColor = null, $ignoreTransparent = true )
    {
        if ( (int) $angle !== 0 )
        {
            $this->image = $this->image->rotate($angle, $bgColor, $ignoreTransparent);
        }

        return $this;
    }
}
