<?php
    namespace Lock;
    require_once(realpath(__DIR__."/..")."/CAGShop.class.php");
    use AGShop; 
   
    class CLock extends \AGShop\CAGShop{
        private $sLockFile = "";
        private $nFolderDepth = 4;
        private $nExpires = 0;
        private $sBasePath = '';
        private $arPath = [];
        
        function __construct(
            $sLockType,     //!< Тип объекта блокировки (строка)
            $sLockId,       //!< ID блокировки (целое число)
            $nLockExpire    //!< Время блокировки (секунд)
        ){
            parent::__construct();
            $this->sBasePath = realpath($_SERVER["DOCUMENT_ROOT"]."/..")."/locks";
            $this->arPath[] = $sLockType;
            $sLockId = intval($sLockId);
            $this->nExpires = intval($nLockExpire);
            
            $sId = sprintf("%0".$this->nFolderDepth."d",$sLockId);
            for($i=0;$i<$this->nFolderDepth;$i++)
                $this->arPath[] = substr($sId, $i, 1);
            
            $this->sLockFile = 
                $this->sBasePath."/"
                .implode("/",$this->arPath)
                ."/$sLockId.lock";
            
            $this->createPath();
        }
        
        function isLocked(){
            if(!file_exists($this->sLockFile))return false;
            $stat = stat($this->sLockFile);
            if(time()-$stat['mtime'] > $this->nExpires){
                unlink($this->sLockFile);
                return false;
            }
            return true;
        }
        
        function lock($sData = ''){
            if(!$fd = fopen($this->sLockFile,"w")){
                $this->addError("Не удалось создать блокировку");
                return false;
            }
            fclose($fd);
            return true;
        }
        
        function reset(){
            unlink($this->sLockFile);
            return true;
        }
        
        private function createPath(){
            $sPath = $this->sBasePath;
            foreach($this->arPath as $sFolder){
                $sPath .= "/".$sFolder;
                if(!is_dir($sPath))mkdir($sPath);
            }
        }
        
    }
   
