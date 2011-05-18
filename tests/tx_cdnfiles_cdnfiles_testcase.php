<?php
require_once(t3lib_extMgm::extPath('cdnfiles').'class.tx_cdnfiles.php');
/* 
 * Config file for the tets
## YAML CDN configuration file
patterns:
  '(fileadmin/[^"]*\.css)':
    cdn_prefix: http://a0.twimg.com/a/1266605807/images/
    replace: true
  '(fileadmin/[^"]*\.js)':
    replace: false
files:
  "jquery-1.3.2.min.js":
   replace: true
   cdn_url: http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js
  "typo3temp/pics/3a5338c306.jpg":
    replace: false
 */
/**
 * [extensionConfiguration:tx_cdnfiles:private] => Array
        (
            [replace_fileadmin_directory] => 1
            [fileadmin_urlprefix] => http://d3n13n30hxhvxs.cloudfront.net/
            [replace_uploads_directory] => 1
            [uploads_urlprefix] => http://d3peu3j0iocvte.cloudfront.net/
            [replace_typo3temppics_directory] => 1
            [typo3temppics_urlprefix] => http://d17p6bjnrj0oj1.cloudfront.net/
            [advancedconfig_file] => /var/www/html/zetalab/typo3conf/cdnfiles.yml
            [fileadmin_regexp] => (fileadmin/[^"]*)
            [uploads_regexp] => (uploads/[^"]*)
            [typo3temppics_regexp] => (typo3temp/pics/[^"]*)
            [remove_fileadmin_directory] => 1
            [remove_uploads_directory] => 1
            [remove_typo3temp_directory] => 1
        )


 *
 */
/**
 * Description of tx_cdnfiles_cdnfiles
 *
 * @author falcifer
 */
class tx_cdnfiles_cdnfiles_testcase extends tx_phpunit_testcase {
    public function setUp(){
        // new replacer object tx_cdnfiles
        $this->cdnfilesObj = t3lib_div::makeInstance('tx_cdnfiles');

        //configure the object accessing private properties
        $property = new ReflectionProperty($this->cdnfilesObj,'extensionConfiguration');
        $property->setAccessible(TRUE);
        $config['replace_fileadmin_directory'] = '1';
        $config['fileadmin_urlprefix'] = 'http://d3n13n30hxhvxs.cloudfront.net/';
        $config['replace_uploads_directory'] = '1';
        $config['uploads_urlprefix'] = 'http://d3peu3j0iocvte.cloudfront.net/';
        $config['replace_typo3temppics_directory'] = '1';
        $config['typo3temppics_urlprefix'] = 'http://d17p6bjnrj0oj1.cloudfront.net/';
        $config['advancedconfig_file'] = '/var/www/html/zetalab/typo3conf/cdnfiles.yml';
        $config['fileadmin_regexp'] = '(fileadmin/[^"]*)';
        $config['uploads_regexp'] = '(uploads/[^"]*)';
        $config['typo3temppics_regexp'] = '(typo3temp/pics/[^"]*)';
        $config['remove_fileadmin_directory'] = '1';
        $config['remove_uploads_directory'] = '1';
        $config['remove_typo3temp_directory'] = '1';
        $property->setValue($this->cdnfilesObj, $config);

        //new object to manage the configuration files in YAML
        $specialConfigurationObj = t3lib_div::makeInstance("tx_cdnfiles_specialconfiguration",$this->extConfig['advancedconfig_file']);
        //load the YAML as inline string but not from a real file so:
        //you need configure the object accessing private properties
        $property = new ReflectionProperty($specialConfigurationObj,'config');
        $property->setAccessible(TRUE);
        $config = sfYamlInline::load("{ patterns: { '(fileadmin/[^\"]*\.css)': { cdn_prefix: 'http://a0.twimg.com/a/1266605807/images/', replace: true }, '(fileadmin/[^\"]*\.js)': { replace: false } }, files: { jquery-1.3.2.min.js: { replace: true, cdn_url: 'http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js' }, typo3temp/pics/3a5338c306.jpg: { replace: false } } }");
        $property->setValue($specialConfigurationObj, $config);

        // inject the 'specialConfigurationObj' inside tc_cdfiles replacer object
        $property = new ReflectionProperty($this->cdnfilesObj,'specialConfigurationObj');
        $property->setAccessible(TRUE);
        $property->setValue($this->cdnfilesObj, $specialConfigurationObj);
    }


