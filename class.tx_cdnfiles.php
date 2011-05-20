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
     * @var tx_cdnfiles_specialconfiguration_interface this object reads the YAML configuration fileUrl and tests each fileUrl against it to look for any special configuration
     */
    private $specialConfigurationObj = null;

    public function __construct(){
        // TODO: override extension configuration from extension manager with template TSConfig if exists
        // global extension configuration is read the typo3conf/localconf.php and managed with the TYPO3 extension manager
        $this->extensionConfiguration = unserialize(
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cdnfiles']
        );
        // fileUrl with absolute path of the YAML fileUrl with advanced replacement configuration
        $this->extensionConfiguration['advancedconfig_file'] = PATH_site.$this->extensionConfiguration['advancedconfig_file'];

        // TODO: make this object plugable, which paramaters/initialization should it use
        // the object manager with advanced configuration. this object is responsible to read the YAML fileUrl and giving a original fileUrl
        // it returns an URL for this fileUrl if there is anyone
        $this->specialConfigurationObj = t3lib_div::makeInstance("tx_cdnfiles_specialconfiguration",$this->extensionConfiguration['advancedconfig_file']);
    }

    /**
     * Responsible of replacement inside the hook ['tslib/class.tslib_fe.php']
     * it takes a HTML string and returns the string with URLS replaced
     * 
     * @param string $htmlContent HTML of the page, fileUrl references should be in quotes
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
     * Appends a prefix to a file URL depending of the directory you are working with
     * @param  $fileUrl The file URL input
     * @return string FileURL + a prefix
     */
    private function prefixFileUrl($fileUrl) {
        //at least it should result the fileUrl itself
        $fileUrlWithPrefix = $fileUrl;
        
        switch($this->currentDirectory){
                case 'fileadmin':
                    $fileUrlWithPrefix = $this->extensionConfiguration['fileadmin_urlprefix'] . $fileUrl;
                    break;
                case 'uploads':
                    $fileUrlWithPrefix = $this->extensionConfiguration['uploads_urlprefix'] . $fileUrl;
                    break;
                case 'typo3temppics':
                    $fileUrlWithPrefix = $this->extensionConfiguration['typo3temppics_urlprefix'] . $fileUrl;
                    break;

        }

        return $fileUrlWithPrefix;
    }

    /**
     * Remove fileadmin/ uploads/ or typo3temp/ directory from the input URL only if you have set it in your config
     * @param  $fileUrl input URL
     * @return string file with
     */
    private function removeDirectoryFromUrlIfNeeded($fileUrl) {
        // TODO: should I remove that directories in the URL even if if I have a special config for this fileUrl?
        // TODO: testcase fileadmin/somefile.js replaced with http://server.mycdn.com/fileadmin/something.js

        //should I remove the fileadmin/ uploads/ or typo3temp/ directory
        if($this->extensionConfiguration['remove_fileadmin_directory']){
                        $fileUrl = str_replace('/fileadmin/', '/', $fileUrl);
        }
        if($this->extensionConfiguration['remove_uploads_directory']){
                        $fileUrl = str_replace('/uploads/', '/', $fileUrl);
        }
        if($this->extensionConfiguration['remove_typo3temp_directory']){
                        $fileUrl = str_replace('/typo3temp/', '/', $fileUrl);
        }

        return $fileUrl;
    }
    /**
     * This function is triggered for every PCRE match and it proccess the filereferences
     * Reponsible of replacement for just one fileUrl URL
     *
     * @param array $matchedText as matched in a PCRE regular expression
     * @return string The fileUrl reference proccessed in quotes
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
            //Look for a special configuration for this fileUrl
            $fileWithUrlReplaced = $this->specialConfigurationObj->getFileUrlReplaced($searchedFile);
        
            /**
             * If !$fileWithUrlReplaced is because there isnt any special configuration for that fileUrl
             * so going to default config at directory level
             */
            if (!$fileWithUrlReplaced){
                //If no have any special configuration just apply the common config
                /// because I have $this->currentDirectory, I dont have to use a regular expression again to know in that directory i'm working
                $fileWithUrlReplaced = $this->prefixFileUrl($searchedFile);
            }

            /**
             * $fileWithUrlReplaced != $searchedFile is because you did something with the fileUrl: you have replaced the fileUrl with a special config
             */
            if($fileWithUrlReplaced != $searchedFile){
                //should I remove the fileadmin/ uploads/ or typo3temp/ directory
                $fileWithUrlReplaced = $this->removeDirectoryFromUrlIfNeeded($fileWithUrlReplaced);
            }
            //TODO: add hook postReplacement

            //dont forget the quotes
            return '"'.$fileWithUrlReplaced.'"';

    }
    
    /**
     * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
     *
     * This hook is executed if the page contains *_INT objects! It's called always at the
     * last hook before the final output. This isn't the case if you are using a
     * static fileUrl cache like nc_staticfilecache.
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
