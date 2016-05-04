<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\libs;

use \Yii;
use \yii\web\View;

/**
* @desc A class of useful functions.
* @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
*/
class Functions
{
    /**
     * @var string the mysql date time format that can be given to date();
     */
    public static $MySQLDateTimeFormat = 'Y-m-d H:i:s';
    
    /** @var string the mysql date format that can be given to date(); */
    public static $MySQLDateFormat = 'Y-m-d';
    
    /**
    * @var string the mysql date time format for use with Yii validators
    */
    public static $MySQLDateTimeYiiFormat = 'yyyy-M-d H:m:s';
    
    /**
    * @var string the mysql date format for use with Yii validators
    */
    public static $MySQLDateYiiFormat = 'yyyy-M-d';
    
    /**
    * Generate a string of random characters of arbitrary length
    * @param $length integer the length of the return string.
    * @param $vowels boolean include vowels.
    * @param $consonnants boolean include consonnants.
    * @param $numbers boolean include numbers.
    * @param $upperCase boolean include upper case letters.
    * @return string random characters.
    */
    public static function generateRandomString($length, $vowels = true, $consonnants = true, $numbers = false, $upperCase = false)
    {
        if(!is_integer($length))
        {
            throw new Exception('$length must be an integer');
        }
        
        //Build the possible characters set.
        $characters = '';
        $characters .= $vowels ? 'aeiouy' : '';
        $characters .= $vowels && $upperCase ? 'AEIOUY': '';
        $characters .= $consonnants ? 'bcdfghjklmnpqrstvwxz': '';
        $characters .= $consonnants && $upperCase ? 'BCDFGHJKLMNPQRSTVWXZ': '';
        $characters .= $numbers ? '0123456789': '';
         
        $charactersLength = strlen($characters) - 1; //Compute the length of the character set
         
        $string = ''; //The string that will be returned.
        for (; $length > 0; $length--)
        {
        //Select a random character from the possible set.
        $string .= $characters[mt_rand(0, $charactersLength)];
        }
         
        return $string;
    }
    
    /**
     * Removes accents from a string.
     * @param string $string the string.
     * @return string $string without accents.*/
    public static function stripAccents($string)
    {
        $accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ā', 'Ą', 'Ă', 'Æ', 'Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ď', 'Đ', 'È', 'É', 'Ê', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ĝ', 'Ğ', 'Ġ', 'Ģ', 'Ĥ', 'Ħ', 'Ì', 'Í', 'Î', 'Ï', 'Ī', 'Ĩ', 'Ĭ', 'Į', 'İ', 'Ĳ', 'Ĵ', 'Ķ', 'Ł', 'Ľ', 'Ĺ', 'Ļ', 'Ŀ', 'Ñ', 'Ń', 'Ň', 'Ņ', 'Ŋ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Œ', 'Ŕ', 'Ř', 'Ŗ', 'Ś', 'Š', 'Ş', 'Ŝ', 'Ș', 'Ť', 'Ţ', 'Ŧ', 'Ț', 'Ù', 'Ú', 'Û', 'Ü', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ũ', 'Ų', 'Ŵ', 'Ý', 'Ŷ', 'Ÿ', 'Ź', 'Ž', 'Ż', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ā', 'ą', 'ă', 'æ', 'ç', 'ć', 'č', 'ĉ', 'ċ', 'ď', 'đ', 'è', 'é', 'ê', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ƒ', 'ĝ', 'ğ', 'ġ', 'ģ', 'ĥ', 'ħ', 'ì', 'í', 'î', 'ï', 'ī', 'ĩ', 'ĭ', 'į', 'ı', 'ĳ', 'ĵ', 'ķ', 'ĸ', 'ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ō', 'ő', 'ŏ', 'œ', 'ŕ', 'ř', 'ŗ', 'ś', 'š', 'ş', 'ŝ', 'ș', 'ť', 'ţ', 'ŧ', 'ț', 'ù', 'ú', 'û', 'ü', 'ū', 'ů', 'ű', 'ŭ', 'ũ', 'ų', 'ŵ', 'ý', 'ÿ', 'ŷ', 'ž', 'ż', 'ź', 'Þ', 'þ', 'ß', 'ſ', 'Ð', 'ð');
        $letters = array('A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'C', 'C', 'C', 'C', 'D', 'D', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'G', 'G', 'G', 'G', 'H', 'H', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'J', 'J', 'K', 'L', 'L', 'L', 'L', 'L', 'N', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'E', 'R', 'R', 'R', 'S', 'S', 'S', 'S', 'S', 'T', 'T', 'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'W', 'Y', 'Y', 'Y', 'Z', 'Z', 'Z', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'e', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'f', 'g', 'g' ,'g', 'g', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i','i', 'i', 'j', 'j', 'k', 'k', 'l', 'l', 'l', 'l' ,'l' ,'n', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o' ,'o', 'o', 'o', 'o' ,'o', 'e', 'r' ,'r' ,'r' ,'s', 's', 's' ,'s', 's', 't' ,'t' ,'t' ,'t' ,'u' ,'u', 'u' ,'u' ,'u', 'u' ,'u' ,'u' ,'u' ,'u' ,'w', 'y', 'y', 'y', 'z', 'z', 'z', 'T', 't', 'B', 'f','D', 'd');
        return str_replace($accents, $letters, $string);
    }
    