    /**
     * Test the private method callbackReplacementFunction (requires PHP >= 5.3.2)
     * @dataProvider providerFiles
     */
    public function test_callbackReplacementFunction($workdir,$input,$output){
        $property = new ReflectionProperty($this->cdnfilesObj,'currentDirectory');
        $property->setAccessible(TRUE);
        $property->setValue($this->cdnfilesObj, $workdir);

        $method = new ReflectionMethod('tx_cdnfiles', 'callbackReplacementFunction');
        $method->setAccessible(TRUE);
        $this->assertEquals(
                $output,$method->invoke($this->cdnfilesObj,array($input))
                );
        
    }

    /**
     * @dataProvider providerContent
     */
    public function test_doReplacement($replaceFileadmin,$replaceUploads,$replaceTypo3temppics,$content,$result){
        $property = new ReflectionProperty($this->cdnfilesObj,'extensionConfiguration');
        $property->setAccessible(TRUE);
        $config = $property->getValue($this->cdnfilesObj);
        $config['replace_fileadmin_directory'] = $replaceFileadmin;
        $config['replace_uploads_directory'] = $replaceUploads;
        $config['replace_typo3temppics_directory'] = $replaceTypo3temppics;
        $property->setValue($this->cdnfilesObj, $config);

        $this->assertEquals($result,$this->cdnfilesObj->doReplacement($content));
    }

    public function providerContent(){
        return array(
          array(
              0,
              0,
              0,
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              ',
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              '
          ),
          array(
              0,
              0,
              1,
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              ',
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="http://d17p6bjnrj0oj1.cloudfront.net/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              '
          ),
          array(
              0,
              1,
              0,
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              ',
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="http://d3peu3j0iocvte.cloudfront.net/3a5338cfffff.jpg"></li>
              '
          ),
          array(
              1,
              0,
              0,
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="fileadmin/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              ',
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="typo3temp/pics/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="http://d3n13n30hxhvxs.cloudfront.net/3a5338cfffff.jpg"></li>
               <li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol"><img border="0" width="600" height="375" alt="" src="uploads/3a5338cfffff.jpg"></li>
              '
          ),
          array(
              1,
              1,
              1,
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol">typo3temp/pics/3a5338c306.jpg
              ',
              '<li style="width: 600px;" class="csc-textpic-image csc-textpic-firstcol csc-textpic-lastcol">typo3temp/pics/3a5338c306.jpg
              '
          ),
        );
    }

    public function providerFiles(){

        return array(
          array('','','""'),
          array('typo3temppics','typo3temp/pics/d58a9b6619.jpg','"http://d17p6bjnrj0oj1.cloudfront.net/pics/d58a9b6619.jpg"'),
          array('fileadmin','fileadmin/jquery-1.3.2.min.js','"http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"'),
          array('fileadmin','fileadmin/something.js','"fileadmin/something.js"'),
          array('typo3temppics','typo3temp/pics/3a5338c306.jpg','"typo3temp/pics/3a5338c306.jpg"'),
          array('fileadmin','fileadmin/css/content.css','"http://a0.twimg.com/a/1266605807/images/css/content.css"'),
          array('uploads','uploads/d58a9b6619.jpg','"http://d3peu3j0iocvte.cloudfront.net/d58a9b6619.jpg"'),
          array('fileadmin','fileadmin/d58a9b6619.jpg','"http://d3n13n30hxhvxs.cloudfront.net/d58a9b6619.jpg"'),

        );
    }
}
?>
