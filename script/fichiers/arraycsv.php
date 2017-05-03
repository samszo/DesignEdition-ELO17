<?php

$list = array (
   array('Adjib', 'ahamada.adjib@gmail.com', '21ans', 'CDNL'),
   array('92600', '93100', '94100')
);

$fp = fopen('file.csv', 'w');

foreach ($list as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);
?>
