<?php
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CReport/CReport.class.php"); 
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CReport/CJiraReport.class.php"); 
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CReport/CBuildReport.class.php"); 
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CReport/CUnittestsReport.class.php"); 
    require_once($_SERVER["DOCUMENT_ROOT"]."/local/libs/classes/CAGShop/CReport/CInfoReport.class.php"); 

    use \AGShop\Report as Report;

    $objReport = new \Report\CReport;

    $objReport->add(new \Report\CBuildReport);
    $objReport->add(new \Report\CJiraReport);
    $objReport->add(new \Report\CUnittestsReport);
    $objReport->add(new \Report\CInfoReport);


    if(!$objReport->build()){
        echo "<pre>";
        print_r($objReport->getErrors());
        echo "</pre>";
    }
    if(!$sData = $objReport->render()){
        echo "<pre>";
        print_r($objReport->getErrors());
        echo "</pre>";
    }

    $objReport->setMailSubject("AG Nightly - shop");

    $objReport->addRecepient("SklyarovDV@mos.ru"); 
    $objReport->send();

    $objReport->addRecepient("petrovvv@em.mos.ru"); 
    $objReport->send();

    $objReport->addRecepient("andrey.inyutsin@altarix.ru"); 
    $objReport->send();

    echo $sData;
