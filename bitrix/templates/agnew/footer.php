<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

    </div>
    <!-- -end of wrap -->
    
        <script src="<?= SITE_TEMPLATE_PATH?>/js/jquery.min.js"></script>
        <script src="<?= SITE_TEMPLATE_PATH?>/js/jquery-ui.js"></script>
        <script src="<?= SITE_TEMPLATE_PATH?>/js/fotorama.js"></script>
        <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH?>/scripts.js"></script>
        <script type="text/javascript" src="<?= SITE_TEMPLATE_PATH?>/js/jquery.fancybox.js"></script>
        <? if(!CUser::IsAuthorized()):?>
        <? 
            require_once(realpath(dirname(__FILE__)."/../../../.integration/secret.inc.php"));
            $url = $AG_KEYS["uat"]["url"];
            if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru')$url = $AG_KEYS["prod"]["url"];
        ?>
        <script src="<?php echo $url; ?>"></script>
        <? endif?>
    </body>
</html>
