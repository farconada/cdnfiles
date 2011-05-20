<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once t3lib_extMgm::extPath('cdnfiles').'class.tx_cdnfiles_specialconfiguration_interface.php';
require_once t3lib_extMgm::extPath('cdnfiles').'lib/sfYaml.php';

/**
 * Description of classtx_cdnfiles_configfile:
 *  This class proccess the YAML configuration file and proccess the file references
 *  against it
 *
 * This class should be loaded one time only, so it is a singleton
 *
 * @author Fernando Arconada fernando.arconada@gmail.com
 */
class tx_cdnfiles_specialconfiguration implements tx_cdnfiles_specialconfiguration_interface {

    /**
     *
     * @var array the YAML file in array format
     */
    private $config = array();

    /**
     * @param string $configFilePath absulet path of the YAML file
     */
    public function __construct($configFilePath){
        $this->config = $this->loadYamlAsArray($configFilePath);
    }

    /**
     * @param  $configFilePath string sets the configuration file in YAML format and loads it into the config
     * @return void
     */
    public function setConfigFile($configFilePath) {
        $this->config = $this->loadYamlAsArray($configFilePath);
    }

    /**
     * converts a YAML file into array
     * @param  $yamlFilePath
     * @return array
     */
    private function loadYamlAsArray($yamlFilePath) {
        $arrayResult = array();
        try{
            $arrayResult = sfYaml::load($yamlFilePath);
        }catch(InvalidArgumentException $e){
            t3lib_div::devLog($e->getMessage(),"cdnfiles");
        }
        return $arrayResult;
    }
    /**
     * Do I have any special config for this file?
     * @param string $fileToReplaced
     * @return string|null if have a special config i should return the file refernce
     *  proccessed, If the file refernce proccessed is equal as filerefernce is
     *  because i have written in my config which that file should be proccessed
     *  the function returns null if has nothing about that file
     */
    public function getFileUrlReplaced($fileToReplaced){
        //lets look in the files section
        /**
         * Files section has priority over patterns section because
         * patterns are more general than files
         * The order is important cause it works in short-circuit mode
         */
        foreach ($this->config['files'] as $file => $fileReplacementConfig){
            //always testing case insensitive
            if(preg_match("|".$file."|i", $fileToReplaced)){
                //There is a config for this file
                if($fileReplacementConfig['replace']){
                    //then i should have an URL
                    return $fileReplacementConfig['cdn_url'];
                }else{
                    // please dont touch my file it shouldnt be replaced
                    return $fileToReplaced;
                }

            }
        }


        //lets look in the patterns section
        foreach ($this->config['patterns'] as $pattern => $patternReplacementConfig){
            if(preg_match("|".$pattern."|i", $fileToReplaced)){
                if($patternReplacementConfig['replace']){
                    return $patternReplacementConfig['cdn_prefix'].$fileToReplaced;
                }else{
                    return $fileToReplaced;
                }

            }
        }

        //I cant find any config for this file, lets return null
        return null;
        
    }
}

?>
