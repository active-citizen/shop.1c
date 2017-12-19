<?php
    namespace Cache;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGShop; 
   
    class CCache extends \AGShop\CAGShop{
        
        var $sKey = '';
        var $sGroup = '';
        var $nExpires = 300;
        var $sFullKey = '';
        var $sBasePath = '';
        
        function __construct($sGroup, $sKey, $nExpires=''){
            $this->sGroup = $sGroup;
            $this->sKey = $sKey;
            $this->nExpires = $nExpires;
            $this->sFullKey = "$sGroup:$sKey";
            $this->sBasePath = $_SERVER["DOCUMENT_ROOT"]."/../tmp/CCache";
        }
        
        function get(){
            if(!$this->getKeyFilename())return false;
            $stat = stat($this->getKeyFilename());
            if($stat['mtime']+$this->nExpires < time()){
                unlink($this->getKeyFilename());
                return false;
            }
            return unserialize(file_get_contents($this->getKeyFilename()));
        }
        
        function set($sValue){
            $sData = serialize($sValue);
            if(!is_dir($this->getKeyPath()))$this->createPath();
            $fd = fopen($this->getKeyFilename(),"w");
            fwrite($fd, $sData);
            fclose($fd);
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
        
    }
   
