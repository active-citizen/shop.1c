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
        <? endif ?>
    </ul>

