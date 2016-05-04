<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */
namespace yiingine\components;

/** A component to manage a color palette by providing the ability to get
 * different brightnesses of a color. */
class Palette extends \yii\base\Object 
{
    /** @var array the base colors. */
    public $colors = [];

    /** Returns a color with a brightness difference applied to it.
     * @param string $color the color to manipulate, if it starts with #, the color
     * is not retrieved from the $colors array.
     * @param int $brightness the brightness difference from -95 to +95.
     * @return string the modified color. */
    public function get($color, $brightness = 0) 
    {
        //If the color was specified as an hex value pass it as is to mix.
        return self::mix(strpos($color, '#') === 0 ? $color : $this->colors[$color], (100 - abs($brightness)) / 100, $brightness > 0 ? 255 : 0);
    }

    /** Returns a color with a brightness difference applied to it.
     * @param string $color the color to manipulate, if it starts with #, the color
     * is not retrieved from the $colors array.
     * @param int $brightness the brightness difference from -95 to +95.
     * @return string the modified color in rgb format. */
    public function rgb($color, $brightness = 0) 
    {
        //If the color was specified as an hex value pass it as is to mix.
        return self::mix(strpos($color, '#') === 0 ? $color : $this->colors[$color], (100 - abs($brightness)) / 100, $brightness > 0 ? 255 : 0, 'rgb');
    }
    
    /** Returns a color with a brightness difference applied to it.
     * @param string $color the color to manipulate, if it starts with #, the color
     * is not retrieved from the $colors array.
     * @param int $brightness the brightness difference from -95 to +95.
     * @param int $opacity the transparency from 0 to 1.
     * @return string the modified color in rgba format. */
    public function rgba($color, $brightness = 0, $opacity = 1) 
    {
        //If the color was specified as an hex value pass it as is to mix.
        return self::mix(strpos($color, '#') === 0 ? $color : $this->colors[$color], (100 - abs($brightness)) / 100, $brightness > 0 ? 255 : 0, 'rgba', $opacity);
    }

    /** Applies a mask to a color..
     * @param string $hex the color to mix.
     * @param float $percent the strength of the mix.
     * @param integer $mask the mask to apply to the color.
     * @return string the modified color. */
    public static function mix($hex, $percent, $mask, $format = 'hex', $opacity = 1) 
    {
        // '#' eraser for hex
        $hex = str_replace('#', '', $hex);
        //dump($percent);
        //Convert an hex value to rgb.
        $hex = str_split($hex, 2);
        $rgb0 = hexdec($hex[0]);
        $rgb1 = hexdec($hex[1]);
        $rgb2 = hexdec($hex[2]);

        //Apply the mix to the rgb value. 
        $rgb0 = round($rgb0 * $percent) + round($mask * (1 - $percent));
        $rgb0 > 255 ? $rgb0 = 255 : null;
        $rgb1 = round($rgb1 * $percent) + round($mask * (1 - $percent));
        $rgb1 > 255 ? $rgb1 = 255 : null;
        $rgb2 = round($rgb2 * $percent) + round($mask * (1 - $percent));
        $rgb2 > 255 ? $rgb2 = 255 : null;

        if ($format == 'hex') 
        {
            //Convert the rgb back to hexadecimal.
            $hex = dechex($rgb0);
            strlen($hex) < 2 ? $hex = '0' . $hex : null;
            $hexDigit = dechex($rgb1);
            $hex .= strlen($hexDigit) < 2 ? '0' . $hexDigit : $hexDigit;
            $hexDigit = dechex($rgb2);
            $hex .= strlen($hexDigit) < 2 ? '0' . $hexDigit : $hexDigit;
            return '#' . $hex;
        }
        elseif ($format == 'rgba') 
        {
            return 'rgba(' . $rgb0 . ',' . $rgb1 . ',' . $rgb2 . ','.$opacity.')';
        }
        else 
        {
            return 'rgb(' . $rgb0 . ',' . $rgb1 . ',' . $rgb2 . ')';
        }
    }
}
