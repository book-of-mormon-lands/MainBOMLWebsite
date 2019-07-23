<?php
session_start();
date_default_timezone_set('Europe/Paris'); // Enlever l'erreur "Warning: strftime()" en local
// Livre d'or - GUESTBOOK
	$file		= 'Guesbook.txt';
	$delimit 	= '-*-'; // délimiteur dans le fichier afin de retrouver les arguments
	$nom = $_POST['WEffd8aa8149']; // Nom
	$ville = $_POST['WEfe5ef239b9']; // Ville
	$message = $_POST['WE19b5b9a65e']; // Message
        $AntiF5Refresh = $_POST['WE5bb82e5bb3']; // AntiF5

	$ipvisiteur = $_SERVER["REMOTE_ADDR"];
	$CookieName = 'NomDuCookie'; // Nom du cookie

// Affichage date en fr
	setlocale(LC_TIME, 'fr_FR.UTF8');
    $date		= strftime('%A %d %B %Y à %H'.h.'%M'); // Date avec l'heure sous le format : dimanche 19 octobre 2014 à 18h58

// Initialisation des variables
	$MsgErreur	= '';
	$validForm	= true;

// Récupération des données
    // Assigner les variables au POST

// TRAITEMENT du formulaire au POST

if(isset($nom, $ville, $message, $AntiF5Refresh, $_SESSION['AntiF5']) && $AntiF5Refresh==$_SESSION['AntiF5'])
{

// Début - Gestion du Cookie
    // Pour éviter qu'une unique personne poste 50 fois dans le livre d'or, création d'un cookie
  	setcookie($CookieName,$_SERVER['REMOTE_ADDR'],time()+3600*24); // 24 heures soit 3600 secondes = 1 heures x 24 = 24 heures soit +3600*24
	// Si le cookie est égal à l'ip du client, alors on le stop
	if (isset($_COOKIE[$CookieName]) && $_COOKIE[$CookieName]==$_SERVER['REMOTE_ADDR'])
	{

} else {

// Fin - Gestion du Cookie

	// On convertit les caracteres html
	$nom 		= htmlspecialchars(stripslashes(trim(strip_tags($nom))));
	$ville 		= htmlspecialchars(stripslashes(trim(strip_tags($ville))));

    // Champs de texte multi-ligne : Gestion des injections de code "html"
	$allowable_tags = '<b><a>'; // On autorise les balises <b> (gras) et <a> (lien) - (facultatif)
	$message 	= htmlspecialchars(stripslashes(trim(strip_tags($message, $allowable_tags))));
	$message 	= nl2br($message); // nl2br() : Change les sauts de ligne en <br />
	$message 	= preg_replace("/(\r\n|\n|\r)/", " ", $message); // Enlève les sauts de ligne, formatage du texte sur une seule ligne dans le fichier txt

// Vérification du message

	// DÉBUT - Censure de certains mots
	function CensureMots($text){
	// Liste des mots a filtrer ou les expressions, ne pas oublier de mettre un espace avant et après le mot ou l'expression
		$words_to_censor = array(' conne ',' merde ', ' salope ', ' connasse ', ' bite ', ' nul ', ' bof ', ' voleur ', ' arnaque '); // Les espaces pour éviter de censurer les mots comme "contenir"
        return str_replace($words_to_censor, ' *bip* ', $text); // On remplace la censure par *bip*
	}
	// On censure
	$WasCensoredNom 	= CensureMots($nom);
    $WasCensoredVille 	= CensureMots($ville);
	$WasCensoredMessage = CensureMots($message);

	$Field_Censored = array();
	if ($nom!=$WasCensoredNom) {			$Field_Censored[] = ' "Nom" a été censuré'; }
	if ($ville!=$WasCensoredVille) {		$Field_Censored[] = ' "Ville" a été censuré'; }
	if ($message!=$WasCensoredMessage) {	$Field_Censored[] = ' "Message" a été censuré'; }
	if(count($Field_Censored)>0) {
		$MsgErreur 	.= 'Attention le champ de saisie : '.implode(', ',$Field_Censored).'<br />';
	}
    // FIN - Censure de certains mots

	// Champs obligatoires
	$Field_Obligatory = array();
	if ($nom=='' || $WasCensoredNom=='') {			$validForm = false;		$Field_Obligatory[] = 'Votre Prénom et le Nom'; }
	if ($ville=='' || $WasCensoredVille=='') 	{	$validForm = false;		$Field_Obligatory[] = 'Votre ville'; }
	if ($message=='' || $WasCensoredMessage=='') {	$validForm = false;		$Field_Obligatory[] = 'Votre message'; }
	
	if(count($Field_Obligatory)>1) {		$MsgErreur 	.= 'Vous devez remplir tous les champs obligatoires : '.implode(', ',$Field_Obligatory).'<br />'; }


	// Le formulaire a été contrôlé, on écrit les données dans le fichier txt
	if($validForm === true) 
	{
		if($WasCensoredNom!='' && $WasCensoredVille!='' && $WasCensoredMessage!='')
		{
			//Écriture dans le GUESTBOOK
			//Ouverture du fichier en écriture
			$fp 	= fopen($file,'a');
			$line 	= $WasCensoredNom.$delimit.$WasCensoredMessage.$delimit.$date.$delimit.ucfirst(strtolower($WasCensoredVille)).$delimit.$ipvisiteur."\n";
			//On rajoute le message dans le fichier
			fwrite($fp, $line, strlen($line));
			//fermeture du fichier
			fclose($fp);

			// Send mail à l'admin pour l'informer qu'un nouveau message dans le livre d'or vient d'être ajouté (votre mail d'une façon découpé pour éviter les robots spammer...)
			$nommail = "brice";
			$arob = "@";
			$nomdomaine = "sensode";
			$nomdomaineext = ".com"; // l'e-mail de l'exemple est : Moi@NomDeDomaine.fr

			// Send mail à l'admin pour l'informer qu'un nouveau message dans le livre d'or vient d'être ajouté
			$sujetmail = 'Nouveau message dans le livre d\'or';
			$messagemail = "Vous avez un nouveau message de ".$nom." dans le livre d'or,<br /><br /><strong>-Message :</strong><br />$message";
			$destinatairemail = ''.$nommail.$arob.$nomdomaine.$nomdomaineext;
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			// Envoie du message en html
			mail($destinatairemail,$sujetmail,$messagemail,$headers);
		}

session_unset(); // Détruit toutes les variables de la session courante.
session_destroy(); // Détruit toutes les données associées à la session courante. Cette fonction ne détruit pas les variables globales associées à la session, de même, elle ne détruit pas le cookie de session.

	}
}
}
unset($_POST);
// Anti-F5 (évite de re-poster le formulaire en cas d'appuis sur la touche F5 ("Actualiser la page")
$_SESSION['AntiF5'] = rand(100000,999999);

