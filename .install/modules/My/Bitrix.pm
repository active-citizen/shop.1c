package Bitrix;

    sub new{
        
        my ($class, $conf, $verbose) = @_;
        
        my $self = {
    	    "varbose"	=> $verbose,
    	    "conf"	=> $conf
        };
        bless $self,$class;
        
        return $self;
    }

    sub Install{
    
    }

1;
