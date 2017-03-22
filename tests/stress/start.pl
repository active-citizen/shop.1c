#!/usr/bin/perl
#
#   Stress testing script
#   
#   Usage:
#

use libs::conf;

for($i=0;$i<$conf::POW;$i++){fork();}

opendir($dd,"cases/".$conf::CASENAME);
@case_steps = ();
AAA:while($filename = readdir($dd)){
    next AAA unless -f "cases/".$conf::CASENAME."/".$filename;
    next AAA unless $filename=~m/^.*\.txt$/;
    push(@case_steps, $filename);
}
sort(@case_steps);

for($i=0;$i<$conf::ITERATIONS;$i++){
    sleep($conf::MAXPAUSE + rand($conf::MAXPAUSE-$conf::MINPAUSE));
    foreach $filename(@case_steps){
        sleep($conf::MAXDELAY + rand($conf::MAXDELAY-$conf::MINDELAY));
        open(A, "cases/".$conf::CASENAME."/".$filename);
        $command = join("\n", <A>);
        close(A);
        $command=~s/\{\{BASEURL\}\}/$conf::BASEURL/gi;
        chomp($command);
        $output_filename = "results/".$conf::CASENAME.'.'.$filename.'.'.time().'.'.$$.".log";
        $command = 'wget -d '
            .'-o '.$output_filename
            .' -O '.$output_filename.".html "
            .$conf::OPTIONS
            .' '.$command;
        print "PID=".$$.';'.$conf::CASENAME.";".$filename.";\n";
        `$command`;
    }
}