    /**
     * Encodes a string to make it suitable for file names.
     * @param string $fileName the string to encode.
     * @param string $uniqueIdentifier a string that should be added between the name 
     *     and the extension.
     * @param string the encoded string.*/
    public static function encodeFileName($fileName, $uniqueIdentifier = '')
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION); //Extracts the extention.
        
        if($ext) //If there is an extension.
        {
            //Remove it from the file name and encode it.
            $fileName = str_replace('.'.$ext, '', $fileName);
            $ext = self::encodeFileName($ext);
        }
        
        //Encode the file name.
        $fileName = self::stripAccents($fileName);
        $fileName = mb_strtolower($fileName);
        $fileName = str_replace(' ', '-', $fileName);
        $fileName = preg_replace("/[^-a-z0-9_]/", "", $fileName );
        $fileName = trim($fileName, '-');
        $fileName = str_replace('--', '-', $fileName); //Removes duplicates --.
        
        //If there was an extension, add it and then return.
        return $ext ? $fileName.$uniqueIdentifier.'.'.$ext: $fileName;
    }
    
    /**
     * ucfirst for multibyte strings. In other words, this function will upper case 
     * string with accents.
     * @param string $str the string to ucfirst.
     * @param string $encoding the encoding of the string.
     * @param boolean $lower_str_end if the rest of the string should be lower case.
     * @return string with an upper cased firts letter.*/
    public static function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) 
    {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        }
        else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
    
    /**
     * Convert and escaped html string back to html.
     * @param string $htmlString the html string.
     * @return string the converted html string.
     */
    public static function unhtmlentities($htmlString) 
    {
        $tmp = get_html_translation_table(HTML_ENTITIES);
        $tmp = array_flip($tmp);
        $chaineTmp = strtr($htmlString, $tmp);
 
        return $chaineTmp;
    }
    
    /**
     * Converts a short hand bytes representation to an integer.
     * See: http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes.
     * Source:http://www.php.net/manual/en/function.ini-get.php#96996
     * @param string $size_str the short hand bytes representation.
     * @return mixed the integer representation of the string or the string if conversion failed.
     * */
    public static function returnBytes($size_str)
    {
        switch (substr ($size_str, -1))
        {
            case 'M': case 'm': return (int)$size_str * 1048576;
            case 'K': case 'k': return (int)$size_str * 1024;
            case 'G': case 'g': return (int)$size_str * 1073741824;
            default: return $size_str;
        }
    }
    
    /**@param string $word singular|plural.
     * @param boolean $plural return the plural.
     * @return the plural or singular form.*/
    public static function pickPlural($word, $plural = true)
    {
        $separator = mb_strpos($word, '|'); //Find the | separator.
        
        if($separator !== false) //If there is a separator.
        {
            //Pick the plural or singular part.
            $word = $plural ? mb_substr($word, $separator + 1): mb_substr($word, 0, $separator);
        }
        
        return $word;
    }
    
    /** @param mixed $value the value to convert to a string representation of a boolean.
     * @return the string representation of the boolean value of the parameter. */
    public static function strbool($value){ return $value ? 'true' : 'false' ; }
    
    /** A multi-byte version of php's str_split function.
     * 
     * Copied from https://gist.github.com/girvan/2155412
     * @param string $string the string to split.
     * @param int $string_length the length of each part.
     * @param string $charset the charset to use.
     * @return array the string divided into parts.
     * */
    public static function mb_str_split($string, $string_length = 1, $charset = 'utf-8')
    {
        if(mb_strlen($string, $charset) > $string_length || !$string_length)
        {
            do 
            {
                $c = mb_strlen($string, $charset);
                $parts[] = mb_substr($string, 0, $string_length, $charset);
                $string = mb_substr($string, $string_length, $c - $string_length, $charset);
            }
            while(!empty($string));
        } 
        else 
        {
            $parts = array($string);
        }
        
        return $parts;
    }
        
    /**
     * @param string $html the html code to treat.
     * @param string $view the view rendering the gallery.
     * @param integer $width the width resized of the iframe found.
     * @param integer $height the height resized of the iframe found.
     * @return the html with iframes resized and optimized. */
    public static function replaceGalleries($html, $view, $width=false, $height=false)
    {
        $objets = array();
        
        $html = str_replace("\n", "", $html);
        $html = str_replace("\r", "", $html);
        
        if(preg_match_all("#\{\{gallery\}\}(.+)\{\{\/gallery\}\}#", $html, $isolatedGalleries) >= 1)
        {
            foreach($isolatedGalleries[0] as $isolatedGallery)
            {
                // Imgs tags isolation
                if(preg_match_all('#(<img[^>]+>)#i', $isolatedGallery, $isolatedImages) >= 1)
                {
                    foreach($isolatedImages[0] as $isolatedImage)
                    {
                        // Get isoled img src attribute
                        $srcAttr = (preg_match_all('/src=\"([a-zA-Z0-9\.\_\-\/\:\?\=]+)\"/', $isolatedImage, $resultSrc) == 1) ? $resultSrc[1][0] : false ;
                        // Set path
                        $path = str_replace(Yii::$app->request->hostInfo.Yii::$app->baseUrl, '', $srcAttr);
                        // Add path to objets
                        array_push($objets, $path);
                    }
                }
                
                $params = array('objects'=>$objets, 'play'=>false);
                if($width) $params['width'] = $width;
                if($height) $params['height'] = $height;

                $gallery = Yii::$app->controller->renderPartial($view, $params, true);
                
                // Replacing the html returned
                $html = str_replace($isolatedGallery, $gallery, $html);
            }
        }
            
        return $html;
    }
}
