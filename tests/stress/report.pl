#!/usr/bin/perl

use libs::conf;

opendir($dd,"cases/".$conf::CASENAME);
@case_steps = ();
AAA:while($filename = readdir($dd)){
    next AAA unless -f "cases/".$conf::CASENAME."/".$filename;
    next AAA unless $filename=~m/^.*\.txt$/;
    push(@case_steps, $filename);
}
sort(@case_steps);


$results = {
    "http_errors"   =>  {},
    "times"         =>  {},
    "sizes"         =>  {},
    "requests"      =>  {}
};

opendir($dd,"results");
@timestamps = ();
BBB:while($filename = readdir($dd)){
    next BBB unless $filename=~m/^.*?\.(.*?)\.(.*?)\.(.*?)\..*?\.log$/i;
    $step_filename = $1.".".$2;
    $timestamp = $3;
    $timestamp = datetime($timestamp);
    push(@timestamps,$timestamp);
}
closedir($dd);
sort(@timestamps);

foreach $type(keys %{$results}){
    foreach $timestamp(@timestamps){
        $result->{$type}->{$timestamp} = {};
        foreach $case_step(@case_steps){$result->{$type}->{$timestamp}->{$case_step} = " ";}
    }
}

opendir($dd,"results");
while($filename = readdir($dd)){
    if($filename=~m/^.*?\.(.*?)\.(.*?)\.(.*?)\..*?\.log$/i){
        $step_filename = $1.".".$2;
        $timestamp = $3;
        $timestamp = datetime($timestamp);
        open(C, "results/$filename");
        $content = join("\n",<C>);
        close(C);
        $results->{"http_errors"}->{$timestamp}->{$step_filename} = $1
            if $content=~m|HTTP/1\..\s+(\d+)|m;
        $results->{"requests"}->{$timestamp}->{$step_filename} += 1;
        if($content=~m|\[(\d+)[^\d]|m){
            $results->{"sizes"}->{$timestamp}->{$step_filename} = $1
        }
        else{
            $results->{"sizes"}->{$timestamp}->{$step_filename} = 0
        }

        $results->{"times"}->{$timestamp}->{$step_filename} += $1 
            if $content=~m|time=\[(\d+)\]|m;
         
    }
}
closedir($dd);

foreach $timestamp(sort @timestamps){
    foreach $step(sort @case_steps){
        $a = int($results->{"times"}->{$timestamp}->{$step});
        $b = int($results->{"requests"}->{$timestamp}->{$step});
        $results->{"times"}->{$timestamp}->{$step.".f"} = $a/$b;
    }
}


foreach $report_type(sort keys %{$results}){
    print "\n".("="x30);
    print " $report_type ";
    print ("="x30)."\n";

    print "\n+------------------+";
    print "------------+" foreach @case_steps;

    print "\n|    Datetime      |";
    print "  $_   |" foreach sort @case_steps;

    print "\n+------------------+";
    print "------------+" foreach @case_steps;
 
    foreach $timestamp(sort keys %{$results->{$report_type}}){
        #date = datetime($timestamp);
        $date = $timestamp;
        print "\n| $date |";
        foreach $key(sort @case_steps){
            print sprintf(
                "% 12d",
                $results->{$report_type}->{$timestamp}->{$key.".f"}
                ?
                $results->{$report_type}->{$timestamp}->{$key.".f"}
                :
                $results->{$report_type}->{$timestamp}->{$key}
            )."|";
        }
    }
    print "\n+------------------+";
    print "------------+" foreach @case_steps;

    print "\n";
}

sub datetime{
    $timestamp = shift;
    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) =
        localtime($timestamp);
    $sec = sprintf("%02d",$sec);
    $min = sprintf("%02d",$min);
    $hour = sprintf("%02d",$hour);
    $mday = sprintf("%02d",$mday);
    $mon = sprintf("%02d",$mon+1);
    $year = sprintf("%04d",$year+1900);
     
    return "$year-$mon-$mday $hour:$min";    
}
