package Dialog;


# Вывод сообщения о фатальной ошибке
sub FatalError{
    my $error_text  =   shift;
    print "\n".("="x80)."\n";
    print <<EOFATALERROR
    FATAL ERROR:
    $error_text
EOFATALERROR
;
    print ("="x80);
    print "\n";
    resetLock();
    exit(1);
}

# Постановка блокировки
sub setLock{
    open(A,">locks/update");
    print A "1";
    close(A);
}

# проверка блокировки
sub isLock{
    open(A,"locks/update");
    my $islock = join ("",<A>);
    close(A);
    return $islock;
}

# Снятие блокировки
sub resetLock{
    open(A,">locks/update");
    print A "";
    close(A);
}

# Вывод справки
sub ShowHelp{
    print <<EOHELP
Установщик магазина поощрений "Активный гражданин"

    --help                  помощь
    --verbose               болтливый режим
    --config=ФАЙЛ           задать конфиг для установки вручную (по умолчанию config.ini)
    --show-template-config  показать файл конфигурации с опциями по умолчанию   
    --install-bitrix        установить Битрикс
    --unittests             Выполнение автотестов
    --sync                  Синхронизация кода и выполнение миграций между коммитами (если в удалённом репозитории появился новый коммит в заданной конфигом ветке)

EOHELP
;
    exit(0);
}


1;
