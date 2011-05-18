<?php
require_once t3lib_extMgm::extPath('cdnfiles').'class.tx_cdnfiles_specialconfiguration.php';
/**
 * Description of classtx_cdnfiles:
 * This class do all the replacement work througth hooks in ['tslib/class.tslib_fe.php']
 * The main method is: doReplacement
 *
 *
 * @author Fernando Arconada fernando.arconada@gmail.com
 */
class tx_cdnfiles {

    /** @var array holds the extension configuration */
    private $extensionConfiguration = array();

    /** @var string with current replacement directory, cause
     * I dont know how to pass more arguments to the callback function and i dont want to call pcre functions twice
     */
    private $currentDirectory ='';

    /**
     * @var tx_cdnfiles_specialconfiguration_interface this object reads the YAML configuration file and tests each file against it to look for any special configuration
     */
    private $specialConfigurationObj = null;

    public function __construct(){
        // TODO: override extension configuration from extension manager with template TSConfig if exists
        // global extension configuration is read the typo3conf/localconf.php and managed with the TYPO3 extension manager
        $this->extensionConfiguration = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cdnfiles']
        );
        // file with absolute path of the YAML file with advanced replacement configuration
        $this->extensionConfiguration['advancedconfig_file'] = PATH_site.$this->extensionConfiguration['advancedconfig_file'];

        // TODO: make this object plugable, which paramaters/initialization should it use
        // the object manager with advanced configuration. this object is responsible to read the YAML file and giving a original file
        // it returns an URL for this file if there is anyone
        $this->specialConfigurationObj = t3lib_div::makeInstance("tx_cdnfiles_specialconfiguration",$this->extensionConfiguration['advancedconfig_file']);
    }

    /**
     * Responsible of replacement inside the hook ['tslib/class.tslib_fe.php']
     * it takes a HTML string and returns the string with URLS replaced
     * 
     * @param string $htmlContent HTML of the page, file references should be in quotes
     * @return string HTML with the replaced content
     */
    public function doReplacement($htmlContent){

        /**
         * Do I have to work with, fileadmin/ uploads/ typo3temp/pics?
         * each one could have its own config
         */

        // TODO: avoid repeated code but it could lead to a less readable code
        // fileadmin/
        if ($this->extensionConfiguration['replace_fileadmin_directory']){
            // $this->currentDirectory is used inside 'callbackReplacementFunction'
            $this->currentDirectory='fileadmin';
            // pattern to match the fileadmin directory
            $pattern = $this->extensionConfiguration['fileadmin_regexp'];
            //always testing case insensitive
            $pattern = '|"'.$pattern.'"|i';
            $htmlContent = preg_replace_callback($pattern, array( &$this, 'callbackReplacementFunction'), $htmlContent);
        }

        // uploads/
        if ($this->extensionConfiguration['replace_uploads_directory']){
            $this->currentDirectory='uploads';
            $pattern = $this->extensionConfiguration['uploads_regexp'];
            $pattern = '|"'.$pattern.'"|i';
            $htmlContent = preg_replace_callback($pattern, array( &$this, 'callbackReplacementFunction'), $htmlContent);
        }

        // typo3temp/pics/
        if ($this->extensionConfiguration['replace_typo3temppics_directory']){
            $this->currentDirectory='typo3temppics';
            $pattern = $this->extensionConfiguration['typo3temppics_regexp'];
            $pattern = '|"'.$pattern.'"|i';
            $htmlContent = preg_replace_callback($pattern, array( &$this, 'callbackReplacementFunction'), $htmlContent);
        }
        
        return $htmlContent;

    }

    /**
     * This function is triggered for every PCRE match and it proccess the filereferences
     * Reponsible of replacement for just one file URL
     *
     * @param array $matchedText as matched in a PCRE regular expression
     * @return string The file reference proccessed in quotes
     */
    private function callbackReplacementFunction($matchedText){
            //TODO: add hook preReplacement

            // If you have a regular expression with () then use the first () variable
            if(isset($matchedText[1])){
                $searchedFile = $matchedText[1];
            }else{
                $searchedFile = $matchedText[0];
            }

            // TODO: add hook
            //Look for a special configuration for this file
            $fileWithUrlReplaced = $this->specialConfigurationObj->getFileUrlReplaced($searchedFile);
        
            /**
             * If !$fileWithUrlReplaced is because there isnt any special configuration for that file
             * so going to default config at directory level
             */
            if (!$fileWithUrlReplaced){
                //If no have any special configuration just apply the common config
                /// because I have $this->currentDirectory, I dont have to use a regular expression again to know in that directory i'm working
                switch($this->currentDirectory){
                    case 'fileadmin':                       
                        $fileWithUrlReplaced = $this->extensionConfiguration['fileadmin_urlprefix'] . $searchedFile;
                        break;
                    case 'uploads':                       
                        $fileWithUrlReplaced = $this->extensionConfiguration['uploads_urlprefix'] . $searchedFile;
                        break;
                    case 'typo3temppics':                     
                        $fileWithUrlReplaced = $this->extensionConfiguration['typo3temppics_urlprefix'] . $searchedFile;
                        break;

                }
            }

            // at least you should get your original file, but you should never go into this configuration
            if(!$fileWithUrlReplaced){
                $fileWithUrlReplaced = $searchedFile;
            }

            /**
             * $fileWithUrlReplaced != $searchedFile is because you did something with the file: you have replaced the file with a special config
             */
            // TODO: should I remove that directories in the URL even if if I have a special config for this file?
            // TODO: testcase fileadmin/somefile.js replaced with http://server.mycdn.com/fileadmin/something.js
            if($fileWithUrlReplaced != $searchedFile){
                //should I remove the fileadmin/ uploads/ or typo3temp/ directory
                if($this->extensionConfiguration['remove_fileadmin_directory']){
                                $fileWithUrlReplaced = str_replace('/fileadmin/', '/', $fileWithUrlReplaced);
                }
                if($this->extensionConfiguration['remove_uploads_directory']){
                                $fileWithUrlReplaced = str_replace('/uploads/', '/', $fileWithUrlReplaced);
                }
                if($this->extensionConfiguration['remove_typo3temp_directory']){
                                $fileWithUrlReplaced = str_replace('/typo3temp/', '/', $fileWithUrlReplaced);
                }

            }
            //TODO: add hook postReplacement

            // TODO: ensure that the file is always returned with quotes
            //dont forget the quotes
            return '"'.$fileWithUrlReplaced.'"';

    }
    
    /**
     * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
     *
     * This hook is executed if the page contains *_INT objects! It's called always at the
     * last hook before the final output. This isn't the case if you are using a
     * static file cache like nc_staticfilecache.
     *
     * @return bool
     */
    public function contentPostProcOutput($_params,$pObj) {

        $ret = $this->doReplacement($pObj->content);
        if ($ret){
            $pObj->content = $ret;
            return true;
        }else{
            return false;
        }
    }


    /**
     * Just a wrapper for the main function!  It's used for the contentPostProc-all hook.
     *
     * The hook is only executed if the page doesn't contains any *_INT objects. It's called
     * always if the page wasn't cached or for the first hit!
     *
     * @return bool
     */
    public function contentPostProcAll($_params,$pObj) {

        $ret = $this->doReplacement($pObj->content);
        if ($ret){
            $pObj->content = $ret;
            return true;
        }else{
            return false;
        }
    }
}
?>
