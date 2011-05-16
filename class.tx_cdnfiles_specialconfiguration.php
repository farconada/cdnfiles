<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(PATH_t3lib . 'interfaces/interface.t3lib_singleton.php');
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
class tx_cdnfiles_specialconfiguration implements t3lib_Singleton {

    /**
     *
     * @var array the YAML file in array format
     */
    private $config = array();

    /**
     *
     * @param string $configFile absulet path of the YAML file
     */
    public function __construct($configFile){
        try{
            $this->config = sfYaml::load($configFile);
        }catch(InvalidArgumentException $e){
            t3lib_div::devLog($e->getMessage(),"cdnfiles");
        }

    }

    /**
     * Do I have any special config for this file?
     * @param string $originalFile
     * @return string|null if have a special config i should return the file refernce
     *  proccessed, If the file refernce proccessed is equal as filerefernce is
     *  because i have written in my config which that file should be proccessed
     *  the function returns null if has nothing about that file
     */
    public function getFile($originalFile){
        //lets look in the files section
        /**
         * Files section has priority over patterns section because
         * patterns are more general than files
         * The order is important cause it works in short-circuit mode
         */
        foreach ($this->config['files'] as $file => $fileConfig){
            //always testing case insensitive
            if(preg_match("|".$file."|i", $originalFile)){
                //There is a config for this file
                if($fileConfig['replace']){
                    //then i should have an URL
                    return $fileConfig['cdn_url'];
                }else{
                    // please dont touch my file it shouldnt be replaced
                    return $originalFile;
                }

            }
        }


        //lets look in the patterns section
        foreach ($this->config['patterns'] as $pattern => $patternConfig){
            if(preg_match("|".$pattern."|i", $originalFile)){
                if($patternConfig['replace']){
                    return $patternConfig['cdn_prefix'].$originalFile;
                }else{
                    return $originalFile;
                }

            }
        }

        //I cant find any config for this file, lets return null
        return null;
        
    }
}

?>
