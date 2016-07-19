package Dialog;


# Вывод сообщения о фатальной ошибке
sub FatalError{
    my $error_text  =   shift;
    my $dont_unlock =   shift; # Не разблокировать после вывода текста об ошибке
    print "\n".("="x80)."\n";
    print <<EOFATALERROR
    FATAL ERROR:
    $error_text
EOFATALERROR
;
    print ("="x80);
    print "\n";
    resetLock() unless $dont_unlock;
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
    chomp($islock);
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
    --mirrations=<all|a:b>  Выполнение миграций (all - между двумя последними коммитами, a:b - от коммита "a" до коммита "b"). all работает только с ключом --sync, a:b - только без ключа -sync
    --sync                  Синхронизация кода и выполнение миграций между коммитами (если в удалённом репозитории появился новый коммит в заданной конфигом ветке)
    --make-report           Создать отчет об обновлении в указанной папке (делается по коду в репозитории)
    --show-quality          Показать по качеству кода (делается по коду в рабочей копии)
    --send-report           Послать отчёт об обновлении на прописанные в конфиге адреса (делается по коду в репозитории)
    --force-unlock          Принудительно снимать блокировку при выполнении (позволяет запустить более одного экземпляра или после аварийного завершения)     

EOHELP
;
    exit(0);
}


1;
