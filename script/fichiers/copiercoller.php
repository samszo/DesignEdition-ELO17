<?php
function (CopierDossier)
{
mkdir("/img");
//mkdir("$pseudo/base/");
//mkdir("$pseudo/base/LE_NOM_DE_TON_DOSSIER"); //pour chaque sous-dossier

$file = "test.txt"; //ici, tu mets les noms de tes fichiers
$file2 = "fichier.txt"; //etc.... (pour chaque fichier)

copy($file, "fichiers/test.txt");
copy($file2, "fichiers/fichier.txt"); //etc... (pour chaque fichier)



}
?>
