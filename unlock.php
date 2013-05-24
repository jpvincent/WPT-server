<?php 
//Vide les dossiers temporaires de WPT et Wptmonitor
function clearFolder($folder)
{
        $dossier=opendir($folder);
        while ($File = readdir($dossier))
        {
                if ($File != "." && $File != "..")
                {
                        $Vidage= $folder.$File;
                        unlink($Vidage);
                        echo $folder." est maintenant vide !";
                }
        }
        closedir($dossier);
}
//Dossiers à vider :
clearFolder("tmp/");
clearFolder("work/jobs/");
clearFolder("wptmonitor/template_c/");
clearFolder("wptmonitor/graph/cache/");