    <ul class="nav nav-tabs partners-menu">
        <li class="<?
        if(preg_match('#^/partners/orders/#',$_SERVER["REQUEST_URI"]))echo
        "active";?>">
            <a href="/partners/orders/">Список заказов</a>
        </li>
        <li class="<?
        if(preg_match("#^/partners/download/#",$_SERVER["REQUEST_URI"]))echo
        "active";?>">
            <a href="/partners/download/">Выгрузка заказов</a>
        </li>
        <li class="<?
        if(preg_match("#^/partners/help/#",$_SERVER["REQUEST_URI"]))echo
        "active";?>">
            <a href="/partners/help/">Помощь</a>
        </li>
        <? if($USER->IsAdmin()):?>
            <li class="<?
            if(preg_match("#^/partners/users/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/users/">Пользователи</a>
            </li>
            <li class="<?
            if(preg_match("#^/partners/reports/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/reports/">Отчеты</a>
            </li>
            <li class="<?
            if(preg_match("#^/partners/dump/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/dump/">Перенос данных</a>
            </li>
            <li class="<?
            if(preg_match("#^/partners/logs/#",$_SERVER["REQUEST_URI"]))echo
            "active";?>">
                <a href="/partners/logs/">Логи обмена</a>
            </li>
        <? endif ?>
        <li style="float:right;">
            <a href="/partners/orders/?logout=yes">Выход</a>
        </li>
    </ul>

