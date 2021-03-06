    <ul class="nav nav-tabs partners-menu">
        <li class="<?
        if(preg_match('#^/partners/orders/#',$_SERVER["REQUEST_URI"]))echo
        "active";?>">
            <a href="/partners/orders/">
                <span class="glyphicon glyphicon-gift"></span>
                Список заказов
            </a>
        </li>
        <li class="<?
        if(preg_match("#^/partners/download/#",$_SERVER["REQUEST_URI"]))echo
        "active";?>">
            <a href="/partners/download/">
                <span class="glyphicon glyphicon-download-alt"></span>
                Выгрузка заказов
            </a>
        </li>
        <li class="<?
        if(preg_match("#^/partners/help/#",$_SERVER["REQUEST_URI"]))echo
        "active";?>">
            <a href="/partners/help/">
                <span class="glyphicon glyphicon-info-sign"></span>
                Помощь
            </a>
        </li>
        <? if(
        $USER->IsAdmin() 
        || 
        in_array(SHOP_ADMIN, $USER->GetUserGroupArray())
        ):?>
            <? if($USER->IsAdmin()):?>
            <li class="<?
            if(preg_match("#^/partners/users/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/users/">
                <span class="glyphicon glyphicon-user"></span>
                    Пользователи
                </a>
            </li>
            <? endif ?>
            <li class="<?
            if(preg_match("#^/partners/reports/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/reports/">
                <span class="glyphicon glyphicon-list-alt"></span>
                    Отчеты
                </a>
            </li>
            <!--
            <li class="<?
            if(preg_match("#^/partners/dump/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/dump/">
                <span class="glyphicon glyphicon-hdd"></span>
                     Перенос данных
                </a>
            </li>
            -->
            <li class="<?
            if(preg_match("#^/partners/logs/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/logs/">
                <span class="glyphicon glyphicon-eye-open"></span>
                    Логи обмена
                </a>
            </li>
            <li class="<?
            if(preg_match("#^/partners/mails/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/mails/">
                <span class="glyphicon glyphicon-envelope"></span>
                    Письма
                </a>
            </li>
            <li class="<?
            if(preg_match("#^/partners/actions/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/actions/">
                <span class="glyphicon glyphicon-export"></span>
                    ЗНИ
                </a>
            </li>
            <? if($USER->IsAdmin()):?>
            <li class="<?
            if(preg_match("#^/partners/settings/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/settings/">
                <span class="glyphicon glyphicon-wrench"></span>
                    Настройки
                </a>
            </li>
            <? /* ?>
            <li class="<?
            if(preg_match("#^/partners/auctions/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/auctions/">
                <span class="glyphicon glyphicon-bullhorn"></span>
                    Аукционы
                </a>
            </li>
            <? */?>
            <li class="<?
            if(preg_match("#^/partners/infotech/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/infotech/">
                <span class="glyphicon glyphicon-th"></span>
                    Инфотех
                </a>
            </li>
            <? endif ?>
        <? endif ?>
        <li style="float:right;">
            <a href="/partners/orders/?logout=yes">Выход</a>
        </li>
    </ul>

