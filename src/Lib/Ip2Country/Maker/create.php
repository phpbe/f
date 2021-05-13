<?php
exit; // Remove this line

$lines = array();

$handle = fopen('ip-to-country.csv', 'r');
while (!feof($handle)) {
    $lines[] = fgets($handle, 4096);
}
fclose($handle);

echo $line_count = count($lines);

$countries = array();

$db = fopen('ip.dat', 'w');
foreach($lines as $line)
{
    $line = explode(',', $line);
    foreach($line as &$x)
    {
        $x = trim($x);
        $x = trim($x, '"');
    }
    
    fwrite($db, pack('L', $line[0]*1));
    fwrite($db, pack('L', $line[1]*1));
    fwrite($db, pack('A*', $line[2]));
    
    $countries[$line[2]] = array($line[3], $line[4]);
}
fclose($db);

$country_string = '';
foreach($countries as $key=>$country)
{
    $country_string .= '\''.$key.'\'=>\''.$country[1].'\','."\r\n";
}

file_put_contents('countries.txt', $country_string);

echo 'created!';
