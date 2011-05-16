<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

if (TYPO3_MODE == 'FE') {
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] =
        'EXT:cdnfiles/class.tx_cdnfiles.php:tx_cdnfiles->contentPostProcOutput';
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] =
        'EXT:cdnfiles/class.tx_cdnfiles.php:tx_cdnfiles->contentPostProcAll';
}

?>
