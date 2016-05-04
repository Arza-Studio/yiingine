<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\widgets;

use \Yii;
use \yii\helpers\Html;

/**
 * A widget for truncating text in an HTML aware way.
 * @author Antoine Wolff <antoine.wolff@arza-studio.com>
 */
class Truncate extends \yii\base\Widget 
{
    /** @var string the html to truncate */
    public $html;
    
    /** @var integer the length of returned string, including ending. */
    public $length = 150;
    
    /** @var string used as ending and appended to the trimmed string. */
    public $ending = '...';
    
    /** @var boolean if false, $string will not be cut mid-word. */
    public $exact = false;
    
    /** @var boolean if true, html tags will be handled correctly. */
    public $considerHtml = true;
    
    /** @var boolean if true, remove paragraphe breaks, bold, italic, underline. */
    public $removeFormating = false;
    
    /** 
     * @var string $string the string whose formatting should be removed.
     * @return string without paragraph breaks (except for img wrapping), bold, italic, underline. 
     */
    protected function removeFormating($string)
    {
        $string = str_replace(["\n", '  ', '  ', '  '], ' ', $string);
        
        // Encode images wrapped in paragraphs to preserve them (see below).
        if(preg_match_all('#(<img[^>]+>)#i', $string, $isolatedImgs) >= 1)
        {
            foreach($isolatedImgs[0] as $isolatedImg)
            {
                $string = str_replace('<p>'.$isolatedImg.'</p>', '{p}'.$isolatedImg.'{/p}', $string);
            }
        }
        
        // Remove paragraphs breaks (except for those wrapping img tags).
        $string = str_replace('</p><p>', ' ', $string);
        
        // Retore paragraphs wrapping img tags.
        $string = str_replace('{p}', '<p>', $string);
        $string = str_replace('{/p}', '</p>', $string);
        
        $string = str_replace([
            '<b>', '</b>', '<strong>', '</strong>', // Remove bold tags.
            '<i>', '</i>', '<em>', '</em>', // Remove italic tags.
            '<u>', '</u>' // Remove underline tags
        ], '', $string);
        
        return $string;
    }
    
    /**
     * @inheritdoc
     */
    public function run() 
    {
        if($this->considerHtml) // If the html tags must be handled correctly.
        {
            // If the string length without html tags in under the required length.
            if(mb_strlen(preg_replace('/<.*?>/', '', $this->html)) <= $this->length)
            {
                return $this->html;
            }
            
            $totalLength = mb_strlen($this->ending);
            $openTags = [];
            $truncate = '';
            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $this->html, $tags, PREG_SET_ORDER);
            foreach($tags as $tag)
            {
                if(!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2]))
                {
                    if(preg_match('/<[\w]+[^>]*>/s', $tag[0]))
                    {
                        array_unshift($openTags, $tag[2]);
                    }
                    elseif(preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag))
                    {
                        $pos = array_search($closeTag[1], $openTags);
                        if($pos !== false)
                        {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];
                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                
                if($contentLength + $totalLength > $this->length)
                {
                    $left = $this->length - $totalLength;
                    $entitiesLength = 0;
                    if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE))
                    {
                        foreach($entities[0] as $entity)
                        {
                            if ($entity[1] + 1 - $entitiesLength <= $left)
                            {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            }
                            else 
                            {
                                break;
                            }
                        }
                    }
                    $truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
                    break;
                }
                else
                {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if($totalLength >= $this->length)
                {
                    break;
                }
            }
        }
        else // If we don't care about html tags.
        {
            if(mb_strlen($this->html) <= $this->length)
            {
                return $this->html;
            }
            else
            {
                $truncate = mb_substr($this->html, 0, $this->length - strlen($this->ending));
            }
        }
        if(!$this->exact) // If we don't want the string to be cut mid-word.
        {
            $spacepos = mb_strrpos($truncate, ' ');
            if(isset($spacepos))
            {
                if($this->considerHtml)
                {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                    if(!empty($droppedTags))
                    {
                        foreach($droppedTags as $closingTag)
                        {
                            if(!in_array($closingTag[1], $openTags))
                            {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }
        if($this->removeFormating) // If the formating must be removed.
        {
            $truncate = $this->removeFormating($truncate);
        }
        
        // Add ending string.
        $truncate .= $this->ending;
        
        if($this->considerHtml)
        {
            foreach ($openTags as $tag)
            {
                $truncate .= '</'.$tag.'>';
            }
        }
        
        return $truncate;
    }
}
