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
    exit(1);
}

# Вывод справки
sub ShowHelp{
    print <<EOHELP
Установщик магазина поощрений "Активный гражданин"

    --help                  помощь
    --verbose               болтливый режим
    --config=ФАЙЛ           задать конфиг для установки вручную (по умолчанию config.ini)
    --show-template-config  показать файл конфигурации с опциями по умолчанию   

EOHELP
;
    exit(0);
}


1;
