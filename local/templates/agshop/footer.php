<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

        <!-- Footer {{{-->
        <footer class="ag-shop-footer">
          <div class="ag-shop-footer__links">
            <a class="ag-shop-footer__link" href="/rules/hiw/">О проекте</a>
            <a class="ag-shop-footer__link" href="/rules/faq/">Часто задаваемые вопросы</a>
            <a class="ag-shop-footer__link" href="#">Обратная связь</a>
          </div>
          <div class="ag-shop-footer__copy"><small class="ag-shop-footer__copy-text">&copy; 2016, Активный Гражданин. <br class="hide-on-desktop">Все права защищены. <a href="#">Публичная оферта</a></small></div>
        </footer>
        <!-- }}} Footer-->
      </div>
    </div>

        <? if(!CUser::IsAuthorized()):?>
        <? 
            require_once(realpath(dirname(__FILE__)."/../../..")."/.integration/secret.inc.php");
            $url = $AG_KEYS["uat"]["url"];
            if($_SERVER["HTTP_HOST"]=='shop.ag.mos.ru')$url = $AG_KEYS["prod"]["url"];
        ?>
        <script src="<?php echo $url; ?>"></script>
        <? endif?>


  </body>
</html>
