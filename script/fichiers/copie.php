<?php

$srcfile='C:\Users\adjib\Documents\Adjib\Cours\Samuel Szoniecky\Stage\fichiers\img\fichier.txt';
$dstfile='C:\Users\adjib\Documents\Adjib\Cours\Samuel Szoniecky\Stage\fichiers';
mkdir(dirname($dstfile), 0777, true);
copy($srcfile, $dstfile);

?>
