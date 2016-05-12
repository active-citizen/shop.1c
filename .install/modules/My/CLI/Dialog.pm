package CLI::Dialog;

# Интерактивное заполнение массива опций
sub interactive{
    my $opts = shift or ();
    my $input = "";
    foreach my $key(keys %{$opts}){
	print $key." [".$opts->{$key}."]:";
	$input = <>;
	chomp($input);
	$opts{$key} = $input if $input;
    }
    
    return $opts;
}

# Вывод справки
sub show_help{
print <<EOHELP
Установщик магазина поощрений "Активный гражданин"

    --verbose       болтливый режим
    --help          помощь
    --config=ФАЙЛ   задать конфиг для установки вручную (по умолчанию config.ini)

EOHELP
}


1;