?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
 <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="generator" content="openElement (1.57.9)" />
  <link id="openElement" rel="stylesheet" type="text/css" href="WEFiles/Css/v02/openElement.css?v=50491098000" />
  <link id="siteFonts" rel="stylesheet" type="text/css" href="Files/Fonts/Fonts.css?v=50491098000" />
  <link id="OETemplate1" rel="stylesheet" type="text/css" href="Templates/loading.css?v=50491098000" />
  <link id="OETemplate2" rel="stylesheet" type="text/css" href="Templates/basecalque.css?v=50491098000" />
  <link id="OETemplate3" rel="stylesheet" type="text/css" href="Templates/Base_2.css?v=50491098000" />
  <link id="OEBase" rel="stylesheet" type="text/css" href="livre-d-or.css?v=50491098000" />
  <link rel="stylesheet" type="text/css" href="WEFiles/Css/WEMenu-v23.css?v=50491098000" />
  <link rel="stylesheet" type="text/css" href="WEFiles/EG/EGf79d11e9/Css/parallax-safe-v26.css?v=50491098000" />
  <link rel="stylesheet" type="text/css" href="WEFiles/EG/EG2e52cdc0/Css/SlideOpener-v103.css?v=50491098000" />
  <link rel="stylesheet" type="text/css" href="WEFiles/Css/opentip.css?v=50491098000" />
  <!--[if lte IE 7]>
  <link rel="stylesheet" type="text/css" href="WEFiles/Css/ie7.css?v=50491098000" />
  <![endif]-->
  <script type="text/javascript">
   var WEInfoPage = {"PHPVersion":"phpOK","OEVersion":"1-57-9","PagePath":"livre-d-or","Culture":"DEFAULT","LanguageCode":"EN","RelativePath":"","RenderMode":"Export","PageAssociatePath":"livre-d-or","EditorTexts":null};
  </script>
  <script type="text/javascript" src="WEFiles/Client/jQuery/1.10.2.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/Client/jQuery/migrate.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/Client/Common/oe.min.js?v=50491098000"></script>
  <script type="text/javascript" src="livre-d-or(var).js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/EG/EGdfb2de80/Js/ScrollTo-v9.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/Client/WEMenu-v23.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/EG/EG8ced4f68/Js/field-def-text-v59.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/EG/EGf79d11e9/Js/parallax-safe-v48.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/EG/EG2e52cdc0/Js/SlideOpener-v179.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/EG/EGb09efbd1/Js/iframeprotect-v18.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/Client/jQuery/Plugins/jquery.form.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/Client/opentip-jquery.min.js?v=50491098000"></script>
  <script type="text/javascript" src="WEFiles/Client/WESendForm-v210.js?v=50491098000"></script>
  <script type="text/javascript">
   var WEEdValidators = {"WEffd8aa8149":[{"MsgError":"Votre Nom.","Expression":".+"}],"WEfe5ef239b9":[{"MsgError":"Votre ville.","Expression":".+"}],"WE19b5b9a65e":[{"MsgError":"Votre message.","Expression":".+"}],"WE3d3080a6a4":[{"MsgError":"Votre Nom.","Expression":".+"}],"WE17240d11bf":[{"MsgError":"Votre ville.","Expression":".+"}],"WE0db3529c74":[{"MsgError":"Votre message.","Expression":".+"}]}
  </script>
  <style id="OEScriptManager" type="text/css">
   body {
       overflow: hidden;
   }
   
   /* Preloader */
   
   #preloader {
       position:fixed;
       top:0;
       left:0;
       right:0;
       bottom:0;
       background-color:#fff; /* change if the mask should have another color then white */
       z-index:9999999; /* makes sure it stays on top */
   }
   
   #status {
       width:400px;
       height:300px;
       margin:-200px 0 0 -200px; /* is width and height divided by two */
       position:absolute;
       left:50%; /* centers the loading animation horizontally one the screen */
       top:50%; /* centers the loading animation vertically one the screen */
       background-image:url(Files/Image/preloader.gif); /* path to your loading animation */
       background-repeat:no-repeat;
       background-position:center;
   }
   #WEToTop {
   	position:fixed !important;
   	z-index:99999 !important;
   }
   #WEToTop:not(.c-visible) {
   	bottom:-35px !important;
   	opacity:0;
   }
   
   .BtnLarsene{
    	transition: background-color 1s, color 1s;
   	width:150px;
   	height:30px;
   	cursor:pointer;
   }
   
   .BtnLarsene .content{
   	position: relative;
   	top: 50%;
   	-webkit-transform: translateY(-50%);
   	-ms-transform: translateY(-50%);
   	transform: translateY(-50%);
   }
   
   .BtnLarsene .bloc{
   	text-align:center;
   }
   
   a.BtnLarsene {
       position: absolute;
       width: 100%;
       height: 100%;
       top: 0;
       left: 0;
       text-decoration: none;
       z-index: 10;
       background-color: white;
       opacity: 0;
       filter: alpha(opacity=0);
   }
   /*pas de ligne sur les blocs de formulaire*/
   input[type="text"]:focus {
   	outline:none;
   }
   
   a {
      outline:0;
   }
   
   input[type="text"]:focus, textarea:focus{
    outline:none;
   }
   
   /* transition effect */
   .transitions, .transitions a {
   	-webkit-transition: all 1s ease;
   	-moz-transition: all 1s ease;
   	-o-transition: all 1s ease;
   	transition:	all 1s ease;
   }
   
   #WEToTop {
   	position:fixed !important;
   	z-index:99999 !important;
   }
   #WEToTop:not(.c-visible) {
   	bottom:-35px !important;
   	opacity:0;
   }
   
   .OESZ_DivContent > .oeip-wrapper {
   	position: absolute;
   	left: 0; top: 0; right: 0; bottom: 0;
   	cursor: pointer;
   }
   .oeip-wrapper iframe {
   	position: relative; /* IE needs a position other than static */
   	pointer-events: none;
   }
   .oeip-wrapper iframe.clicked {
   	pointer-events: auto;
   }
   /* Gestion de l'affichage */
   .GUESTBOOK-Nom { float:left; cursor:default;}
   .GUESTBOOK-Date { float:right; cursor:default;}
   .GUESTBOOK-Message { margin:5px 40px; clear:both; cursor:default; text-align:justify;}
   .CensoredField { color:red; cursor:default;}
  </style>
  <!-- Preloader -->
  <div id="preloader">
      <div id="status">&nbsp;</div>
  </div>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <link type="text/css" rel="stylesheet" href="Files/Other/btscroll/returnOnTop.css" media="all" />
  <script type="text/javascript" src="Files/Other/btscroll/returnOnTop.js"></script>
  <script type="text/javascript">
   $(function(){
   	
   	// Button "Back to top of the page" / Bouton "Vers le haut de la page"
   
   	$('body').prepend('<a name="page-top" id="page-top">'); // create anchor at the page top
       
   	$(window).on('scroll', function(){
   		$('#WEToTop').toggleClass('c-visible', ($(window).scrollTop() > 100)); // show the button if vertical scroll is at least 100px
   	}).trigger('scroll'); // update on opening the page	
   	
   });
  </script><?php
  	if (isset($oeHeaderInlineCode)) echo $oeHeaderInlineCode;
  ?>
 </head>
 <body class="RWAuto" data-gl="{&quot;KeywordsHomeNotInherits&quot;:false}"><?php
  	if (isset($oeStartBodyInlineCode)) echo $oeStartBodyInlineCode;
  ?>
  <form id="XForm" method="post" action="#"></form>
  <div id="XBody" class="BaseDiv RWidth OEPageXbody OESK_XBody_Default" style="z-index:3000">
   <div class="OESZ OESZ_DivContent OESZG_XBody">
    <div class="OESZ OESZ_XBodyContent OESZG_XBody OECT OECT_Content OECTRel">
     <div class="OERelLine OEHAlignC OEVAlignT">
      <div id="WE18e8cc1697" class="BaseDiv RBoth OEWEPanel OESK_WEPanel_Default" style="z-index:2001">
       <div class="OESZ OESZ_DivContent OESZG_WE18e8cc1697">
        <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
         <div class="OERelLine OEHAlignC OEVAlignT">
          <div id="WE01012c72d6" class="BaseDiv RBoth OEWEPanel OESK_WEPanel_Default" style="z-index:2001">
           <div class="OESZ OESZ_DivContent OESZG_WE01012c72d6">
            <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
             <div class="OERelLine OEHAlignC OEVAlignM">
              <div id="WEb664b2e4de" class="BaseDiv RHeight OEEGdfb2de80 OESK_EGdfb2de80_Default  BtnLarsene" style="z-index:2002">
               <div class="OESZ OESZ_DivContent OESZG_WEb664b2e4de">
                <div class="content">
                	<div class="bloc">
                		SENSLIGHT
	</div>
