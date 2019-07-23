<?php //Get PHP configuration
////////////////////////////////Attention d'enregistrer ce fichier sous notePad avec un encodage : UTF-8 (sans BOM)////////////////////


error_reporting (0);//E_ALL & ~E_NOTICE
ini_set("display_errors",0);

//Version PHP
$tabres["PhpVersion"]=phpversion();
//Chemin physique
$tabres["RootPath"]=str_replace("hostGetParam.php","",$_SERVER['SCRIPT_FILENAME']);
//Available PHP modules
$tabres["AvailablePHPModules"] = implode(';',get_loaded_extensions());

// check index.* to test for obsolete conflicting files
$tabres["IndexFiles"] = ""; // ex. "index.htm;index.html;index.php"
$filesIndex = glob("index.*");
if (!empty($filesIndex)) {
	foreach ($filesIndex as $filename) {
		if (in_array($filename, array("index.html", "index.htm", "index.php", "index.asp", "index.aspx")))
			$tabres["IndexFiles"] .= (($tabres["IndexFiles"]) ? ';' : '').$filename;
	}
}


echo json_encode($tabres);

?>