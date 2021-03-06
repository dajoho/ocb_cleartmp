<?php

class ocb_cleartmp_navigation extends ocb_cleartmp_navigation_parent
{
    /**
     * Change the full template as there is no block jet in the header.
     * 
     * @return string templatename
     */
    public function render()
    {
        $sTpl = parent::render();
        
        $this->_aViewData['prodmode'] = oxRegistry::getConfig()->isProductiveMode();
        
        if( 'header.tpl' == $sTpl )
        {
            return 'ocb_header.tpl';
        }
        else
        {
            return $sTpl;
        }
    }
    
    /**
     * Method that will be called from the frontend
     * and starts the clearing
     * 
     * @return null
     */
    public function cleartmp()
    {
        $oConf = oxRegistry::getConfig();
        $sShopId = $oConf->getShopId();
        
        $blDevMode = 0;
        if(false != $oConf->getRequestParameter('devmode'))
        {
            $blDevMode = $oConf->getRequestParameter('devmode');
        }
        $oConf->saveShopConfVar('bool', 'blDevMode', $blDevMode, $sShopId, 'module:ocb_cleartmp');
        
        $this->deleteFiles();
        
        return;
    }
    
    /**
     * Check wether the developermode is enabled or not
     * 
     * @return bool
     */
    public function isDevMode()
    {
        return oxRegistry::getConfig()->getShopConfVar('blDevMode',null,'module:ocb_cleartmp');
    }
    
    /**
     * Method to remove the files from the cache folder 
     * and trigger other options
     * depending on the given option
     * @return null
     */
    public function deleteFiles()
    {
        $oConf   = oxRegistry::getConfig();
        $option  = $oConf->getRequestParameter('clearoption');
        $sTmpDir = realpath($oConf->getShopConfVar('sCompileDir'));
        
        switch($option)
        {
            case 'smarty':
                $aFiles = glob($sTmpDir.'/smarty/*.php');
                break;
            case 'staticcache':
                $aFiles = glob($sTmpDir.'/ocb_cache/*.json');
                break;
            case 'language':
                oxRegistry::get('oxUtils')->resetLanguageCache();
                break;
            case 'database':
                $aFiles = glob($sTmpDir.'/*{_allfields_,i18n,_aLocal,allviews}*',GLOB_BRACE);
                break;
            case 'complete':
                $aFiles = glob($sTmpDir.'/*{.php,.txt}',GLOB_BRACE);
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/smarty/*.php'));
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/ocb_cache/*.json'));
                break;
            case 'seo':
                $aFiles = glob($sTmpDir.'/*seo.txt');
                break;
                break;
            case 'allMods':
                $this->removeAllModuleEntriesFromDb();
                $aFiles = glob($sTmpDir.'/*{.php,.txt}',GLOB_BRACE);
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/smarty/*.php'));
                $aFiles = array_merge($aFiles, glob($sTmpDir.'/ocb_cache/*.json'));
                return;
            case 'none':
            default:
                return;
        }
        
        if(count($aFiles) > 0)
        {
            foreach($aFiles as $file) {
                @unlink($file);
            }
        }
    }
   
    /**
     * Remove all module entries from the oxConfig table
     * Will only work if the developer mode is enabled.
     */
    protected function removeAllModuleEntriesFromDb()
    {
       if(false != oxRegistry::getConfig()->getRequestParameter('devmode'))
       {
       
            $sSql1 = 'DELETE FROM `oxconfig` WHERE `OXVARNAME` LIKE \'%aMod%\';'; 
            $sSql2 = 'DELETE FROM `oxconfig` WHERE `OXVARNAME` LIKE \'%aDisabledModules%\';';
            
            $res1 = oxDb::getDb()->execute($sSql1);
            $res2 = oxDb::getDb()->execute($sSql2);
       }
        
    }
    
}