</div>
               </div>
              </div><div id="WEed8dadef81" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2005">
               <div class="OESZ OESZ_DivContent OESZG_WEed8dadef81">
                <div class="content">
                	<div class="bloc">
                		EN
	</div>
</div>
               </div>
              </div><div id="WE231e895c8d" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2007">
               <div class="OESZ OESZ_DivContent OESZG_WE231e895c8d">
                <div class="content">
                	<div class="bloc">
                		FR
	</div>
</div>
               </div>
              </div><div id="WE717997ac74" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2006">
               <div class="OESZ OESZ_DivContent OESZG_WE717997ac74">
                <div class="content">
                	<div class="bloc">
                		CONTACT
	</div>
</div>
               </div>
              </div><div id="WE4c227c7f8a" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2004">
               <div class="OESZ OESZ_DivContent OESZG_WE4c227c7f8a">
                <div class="content">
                	<div class="bloc">
                		GALERIE
	</div>
</div>
               </div>
              </div><div id="WEdc6e567611" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo OEGd  BtnLarsene" style="z-index:2004">
               <div class="OESZ OESZ_DivContent OESZG_WEdc6e567611">
                <div class="content">
                	<div class="bloc">
                		PAGES
	</div>
</div>
               </div>
              </div><div id="WE7fbf13d323" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2004">
               <div class="OESZ OESZ_DivContent OESZG_WE7fbf13d323">
                <div class="content">
                	<div class="bloc">
                		ACCUEIL
	</div>
