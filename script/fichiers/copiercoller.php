<?php
function (CopierDossier)
{
mkdir("/img");


$file = "test.txt";
$file2 = "fichier.txt";

copy($file, "fichiers/test.txt");
copy($file2, "fichiers/fichier.txt");



}
?>
