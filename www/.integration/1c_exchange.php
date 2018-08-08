<?
if(
    $_SERVER["HTTP_HOST"]=='shop.ag.mos.ru'
    ||
    $_SERVER["HTTP_HOST"]=='dev.shop.ag.mos.ru'
)include("logger.inc.php");

/*
    Custom catalog importer
*/
if(
    isset($_GET["type"]) 
    && isset($_GET["mode"]) 
    && isset($_GET["filename"])
    && $_GET["type"]=='catalog' 
    && $_GET["mode"]=="import"
    && !file_exists($_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$_GET["filename"])
){
    $sZipFileFolder = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/";
    $sZipFilename = '';
    $dd = opendir($sZipFileFolder);
    while($filename = readdir($dd))
        if(preg_match("#^.*\.zip$#",$filename)){
            $sZipFilename = $filename;
            break;
        }
    if(!trim($sZipFilename)){
        echo "failure\n";
        echo "Zip archive not found";
        die;
    }
    $zip = new ZipArchive;
    if(!$zip->open($sZipFileFolder."/".$sZipFilename)){
        echo "failure\n";
        echo "Cant open zip archive $sZipFilename";
        die;
    }
    if(!$zip->extractTo($sZipFileFolder)){
        echo "failure\n";
        echo "Cant extract zip archive $sZipFilename";
        die;
    }

//    echo "failure\n";
//    echo "file ".$_GET["filename"]." not exists";
//    die;
}

if(
    isset($_GET["type"]) 
    && isset($_GET["mode"]) 
    && isset($_GET["filename"])
    && $_GET["type"]=='catalog' 
    && $_GET["mode"]=="import"
    && !preg_match("#\.\.#", $_GET["filename"])
    && file_exists($_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/".$_GET["filename"])
){
    $filename = LOGGER_INPUT_FILENAME;
    $sFolder = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_catalog/";
    $oldFilename = $sFolder.$_GET["filename"];
    $newFilename = $filename.".".$_GET["filename"];
    copy($oldFilename,$newFilename);

    include($_SERVER["DOCUMENT_ROOT"]."/.integration/1c_catalog.ajax.php");
    die;
}

/*
    Custom Bitrix -> 1C orders export
*/
if(
    isset($_GET["type"]) 
    && isset($_GET["mode"])
    && $_GET["type"]=='sale' 
    && $_GET["mode"]=="query"
){
    include("order_export.ajax.php");
    die;
}

/*
    Custom success operation
*/
if(
    isset($_GET["type"]) 
    && isset($_GET["mode"])
    && $_GET["type"]=='sale' 
    && $_GET["mode"]=="success"
){
    include("order_success.ajax.php");
    die;
}

    
/*
    Custom 1C -> Bitrix import
*/
if(
    isset($_GET["type"]) 
    && isset($_GET["mode"]) 
    && isset($_GET["filename"])
    && $_GET["type"]=='sale' 
    && $_GET["mode"]=="file" 
    && $_GET["filename"]
){
   if(!file_exists($filename = $_SERVER["DOCUMENT_ROOT"]."/upload/1c_exchange/".$_GET["filename"])){
        $fd = fopen("php://input", "r");
        $fd2= fopen($filename,"w");
        while(!feof($fd))fwrite($fd2, fread($fd,1000));
        fclose($fd2);
        fclose($fd);
    }

    include("order_import.ajax.php");
    die;
}

// Default exchange script
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/1c_exchange.php");
?>