</div>
               </div>
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
     <div class="OERelLine OEHAlignL OEVAlignB">
      <div id="WEc51b6784d5" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:2002">
       <div class="OESZ OESZ_DivContent OESZG_WEc51b6784d5">
        <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
         <div class="OERelLine OEHAlignC OEVAlignT">
          <div id="WE4c86b365bd" class="BaseDiv RBoth OEWEPanel OESK_WEPanel_Default" style="z-index:2001">
           <div class="OESZ OESZ_DivContent OESZG_WE4c86b365bd">
            <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
             <div class="OERelLine OEHAlignC OEVAlignM">
              <div id="WEcadaadf518" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:2002">
               <div class="OESZ OESZ_DivContent OESZG_WEcadaadf518">
                <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                <style>
                /* Circle - default */
                .icon-MicroVinc-bars {
	background-color: ;
	border-radius: 100px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-bars span {
	font-size: 12px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-bars:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 100px;
}
/* Background color - mouse over */	
.bars span {
	background-color: ;
}
/* Circle - animation */
.icon-MicroVinc-bars i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-bars .fa-bars {
	color: #FFFFFF;
}
/* Couleur font - mouse over */
.icon-MicroVinc-bars:hover .fa-bars {
	color: #C00000;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-bars bars"><i class="fa fa-bars fa-No_Anim_It"></i>
	<span></span>
</a>
               </div>
              </div><div id="WE1fd6499221" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default  BtnLarsene" style="z-index:2002">
               <div class="OESZ OESZ_DivContent OESZG_WE1fd6499221">
                <div class="content">
                	<div class="bloc">
                		SENSLIGHT
	</div>
</div>
               </div>
              </div><div id="WEe0050f641c" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2004">
               <div class="OESZ OESZ_DivContent OESZG_WEe0050f641c">
                <div class="content">
                	<div class="bloc">
                		EN
	</div>
</div>
               </div>
              </div><div id="WE885eb2e734" class="BaseDiv RBoth OEEGdfb2de80 OESK_EGdfb2de80_Default OEGo  BtnLarsene" style="z-index:2004">
               <div class="OESZ OESZ_DivContent OESZG_WE885eb2e734">
                <div class="content">
                	<div class="bloc">
                		FR
	</div>
</div>
               </div>
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
     <div class="OERelLine OEHAlignC OEVAlignT">
      <div id="WE905a7e731d" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3002">
       <div class="OESZ OESZ_DivContent OESZG_WE905a7e731d">
        <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
         <div class="OERelLine OEHAlignC OEVAlignT">
          <div id="WE64b8997485" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3001">
           <div class="OESZ OESZ_DivContent OESZG_WE64b8997485">
            <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
             <div class="OERelLine OEHAlignC OEVAlignT">
              <div id="WE05c1b69ac0" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3002">
               <div class="OESZ OESZ_DivContent OESZG_WE05c1b69ac0">
                <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                 <div class="OERelLine OEHAlignL OEVAlignB">
                  <div id="WE64866444df" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3002">
                   <div class="OESZ OESZ_DivContent OESZG_WE64866444df">
                    <span class="OESZ OESZ_Text OESZG_WE64866444df ContentBox"><a href="index.htm">HOME</a>&nbsp;» <a href="index.htm">LOREM</a>&nbsp; » <a href="livre-d-or.php"><span style="color:rgb(169, 81, 54);">L</span><span style="color:rgb(169, 81, 54);">IVRE D'OR</span></a></span>
                   </div>
                  </div>
                 </div>
                 <div class="OERelLine OEHAlignL OEVAlignB">
                  <div id="WE5cc7a14d11" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default  apply-smoove Panel_LivreOr" style="z-index:3001">
                   <div class="OESZ OESZ_DivContent OESZG_WE5cc7a14d11">
                    <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                     <div class="OERelLine OEHAlignC OEVAlignB">
                      <div id="WE12586b49ff" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3001">
                       <div class="OESZ OESZ_DivContent OESZG_WE12586b49ff">
                        <span class="OESZ OESZ_Text OESZG_WE12586b49ff ContentBox">Quisque Condimentum</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignB">
                      <div id="WEfaaf6c95ae" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3002">
                       <div class="OESZ OESZ_DivContent OESZG_WEfaaf6c95ae">
                        <span class="ContentBox">Justo sit amet placerat a magna sit amet nunc malesuada mollis suspendisse rutrum nisi eu rhoncus</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignB">
                      <div id="WEffd8aa8149" class="BaseDiv RWidth OEWETextBoxV2 OESK_WETextBox2_Default OEGo  default-text" style="z-index:3003">
                       <div class="OESZ OESZ_DivContent OESZG_WEffd8aa8149">
                        <input name="WEffd8aa8149" type="text" class="OESZ OESZ_TextBox OESZG_WEffd8aa8149 OEDynTag0" value="Nom*" />
                       </div>
                      </div><div id="WEfe5ef239b9" class="BaseDiv RWidth OEWETextBoxV2 OESK_WETextBox2_Default OEGo  default-text" style="z-index:3004">
                       <div class="OESZ OESZ_DivContent OESZG_WEfe5ef239b9">
                        <input name="WEfe5ef239b9" type="text" class="OESZ OESZ_TextBox OESZG_WEfe5ef239b9 OEDynTag0" value="Ville" />
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignT">
                      <div id="WE19b5b9a65e" class="BaseDiv RBoth OEWETextAreaV2 OESK_WETextArea2_Default OEGo  default-text" style="z-index:3006">
                       <div class="OESZ OESZ_DivContent OESZG_WE19b5b9a65e">
                        <textarea class="OESZ OESZ_TextArea OESZG_WE19b5b9a65e OEDynTag0" name="WE19b5b9a65e" style="resize:none" rows="3" cols="50">Message</textarea>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignT">
                      <div id="WEd6c55fb70c" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3005">
                       <div class="OESZ OESZ_DivContent OESZG_WEd6c55fb70c">
                        <span class="OESZ OESZ_Text OESZG_WEd6c55fb70c ContentBox">ENVOYER</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WEfa6a62c05b" class="BaseDiv RNone OEWELink OESK_WELink_Default" style="z-index:3008">
                       <div class="OESZ OESZ_DivContent OESZG_WEfa6a62c05b">
                        <a class="OESZ OESZ_Link OESZG_WEfa6a62c05b ContentBox" data-cd="PageLink">Afficher les commentaires</a>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE5bb82e5bb3" class="BaseDiv RWidth OEWETextBoxV2 OESK_WETextBox2_Default OEGo  default-text" style="z-index:3008">
                       <div class="OESZ OESZ_DivContent OESZG_WE5bb82e5bb3">
                        <input name="WE5bb82e5bb3" type="text" class="OESZ OESZ_TextBox OESZG_WE5bb82e5bb3 OEDynTag0" value="compteur" />
                       </div>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div>
                 </div>
                 <div class="OERelLine OEHAlignL OEVAlignB">
                  <div id="WE820acdc8c1" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default  LivreOr_AlreadySend" style="z-index:3004">
                   <div class="OESZ OESZ_DivContent OESZG_WE820acdc8c1">
                    <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                     <div class="OERelLine OEHAlignC OEVAlignB">
                      <div id="WE65128472a4" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3001">
                       <div class="OESZ OESZ_DivContent OESZG_WE65128472a4">
                        <span class="OESZ OESZ_Text OESZG_WE65128472a4 ContentBox">Quisque Condimentum</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignB">
                      <div id="WE7cef0e82d9" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3002">
                       <div class="OESZ OESZ_DivContent OESZG_WE7cef0e82d9">
                        <span class="ContentBox">Votre commentaire a bien été posté.<br />Nous vous remercions de votre passage.<br />Vous ne pouvez poster de nouveau que dans 24h.<br /></span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignB">
                      <div id="WE3d3080a6a4" class="BaseDiv RWidth OEWETextBoxV2 OESK_WETextBox2_Default OEGo  default-text" style="z-index:3003">
                       <div class="OESZ OESZ_DivContent OESZG_WE3d3080a6a4">
                        <input name="WE3d3080a6a4" type="text" readonly="readonly" disabled="disabled" class="OESZ OESZ_TextBox OESZG_WE3d3080a6a4 OEDynTag0" value="Nom*" />
                       </div>
                      </div><div id="WE17240d11bf" class="BaseDiv RWidth OEWETextBoxV2 OESK_WETextBox2_Default OEGo  default-text" style="z-index:3004">
                       <div class="OESZ OESZ_DivContent OESZG_WE17240d11bf">
                        <input name="WE17240d11bf" type="text" readonly="readonly" disabled="disabled" class="OESZ OESZ_TextBox OESZG_WE17240d11bf OEDynTag0" value="Ville" />
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignT">
                      <div id="WE0db3529c74" class="BaseDiv RBoth OEWETextAreaV2 OESK_WETextArea2_Default OEGo  default-text" style="z-index:3006">
                       <div class="OESZ OESZ_DivContent OESZG_WE0db3529c74">
                        <textarea class="OESZ OESZ_TextArea OESZG_WE0db3529c74 OEDynTag0" name="WE0db3529c74" style="resize:none" readonly="readonly" disabled="disabled" rows="3" cols="50">Message</textarea>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignC OEVAlignT">
                      <div id="WEe2cf869999" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3005">
                       <div class="OESZ OESZ_DivContent OESZG_WEe2cf869999">
                        <span class="OESZ OESZ_Text OESZG_WEe2cf869999 ContentBox">ENVOYER</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE9467898b7b" class="BaseDiv RNone OEWELink OESK_WELink_Default" style="z-index:3008">
                       <div class="OESZ OESZ_DivContent OESZG_WE9467898b7b">
                        <a class="OESZ OESZ_Link OESZG_WE9467898b7b ContentBox" data-cd="PageLink">Afficher les commentaires</a>
                       </div>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
         <div class="OERelLine OEHAlignC OEVAlignT">
          <div id="WEbfe584cf05" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3002">
           <div class="OESZ OESZ_DivContent OESZG_WEbfe584cf05">
            <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
             <div class="OERelLine OEHAlignC OEVAlignT">
              <div id="WE2f4dafb561" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3002">
               <div class="OESZ OESZ_DivContent OESZG_WE2f4dafb561">
                <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                 <div class="OERelLine OEHAlignC OEVAlignT">
                  <div id="WE97be3a085b" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3001">
                   <div class="OESZ OESZ_DivContent OESZG_WE97be3a085b">
                    <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                     <div class="OERelLine OEHAlignL OEVAlignT">
                      <div id="WE146596d223" class="BaseDiv RWidth OEWECodeBlock OESK_WECodeBlock_Default" style="z-index:3001">
                       <div class="OESZ OESZ_DivContent OESZG_WE146596d223">
                        <?php
                        
                        // Affichage d'un message si un champ de saisie contient des mots censurés
                        if(!empty($MsgErreur)) {
                        	echo '<p class="CensoredField">&nbsp;'.$MsgErreur.'</p>';
                        }
                        
                        
                        // Lecture du GUESTBOOK
                        $LinesBDD = file($file);
                        // array_reverse : affichage dans l'ordre ANTI-CHRONOLOGIQUE
                        $LinesBDD = array_reverse($LinesBDD);
                        // lecture dans le fichier ligne par ligne
                        foreach($LinesBDD as $LineBDD) {
                        	$LineBDD = trim($LineBDD);
                        	if(strlen($LineBDD) > 2){
                        
                        		$SingleArgument = explode($delimit,$LineBDD);
                        		$nom = $SingleArgument[0];
                        		$message = html_entity_decode($SingleArgument[1]);
                        		$date = $SingleArgument[2];
                        		$ville	 = $SingleArgument[3];
                        		$ip = $SingleArgument[4]; // Option affichage de l'ip
                        		
                        		$ShowMessages = '<p><span class="GUESTBOOK-Nom"><img src="Files/Image/Plume.png" height=30 width=30></img>De <b>'.$nom.'</b>'; // Affichage de l'image
                        		$ShowMessages .= '&nbsp;&nbsp;<i>('.$ville.')</i>';
                        		$ShowMessages .= '</span><span class="GUESTBOOK-Date">';
                        		$ShowMessages .= '<i><font color=#A4A4A4>le '.substr($date, 0, -8).'&nbsp;</font></i>';
                        		$ShowMessages .= '</span></p>';
                        		$ShowMessages .= '<p class="GUESTBOOK-Message"><br><font color=#0489B1>'.$message.'</font></p><hr/>';
                        		echo $ShowMessages;
                        	}
                        }
                        ?>
                       </div>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div><div id="WE30a817024e" class="BaseDiv RBoth OEWELinkImage OESK_WELinkImage_Default OEGo  transitions" style="z-index:3002">
                   <div class="OESZ OESZ_DivContent OESZG_WE30a817024e">
                    <img style="width:100%;height:100%;border:none" src="WEFiles/Image/empty.png" />
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
             </div>
             <div class="OERelLine OEHAlignC OEVAlignT">
              <div id="WE64ecefcb3f" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3002">
               <div class="OESZ OESZ_DivContent OESZG_WE64ecefcb3f">
                <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                 <div class="OERelLine OEHAlignC OEVAlignT">
                  <div id="WE6bf6bddcc3" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3001">
                   <div class="OESZ OESZ_DivContent OESZG_WE6bf6bddcc3">
                    <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE42dea82570" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3001">
                       <div class="OESZ OESZ_DivContent OESZG_WE42dea82570">
                        <span class="OESZ OESZ_Text OESZG_WE42dea82570 ContentBox">SENSLIGHT</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE0b00d53059" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3002">
                       <div class="OESZ OESZ_DivContent OESZG_WE0b00d53059">
                        <span class="ContentBox">Template <a href="http://www.openelement.fr" onclick="window.open(this.href);return false">OpenElement</a></span>
                       </div>
                      </div><div id="WE8c4c05dcf9" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3003">
                       <div class="OESZ OESZ_DivContent OESZG_WE8c4c05dcf9">
                        <span class="ContentBox">Réalisation&nbsp;<a href="https://sensode.com/">Sensode</a></span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WEb32af33961" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3004">
                       <div class="OESZ OESZ_DivContent OESZG_WEb32af33961">
                        <span class="ContentBox">Viverra in vel consequat libero lorem ipsum dolor sit amet consectetur</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WEa2772a033e" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:3005">
                       <div class="OESZ OESZ_DivContent OESZG_WEa2772a033e">
                        <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                        <style>
                        /* Circle - default */
                        .icon-MicroVinc-facebook {
	background-color: #FFFFFF;
	border-radius: 0px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-facebook span {
	font-size: 24px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-facebook:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 0px;
}
/* Background color - mouse over */	
.facebook span {
	background-color: #3B5998;
}
/* Circle - animation */
.icon-MicroVinc-facebook i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-facebook .fa-facebook {
	color: #3B5998;
}
/* Couleur font - mouse over */
.icon-MicroVinc-facebook:hover .fa-facebook {
	color: #FFFFFF;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-facebook facebook"><i class="fa fa-facebook fa-No_Anim_It"></i>
	<span></span>
</a>
                       </div>
                      </div><div id="WEb1a6d21670" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:3010">
                       <div class="OESZ OESZ_DivContent OESZG_WEb1a6d21670">
                        <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                        <style>
                        /* Circle - default */
                        .icon-MicroVinc-twitter {
	background-color: #FFFFFF;
	border-radius: 0px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-twitter span {
	font-size: 24px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-twitter:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 0px;
}
/* Background color - mouse over */	
.twitter span {
	background-color: #3B5998;
}
/* Circle - animation */
.icon-MicroVinc-twitter i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-twitter .fa-twitter {
	color: #3B5998;
}
/* Couleur font - mouse over */
.icon-MicroVinc-twitter:hover .fa-twitter {
	color: #FFFFFF;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-twitter twitter"><i class="fa fa-twitter fa-No_Anim_It"></i>
	<span></span>
</a>
                       </div>
                      </div><div id="WEeb316c781c" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:3010">
                       <div class="OESZ OESZ_DivContent OESZG_WEeb316c781c">
                        <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                        <style>
                        /* Circle - default */
                        .icon-MicroVinc-pinterest {
	background-color: #FFFFFF;
	border-radius: 0px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-pinterest span {
	font-size: 24px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-pinterest:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 0px;
}
/* Background color - mouse over */	
.pinterest span {
	background-color: #3B5998;
}
/* Circle - animation */
.icon-MicroVinc-pinterest i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-pinterest .fa-pinterest {
	color: #3B5998;
}
/* Couleur font - mouse over */
.icon-MicroVinc-pinterest:hover .fa-pinterest {
	color: #FFFFFF;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-pinterest pinterest"><i class="fa fa-pinterest fa-No_Anim_It"></i>
	<span></span>
</a>
                       </div>
                      </div><div id="WEa6397e64d8" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:3010">
                       <div class="OESZ OESZ_DivContent OESZG_WEa6397e64d8">
                        <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                        <style>
                        /* Circle - default */
                        .icon-MicroVinc-instagram {
	background-color: #FFFFFF;
	border-radius: 0px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-instagram span {
	font-size: 24px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-instagram:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 0px;
}
/* Background color - mouse over */	
.instagram span {
	background-color: #3B5998;
}
/* Circle - animation */
.icon-MicroVinc-instagram i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-instagram .fa-instagram {
	color: #3B5998;
}
/* Couleur font - mouse over */
.icon-MicroVinc-instagram:hover .fa-instagram {
	color: #FFFFFF;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-instagram instagram"><i class="fa fa-instagram fa-No_Anim_It"></i>
	<span></span>
</a>
                       </div>
                      </div><div id="WEec41057b74" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:3010">
                       <div class="OESZ OESZ_DivContent OESZG_WEec41057b74">
                        <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                        <style>
                        /* Circle - default */
                        .icon-MicroVinc-youtube {
	background-color: #FFFFFF;
	border-radius: 0px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-youtube span {
	font-size: 24px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-youtube:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 0px;
}
/* Background color - mouse over */	
.youtube span {
	background-color: #3B5998;
}
/* Circle - animation */
.icon-MicroVinc-youtube i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-youtube .fa-youtube {
	color: #3B5998;
}
/* Couleur font - mouse over */
.icon-MicroVinc-youtube:hover .fa-youtube {
	color: #FFFFFF;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-youtube youtube"><i class="fa fa-youtube fa-No_Anim_It"></i>
	<span></span>
</a>
                       </div>
                      </div><div id="WE5135df1d1e" class="BaseDiv RBoth OEEG98fd1859 OESK_EG98fd1859_Default" style="z-index:3010">
                       <div class="OESZ OESZ_DivContent OESZG_WE5135df1d1e">
                        <link rel='stylesheet prefetch' href='http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css'>
                        <style>
                        /* Circle - default */
                        .icon-MicroVinc-linkedin {
	background-color: #FFFFFF;
	border-radius: 0px; /* Arrondir les angles */
	cursor: pointer;
	display: inline-block;
	font-size: 120px;  /* Taille font du logo si pb : html {font-size: 10px;} */
	height:  200px;
	width:  200px;
	line-height:  200px;
	position: relative;
	text-align: center;
	-webkit-user-select: none;
	   -moz-user-select: none;
	    -ms-user-select: none;
	        user-select: none;
}
/* Animation txt */
.icon-MicroVinc-linkedin span {
	font-size: 24px;
	border-radius: 0;
	display: block;
	height: 0;
	width: 0;
	top: 50%;
	left: 50%;
	margin: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
}
/* Animation txt */	
.icon-MicroVinc-linkedin:hover span {
	font-size: 0px;
	width:  200px;
	height:  200px;
	/* margin: -97px; */
	margin:-100px;
	border-radius: 0px;
}
/* Background color - mouse over */	
.linkedin span {
	background-color: #3B5998;
}
/* Circle - animation */
.icon-MicroVinc-linkedin i {
	background: none;
	color: white;
	height:  200px;
	width:  200px;
	line-height:  200px;
	top: 0;
	left: 0;
	position: absolute;
	-webkit-transition: all 0.5s;
	   -moz-transition: all 0.5s;
	     -o-transition: all 0.5s;
	        transition: all 0.5s;
	z-index: 10000;
}
/* Couleur font - default */	
.icon-MicroVinc-linkedin .fa-linkedin {
	color: #3B5998;
}
/* Couleur font - mouse over */
.icon-MicroVinc-linkedin:hover .fa-linkedin {
	color: #FFFFFF;
}
</style>
<a href="" target="_blank" class="icon-MicroVinc-linkedin linkedin"><i class="fa fa-linkedin fa-No_Anim_It"></i>
	<span></span>
</a>
                       </div>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div><div id="WEeb24aa0bd8" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3002">
                   <div class="OESZ OESZ_DivContent OESZG_WEeb24aa0bd8">
                    <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WEa011789d6b" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3001">
                       <div class="OESZ OESZ_DivContent OESZG_WEa011789d6b">
                        <span class="OESZ OESZ_Text OESZG_WEa011789d6b ContentBox">la société</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE45caac2af8" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3004">
                       <div class="OESZ OESZ_DivContent OESZG_WE45caac2af8">
                        <span class="ContentBox"><img src="Files/Image/icone-position.png" style="vertical-align: middle; float: left; margin-right: 20px; " />SENSODE<br />11 avenue général Estienne<br />06000 NICE<br /></span>
                       </div>
                      </div><div id="WE1735de2954" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3003">
                       <div class="OESZ OESZ_DivContent OESZG_WE1735de2954">
                        <span class="ContentBox"><img src="Files/Image/icone-telephone.png" style="vertical-align: middle; float: left; margin-right: 20px; " /><br />+33 6 28 55 81 22<br /></span>
                       </div>
                      </div><div id="WE6999323a6f" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3004">
                       <div class="OESZ OESZ_DivContent OESZG_WE6999323a6f">
                        <span class="ContentBox"><img src="Files/Image/icone-mail.png" style="vertical-align: middle; float: left; margin-right: 20px; " /><br /><a href="mailto:contact@sensode.com">contact@sensode.com</a><br /></span>
                       </div>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div><div id="WEc882ce9610" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3003">
                   <div class="OESZ OESZ_DivContent OESZG_WEc882ce9610">
                    <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WEcf6d7a615e" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3001">
                       <div class="OESZ OESZ_DivContent OESZG_WEcf6d7a615e">
                        <span class="OESZ OESZ_Text OESZG_WEcf6d7a615e ContentBox">INFORMATIONS</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE9cdd9fd0f1" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3003">
                       <div class="OESZ OESZ_DivContent OESZG_WE9cdd9fd0f1">
                        <span class="ContentBox">Imperdiet A Auctor Odio</span>
                       </div>
                      </div><div id="WEfc067e03c9" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3006">
                       <div class="OESZ OESZ_DivContent OESZG_WEfc067e03c9">
                        <span class="ContentBox">vendredi 14 Avril 2017</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE7e2acd8a41" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3004">
                       <div class="OESZ OESZ_DivContent OESZG_WE7e2acd8a41">
                        <span class="ContentBox">Tristique orci ut malesuada fermentum quam non sed eget sagittis mi […]</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WEb5c16b4d3a" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3007">
                       <div class="OESZ OESZ_DivContent OESZG_WEb5c16b4d3a">
                        <span class="ContentBox">Vehicula Aliquam Ornare</span>
                       </div>
                      </div>
                     </div>
                     <div class="OERelLine OEHAlignL OEVAlignB">
                      <div id="WE2fcb486970" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3005">
                       <div class="OESZ OESZ_DivContent OESZG_WE2fcb486970">
                        <span class="ContentBox">Samedi 15 avril 2017<br /></span>
                       </div>
                      </div><div id="WE4c17f16e0f" class="BaseDiv RWidth OEWEText OESK_WEText_Default" style="z-index:3005">
                       <div class="OESZ OESZ_DivContent OESZG_WE4c17f16e0f">
                        <span class="ContentBox">Vivamus luctus nec eros nec tincidunt donec at sagittis nisi in id massa […]</span>
                       </div>
                      </div>
                     </div>
                    </div>
                   </div>
                  </div>
                 </div>
                </div>
               </div>
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
     <div class="OERelLine OEHAlignC OEVAlignT">
      <div id="WE673f434247" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3003">
       <div class="OESZ OESZ_DivContent OESZG_WE673f434247">
        <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
         <div class="OERelLine OEHAlignC OEVAlignT">
          <div id="WE69b2974846" class="BaseDiv RWidth OEWEPanel OESK_WEPanel_Default" style="z-index:3001">
           <div class="OESZ OESZ_DivContent OESZG_WE69b2974846">
            <div class="OECT OECT_Content OECTRel OEDynTag0" style="overflow:hidden">
             <div class="OERelLine OEHAlignC OEVAlignT">
              <div id="WE960b204578" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3001">
               <div class="OESZ OESZ_DivContent OESZG_WE960b204578">
                <span class="OESZ OESZ_Text OESZG_WE960b204578 ContentBox">Copyright © 2017 - Tout droits réservés - Nom de domaine</span>
               </div>
              </div><div id="WE49670ca609" class="BaseDiv RNone OEWELabel OESK_WELabel_Default" style="z-index:3002">
               <div class="OESZ OESZ_DivContent OESZG_WE49670ca609">
                <span class="OESZ OESZ_Text OESZG_WE49670ca609 ContentBox">Template réalisé par Sensode</span>
               </div>
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
    </div>
    <div id="WEb099d1a87a" class="BaseDiv RBoth OEEG8ced4f68 OESK_EG8ced4f68_Default" style="z-index:3001;display: none !important;">
     <div class="OESZ OESZ_DivContent OESZG_WEb099d1a87a"></div>
    </div>
    <div id="WE20ce13d173" class="BaseDiv RBoth OEEGf79d11e9 OESK_EGf79d11e9_Default" style="z-index:3002">
     <div class="OESZ OESZ_DivContent OESZG_WE20ce13d173"></div>
    </div>
    <div id="WEe6194b41af" class="BaseDiv RBoth OEEG2e52cdc0 OESK_EG2e52cdc0_Default" style="z-index:3003">
     <div class="OESZ OESZ_DivContent OESZG_WEe6194b41af"></div>
    </div>
    <div id="WE72a040b678" class="BaseDiv RBoth OEEGb09efbd1 OESK_EGb09efbd1_Default" style="z-index:3004">
     <div class="OESZ OESZ_DivContent OESZG_WE72a040b678"></div>
    </div>
    <div id="WE2bb29f3156" class="BaseDiv RBoth OEWEMenu OESK_WEMenu_Default OE_ActiveLink" style="z-index:2001">
     <div class="OESZ OESZ_DivContent OESZG_WE2bb29f3156 OE_ActiveLink">
      <div class="OESZ OESZ_WEMenuGroup OESZG_WE2bb29f3156 OE_ActiveLink" style="display:none" id="WEMenud20d99">
       <div class="OESZ OESZ_WEMenuTop OESZG_WE2bb29f3156 OE_ActiveLink"></div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenub25f03">
        <table onclick="return OE.Navigate.open(event,'galerie-free.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="galerie-free.htm">GALERIES</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenua3db66">
        <table onclick="return OE.Navigate.open(event,'article-interne.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="article-interne.php">ARTICLE INTERNE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenuf83a92">
        <table onclick="return OE.Navigate.open(event,'page-interne.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="page-interne.php">PAGE INTERNE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenu8b0f2f">
        <table onclick="return OE.Navigate.open(event,'bloc-interne.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="bloc-interne.htm">BLOC INTERNE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenub9e290">
        <table onclick="return OE.Navigate.open(event,'livre-d-or.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="livre-d-or.php">LIVRE D'OR</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenua3c445">
        <table onclick="return OE.Navigate.open(event,'http://sensode.net/livre-dor.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="http://sensode.net/livre-dor.php">LIVRE D'OR 2</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenu13b368">
        <table onclick="return OE.Navigate.open(event,'contact.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="contact.htm">CONTACT</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WE2bb29f3156 OEo OE_ActiveLink" id="WEMenubd8d07">
        <table onclick="return OE.Navigate.open(event,'achat.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WE2bb29f3156 OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WE2bb29f3156 OEo OE_ActiveLink">
           <a href="achat.htm">VERSION COMPLÈTE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuBottom OESZG_WE2bb29f3156 OE_ActiveLink"></div>
      </div>
     </div>
    </div>
    <div id="WEbc65422f1f" class="BaseDiv RBoth OEWEMenu OESK_WEMenu_Default OE_ActiveLink  transitions" style="z-index:2005">
     <div class="OESZ OESZ_DivContent OESZG_WEbc65422f1f OE_ActiveLink">
      <div class="OESZ OESZ_WEMenuGroup OESZG_WEbc65422f1f OEo OE_ActiveLink" style="display:none" id="WEMenud20d99">
       <div class="OESZ OESZ_WEMenuTop OESZG_WEbc65422f1f OE_ActiveLink"></div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenuc09d4d">
        <table onclick="return OE.Navigate.open(event,'index.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="index.htm">ACCUEIL</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenu172e31">
        <table style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="">PAGES</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenud8ab5f">
        <table onclick="return OE.Navigate.open(event,'article-interne.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td style="width:1px">
           <a href="article-interne.php">
            <img src="Files/Image/BLOC-TRANSPARENT.png" class="OESZ OESZ_WEMenuIcon OESZG_WEbc65422f1f OE_ActiveLink" alt="ARTICLE INTERNE" />
           </a>
          </td>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="article-interne.php">ARTICLE INTERNE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenudef559">
        <table onclick="return OE.Navigate.open(event,'page-interne.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td style="width:1px">
           <a href="page-interne.php">
            <img src="Files/Image/BLOC-TRANSPARENT.png" class="OESZ OESZ_WEMenuIcon OESZG_WEbc65422f1f OE_ActiveLink" alt="PAGE INTERNE" />
           </a>
          </td>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="page-interne.php">PAGE INTERNE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenu9625ff">
        <table onclick="return OE.Navigate.open(event,'bloc-interne.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td style="width:1px">
           <a href="bloc-interne.htm">
            <img src="Files/Image/BLOC-TRANSPARENT.png" class="OESZ OESZ_WEMenuIcon OESZG_WEbc65422f1f OE_ActiveLink" alt="BLOC INTERNE" />
           </a>
          </td>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="bloc-interne.htm">BLOC INTERNE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenud07661">
        <table onclick="return OE.Navigate.open(event,'galerie-free.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td style="width:1px">
           <a href="galerie-free.htm">
            <img src="Files/Image/BLOC-TRANSPARENT.png" class="OESZ OESZ_WEMenuIcon OESZG_WEbc65422f1f OE_ActiveLink" alt="GALERIES" />
           </a>
          </td>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="galerie-free.htm">GALERIES</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenua25b7b">
        <table onclick="return OE.Navigate.open(event,'livre-d-or.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td style="width:1px">
           <a href="livre-d-or.php">
            <img src="Files/Image/BLOC-TRANSPARENT.png" class="OESZ OESZ_WEMenuIcon OESZG_WEbc65422f1f OE_ActiveLink" alt="LIVRE D'OR" />
           </a>
          </td>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="livre-d-or.php">LIVRE D'OR</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenu7e5b43">
        <table onclick="return OE.Navigate.open(event,'http://sensode.net/livre-dor.php',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td style="width:1px">
           <a href="http://sensode.net/livre-dor.php">
            <img src="Files/Image/BLOC-TRANSPARENT.png" class="OESZ OESZ_WEMenuIcon OESZG_WEbc65422f1f OE_ActiveLink" alt="LIVRE D'OR 2" />
           </a>
          </td>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="http://sensode.net/livre-dor.php">LIVRE D'OR 2</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenu13b368">
        <table onclick="return OE.Navigate.open(event,'contact.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="contact.htm">CONTACT</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuItem OESZG_WEbc65422f1f OEo OE_ActiveLink" id="WEMenue2953c">
        <table onclick="return OE.Navigate.open(event,'achat.htm',1)" style="border-spacing: 0px; border-collapse: collapse;" class="OESZ OESZ_WEMenuItemTable OESZG_WEbc65422f1f OE_ActiveLink">
         <tr>
          <td class="OESZ OESZ_WEMenuText OESZG_WEbc65422f1f OEo OE_ActiveLink">
           <a href="achat.htm">VERSION COMPLETE</a>
          </td>
         </tr>
        </table>
       </div>
       <div class="OESZ OESZ_WEMenuBottom OESZG_WEbc65422f1f OE_ActiveLink"></div>
      </div>
     </div>
    </div>
   </div>
  </div>
  <script type="text/javascript">
   (function(){
   
   	var tID = null;
   	
   	$('#status').css('background-image', 'url("' + $('#PRELOADIMG').find('img').attr('src') + '")');
   	
   	function onContentLoaded() {
   		// call only once:
   		$(window).off('load.oePreloader');
   		clearTimeout(tID);
   		
   		// remove preloader:
   		$('#status').fadeOut(); // will first fade out the loading animation
   		$('#preloader').delay(250).fadeOut('slow'); // will fade out the white DIV that covers the website.
   		$('body').css({'overflow':'visible'});
   	}
   	
   	$(function(){
   		var tID = setTimeout(onContentLoaded, 7000); // wait max 7 seconds after the page structure is loaded
   	})
   	$(window).on('load.oePreloader', onContentLoaded); // make sure the whole site is loaded
   
   })();
   $(function(){ EGdfb2de80.Init(); });
   
   var EGdfb2de80 = {
   
   	Init: function() {
   		if (OEConfEGdfb2de80 === undefined) return;
   		var allElements = OEConfEGdfb2de80;
   
   		for(var ID in allElements) {
   			var $el = $('#'+ID); // le tag <div> principale de l'élément
   			var properties = allElements[ID]; // les propriétés de l'élément disponibles pour JS
   			this.InitElement(ID, $el, properties);
   		}
   	},
   
   	InitElement: function(ID, $el, properties) {
   		
   		// VOTRE CODE ICI
   		
   		// exemples:
   		// var elWd = parseInt($el.width()); // obtenir la largeur de l'element
   		// var bgIm = $el.find('.OESZ_Zone1').css('background-image'); // obtenir l'image de fond de la Zone de style 'Zone1'
   		// 
   		// 
   		
   		var $IDCible = $('#'+properties.Id)
   		$($el).click(function() {
   			var link = WEEdSiteCommon.LinkGetPath(properties.Link);
   			if (link) {
   				document.location.href = link;
   			} else if ($IDCible && $IDCible.length) {
   				$.scrollTo($IDCible,800,{offset: {top:properties.Verticaloffset, left:properties.Horizontaloffset}} )
   			}
   		});
   		
   	}
   
   };
   
   
   
   
   
   
   
   // Pour éviter un send via F5
   $AntiRefresh = <?php echo $_SESSION['AntiF5']; ?>;
   $('input[name="WE5bb82e5bb3"]').val($AntiRefresh);
   
   $(["Files/Image/top-actif.png","Files/Image/fermeture-noir60x60-actif.png"]).preloadImg();
  </script>
  <script src="https://cdn.jsdelivr.net/scrollreveal.js/3.3.1/scrollreveal.min.js"></script>
  <script type="text/javascript">
  $(function(){// Scroll reveal (apparition au scroll) :
  	(new ScrollReveal()).reveal('.apply-smoove', { duration: 1000, distance: '500px', viewFactor: 0.025 });
  });
  </script><?php
  if ($_COOKIE[$CookieName]==$_SERVER['REMOTE_ADDR'] || isset($AntiF5Refresh)) // Après un post
{
echo "<script>$(document).ready(function() { $('#WE820acdc8c1').css('visibility', 'visible'); $('#WE5cc7a14d11').toggle('slow'); });</script>";  
}
else // Affichage normal
{
echo "<script>$('#WE820acdc8c1').toggle();</script>"; // Text multi-lignes qui explique qu'on peut envoyer un message que toutes les 24h
}


  ?>

 </body>
</html>