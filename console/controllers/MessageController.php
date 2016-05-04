<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

namespace yiingine\console\controllers;

use \Yii;
use \yii\helpers\Console;
use \yii\helpers\FileHelper;

/**
 * Extends Yii's message command to support other ways of defining message categories.
 * @author Antoine Mercier-Linteau <antoine.mercier-linteau@arza-studio.com>
 */
class MessageController extends \yii\console\controllers\MessageController
{
    /** @var array file => error.*/
    public $errors = [];
    
    /** @var boolean if the yiingine's special message categories should be used. */
    public $useYiingineCategories = false;
    
    /** @var boolean recurse into directories to find messages.*/
    public $recursive = true;
    
    /** @var array the messages that have been retrieved.*/
    public $messages = [];
    
    /**
     * @inheritdoc
     * */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['recursive']);
    }
    
    /**
     * @inheritdoc
     * */
    public function actionExtract($configFile = null)
    {
        if($configFile !== null) 
        {
            $configFile = Yii::getAlias($configFile);
            
            if(!is_file($configFile)) 
            {
                throw new Exception("The configuration file does not exist: $configFile");
            } 
            else 
            {
                $config = require($configFile);
            }
        }
        
        if(!isset($config['useYiingineCategories']) ||  !$config['useYiingineCategories'] || $configFile === null)
        {
            $this->useYiingineCategories = false;
            $this->ignoreCategories = array_merge([
                '__CLASS__',
                '*::className()',
                '\yiingine*',
                'get_class(*'
            ], isset($config['ignoreCategories']) ? $config['ignoreCategories']: []);
            
            // Special categories will not be used.
            return parent::actionExtract($configFile);
        }
        
        // Apply the configuration to the controller.
        foreach($config as $name => $value)
        {
            $this->$name = $value;
        }
        
        $this->ignoreCategories[] = 'yii';
        
        if($this->format != 'php')
        {
            throw new \yii\base\Exception('For now, only the php format is supported when using yiingine categories.');
        }
        
        $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(realpath(Yii::getAlias($config['sourcePath'])), \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
                \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        
        foreach($iterator as $dir)
        {
            if(!FileHelper::filterPath($dir, [
                'except' => $this->except,
                'basePath' => $dir
            ]))
            {
                continue;
            }
            
            if(!is_dir($dir)) // It appears that RecursiveDirectoryIterator can't correctly "totally" recognize directories.
            {
                continue;
            }
            
            $this->sourcePath = (string)$dir;
            $this->recursive = false;
            
            parent::actionExtract();
        }
        
        // Merge categories that point to the same class together.
        $messagesToMerge = [];
        foreach($this->messages as $category => $messages)
        {
            if(strpos($category, '\\') !== false) // If the category is a namespaced class name.
            {
                unset($this->messages[$category]);
                
                try
                {
                    $category = (new \ReflectionClass($category))->getFileName();
                    
                    if(isset($messagesToMerge[$category]))
                    {
                        $messagesToMerge = array_merge_recursive($messagesToMerge, [$category => $messages]);
                    }
                    else
                    {
                        $messagesToMerge[$category] = $messages;
                    }
                }
                catch(\ReflectionException $e)
                {
                    $cat = Console::ansiFormat($category, [Console::FG_CYAN]);
                    $skipping = Console::ansiFormat('Skipping category', [Console::FG_YELLOW]);
                    $this->stdout("$skipping $cat. Make sure this category is a valid class.\n");
                    
                    continue; // Category is not a class.
                }
            }
            elseif(is_file($category))
            {
                // If the translation call was not within a class file.
                if(ucfirst(str_replace('_', '', basename($category))) != str_replace('_', '', basename($category)))
                {
                    // It is assumed that the class to which this view belongs is in the parent folder.
                    $class = dirname(dirname($category)).DIRECTORY_SEPARATOR.ucfirst(baseName($category));
                
                    if(!is_file($class)) // If there is no such file.
                    {
                        $file = Console::ansiFormat($category, [Console::FG_CYAN]);
                        $skipping = Console::ansiFormat('Skipping category', [Console::FG_YELLOW]);
                        $this->stdout("$skipping $file. Make sure this view's class is in its parent folder or directly refer to its class using a namespaced class name.\n");
                        unset($this->messages[$category]);
                
                        continue;
                    }
                    
                    unset($this->messages[$category]);
                    $messagesToMerge[$class] = $messages;
                }
            }
        }
        
        $this->messages = array_merge_recursive($this->messages, $messagesToMerge);
        
        foreach($this->languages as $language)
        {
            // Save all extracted messages.   
            foreach($this->messages as $category => $msgs)
            {                
                if(is_file($category))
                {                    
                    $class = str_replace('.php', '', basename($category));
                    $dir = dirname($category).DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language;
                    parent::saveMessagesToPHP([$class => $msgs], $dir, $this->overwrite, $this->removeUnused, $this->sort, $this->markUnused);
                    unset($this->messages[$category]);
                }
            }
            
            $dir = Yii::getAlias($this->messagePath).DIRECTORY_SEPARATOR.$language;
            
            if(!is_dir($dir))
            {
                @mkdir($dir);
            }
            
            parent::saveMessagesToPHP($this->messages, $dir, $this->overwrite, $this->removeUnused, $this->sort, $this->markUnused);
        }
    }
    
    /**
     * @inheritdoc
     */
    protected function extractMessages($fileName, $translator, $ignoreCategories = [])
    {
        // Overriden so our own version of extract message can be called.
        
        if(!$this->useYiingineCategories)
        {
            return parent::extractMessages($fileName, $translator, $ignoreCategories);
        }
        
        $coloredFileName = Console::ansiFormat($fileName, [Console::FG_CYAN]);
        $this->stdout("Extracting messages from $coloredFileName...\n");
    
        $subject = file_get_contents($fileName);
        $messages = [];
        foreach ((array) $translator as $currentTranslator) {
            $translatorTokens = token_get_all('<?php ' . $currentTranslator);
            array_shift($translatorTokens);
            $tokens = token_get_all($subject);
            $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($fileName, $tokens, $translatorTokens, $ignoreCategories));
        }
    
        $this->stdout("\n");
    
        return $messages;
    }
    
    /**
     * Extracts messages from a parsed PHP tokens list.
     * @param string $fileName the name of the file from which tokens were extracted.
     * @param array $tokens tokens to be processed.
     * @param array $translatorTokens translator tokens.
     * @param array $ignoreCategories message categories to ignore.
     * @return array messages.
     */
    private function extractMessagesFromTokens($fileName, array $tokens, array $translatorTokens, array $ignoreCategories)
    {
        $messages = [];
        $translatorTokensCount = count($translatorTokens);
        $matchedTokensCount = 0;
        $buffer = [];
        $pendingParenthesisCount = 0;
        $string = '';
        
        foreach ($tokens as $token) {
            // finding out translator call
            if ($matchedTokensCount < $translatorTokensCount) {
                if ($this->tokensEqual($token, $translatorTokens[$matchedTokensCount])) {
                    $matchedTokensCount++;
                } else {
                    $matchedTokensCount = 0;
                }
            } elseif ($matchedTokensCount === $translatorTokensCount) {
                // translator found
    
                // end of function call
                if ($this->tokensEqual(')', $token)) {
                    $pendingParenthesisCount--;
    
                    if ($pendingParenthesisCount === 0) {
                        // end of translator call or end of something that we can't extract
                        if (isset($buffer[0][0], $buffer[1], $buffer[2][0]) && $buffer[1] === ',' && $buffer[2][0] === T_CONSTANT_ENCAPSED_STRING) {
                            // is valid call we can extract
                            $category = $buffer[0][1];
                            
                            if($buffer[0][0] === T_CONSTANT_ENCAPSED_STRING)
                            {
                                $category = mb_substr($category, 1, mb_strlen($category) - 2);
                            }
    
                            if (!$this->isCategoryIgnored($category, $ignoreCategories)) {
                                $message = stripcslashes($buffer[2][1]);
                                $message = mb_substr($message, 1, mb_strlen($message) - 2);
    
                                if($category == '__CLASS__')
                                {
                                    $category = $fileName;
                                    $messages[$category][] = $message;
                                }
                                // If the category is a string for a namespaced class name.
                                else if($buffer[0][0] === T_CONSTANT_ENCAPSED_STRING && strpos($category, '\\') !== false)
                                {
                                    $messages[$category][] = $message;
                                }
                                else if($buffer[0][0] === T_CONSTANT_ENCAPSED_STRING)
                                {
                                    $category = stripcslashes($category);
                                    $messages[$category][] = $message;
                                }
                                else
                                {
                                    // invalid call or dynamic call we can't extract
                                    $line = Console::ansiFormat($this->getLine($buffer), [Console::FG_CYAN]);
                                    $skipping = Console::ansiFormat('Skipping line', [Console::FG_YELLOW]);
                                    $this->stdout("$skipping $line. Make sure both category and message are static strings or known patterns.\n");
                                }
                            }
    
                            $nestedTokens = array_slice($buffer, 3);
                            if (count($nestedTokens) > $translatorTokensCount) {
                                // search for possible nested translator calls
                                $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($fileName, $nestedTokens, $translatorTokens, $ignoreCategories));
                            }
                        } 
                        else 
                        {
                            $category = explode(' ', $string)[0]; // Get the first part of the string.
                            
                            $message = stripcslashes(explode(' \'', str_replace([$category.' ', $category], '', $string))[0]);
                            $message = mb_substr($message, 1, mb_strlen($message) - 2);
                            
                            // When using ::className(), namespaces must be spelled out completely (ie. no "use").
                            if(strpos($category, '::className') !== false && strpos($category, '\\') !== false)
                            {
                                $category = str_replace('::className', '', $category);
                                
                                $messages[$category][] = $message;
                            }
                            else if(strpos($category, 'get_class$this->context') !== false)
                            {
                                $messages[$fileName][] = $message;
                            }
                            else
                            {
                                // invalid call or dynamic call we can't extract
                                $line = Console::ansiFormat($this->getLine($buffer), [Console::FG_CYAN]);
                                $skipping = Console::ansiFormat('Skipping line', [Console::FG_YELLOW]);
                                $this->stdout("$skipping $line. Make sure both category and message are static strings.\n");
                            }
                        }
    
                        // prepare for the next match
                        $matchedTokensCount = 0;
                        $pendingParenthesisCount = 0;
                        $buffer = [];
                        $string = '';
                    } else {
                        $buffer[] = $token;
                    }
                } elseif ($this->tokensEqual('(', $token)) {
                    // count beginning of function call, skipping translator beginning
                    if ($pendingParenthesisCount > 0) {
                        $buffer[] = $token;
                    }
                    $pendingParenthesisCount++;
                } elseif (isset($token[0]) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                    // ignore comments and whitespaces
                    $buffer[] = $token;
                }
                
                if(isset($token[1]))
                {
                    $string .= $token[1];
                }
            }
        }
    
        return $messages;
    }
    
    /**
     * @inheritdoc
     */
    protected function saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort, $markUnused)
    {
        if(!$this->useYiingineCategories)
        {
            return parent::saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort, $markUnused);
        }
        
        $this->messages = array_merge_recursive($this->messages, $messages);
    }
}
