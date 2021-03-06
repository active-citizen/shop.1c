<?php
    namespace Cache;
    /**
        Класс для кеширования
    */
    class CCache {
        
        var $sKey = '';
        var $sGroup = '';
        var $nExpires = 300;
        var $sFullKey = '';
        private $objMemcached = null;
        private $sBasePath = '';    //!< Киаталог для хранения кэша в файловой
        // системе
        
        /**
            @param $sGroup - группа ключей кеширования
            @param $sKey - ключ кеширования
            @param $nExpires - время жизни кеша (секунд)
        */
        function __construct($sGroup, $sKey, $nExpires=''){
            $this->sGroup = $sGroup;
            $this->sKey = $sKey;
            if($nExpires!='')$this->nExpires = $nExpires;
            $this->sFullKey = "$sGroup:$sKey";
            $this->sBasePath = $_SERVER["DOCUMENT_ROOT"]."/../tmp/CCache";
            // Проверяем доступность соединения с memcached
            $arConfig = $this->__getBitrixConfig();
            if(!isset($arConfig["cache"]["value"]["memcache"]["host"]))
                return false;
            if(!isset($arConfig["cache"]["value"]["memcache"]["port"]))
                return false;
            $nPort = $arConfig["cache"]["value"]["memcache"]["port"];
            $sHost = preg_replace(
                "#^unix://(.*)$#",
                "$1",
                $arConfig["cache"]["value"]["memcache"]["host"]
            );
            if(!class_exists("Memcached"))return false;
            $this->objMemcached = new \Memcached();
            if(!$this->objMemcached->addServer($sHost, $nPort))return false;
            $this->bMemcached = true;
            return true;
        }
        
        function get(){
            if($this->objMemcached && $objData =  $this->__memcachedGet())
                return $objData;
            if(!$this->getKeyFilename())return false;
            $stat = stat($this->getKeyFilename());
            if($stat['mtime']+$this->nExpires < time()){
                unlink($this->getKeyFilename());
                return false;
            }
            return unserialize(file_get_contents($this->getKeyFilename()));
        }
        
        function set($sValue){
            if(
                $this->objMemcached && $objData = $this->__memcachedSet($sValue)
            )return $sValue;
            
            $sData = serialize($sValue);
            if(!is_dir($this->getKeyPath()))$this->createPath();
            $fd = fopen($this->getKeyFilename(),"w");
            fwrite($fd, $sData);
            fclose($fd);
            return $sValue;
        }
        /**
            Очистка ключа
        */
        function clear(){
            if(!$this->objMemcached)
                unlink($this->getKeyFilename());
            else
                return $this->objMemcached->delete($this->sFullKey);
                
        }
        function getAll(){
            return $this->objMemcached->getAllKeys();
        }
        /**
            Очистка текущей группы ключей
        */
        function clearAll(
            $bOnlyGroup = true // Очищать толко заданную в конструкторе группу
        ){
            if(!$this->objMemcached):
                rrmdir($this->getKeyPath());
                return false;
            else:
                $arKeys = $this->getAll();
                $arDeleteKeys = [];
                foreach($arKeys as $sKey){
                    $tmp = explode(":", $sKey);
                    if($bOnlyGroup && $tmp[0]==$this->sGroup)
                        $arDeleteKeys[] = $sKey;
                    elseif(!$bOnlyGroup)
                        $arDeleteKeys[] = $sKey;
                    
                }
                return $this->objMemcached->deleteMulti($arDeleteKeys);
            endif;
        }
        
        private function createPath(){
            mkdir($this->getKeyPath(),0700,true);
        }
        
        private function getKeyPath(){
            $sPath = $this->sBasePath."/".$this->sGroup;
            return $sPath;
        }
        
        private function getKeyFilename(){
            return $this->getKeyPath()."/".$this->sKey.".cache";
        }
        /**
            Получаем значение ключа из memcached
        */
        private function __memcachedGet(){
            return unserialize($this->objMemcached->get($this->sFullKey));
        }
        /**
            Получаем значение ключа из memcached
        */
        private function __memcachedSet($objValue){
            return $this->objMemcached->set(
                $this->sFullKey,
                serialize($objValue),
                $this->nExpires
            );
        }
        private function __getBitrixConfig(){
            $sConfigFilename =
               $_SERVER["DOCUMENT_ROOT"]."/bitrix/.settings_extra.php";
            if(!file_exists($sConfigFilename)){
                $this->bMemcached = false;
                return false;
            }
            return include($sConfigFilename);
         }
        
    }
    
function rrmdir($src) {
    $dir = opendir($src);
    while($file = readdir($dir)) {
        if ( $file == '.' || $file != '..' )continue; 
        
        $full = $src . '/' . $file;
        if ( is_dir($full) ) {
            rrmdir($full);
        }
        else {
            unlink($full);
        }
        
    }
    closedir($dir);
    rmdir($src);
}    
