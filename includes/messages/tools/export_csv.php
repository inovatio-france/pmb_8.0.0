<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: export_csv.php,v 1.6 2023/08/28 14:01:13 tsamson Exp $

header('Content-Type: text/x-csv; charset=utf-8');
//header('Content-Type: text/html")');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Disposition: inline; filename="toto.csv"');
//header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

include('../../../classes/XMLlist.class.php');

// on d�finit les langues existantes

$languages = new XMLlist("languages_csv.xml", 0);
$languages->analyser();
$avail_lang = $languages->table;

$nb_lang = 0;
$messages_list=array();
foreach ($avail_lang as $cle => $valeur) {
	// Dans un tableau des codages, la valeur de codage est stock�e
	$codage[$nb_lang]=$valeur;
	$lang_name[$nb_lang]=$cle;
	$obj_lang = new XMLlist("../$cle.xml", 0);
	$obj_lang->analyser();
	$lang = $obj_lang->table;
	foreach ($lang as $key => $val) {
		$messages_list[$key][$nb_lang]=$val;
	}
	$nb_lang++;
}

foreach ($messages_list as $cle => $valeur) {
	echo $cle.";";
	// La premi�re langue est suppos�e en utf-8 alors que la suivante est en iso
	//$valeur[0]=encoding_normalize::utf8_normalize($valeur[0]); 

	for ($i=0; $i<$nb_lang; $i++) {
	// Si le codage de la langue n'est pas en utf-8 alors on encode
		if ($codage[$i]!="utf-8") {$valeur[$i]=encoding_normalize::utf8_normalize($valeur[$i]);}
		// la fonction de d�tection r�sultat=source est d�sactiv�e, faute de pouvoir mettre en relief
		// l'�galit�
		//		$valeur1=$valeur;
		//		$valeur1[$i]="";
		// $as=array_search($valeur[$i],$valeur1);
		// if (($as!==null)&&($as!==false)) $color="#dddddd"; else $color="#ffffff";
		
		//Si la langue choisie est l'arabe, alors il faut inverser les caract�res � l'affichage
		
		// if($lang_name[$i]!="ar") {print "<SPAN DIR='RTL'>";} else {print "<span dir='LTR'>";} 
		
		if ($valeur[$i]!="") echo $valeur[$i]; else 
		{
			if ($codage[$i]!="utf-8") 
				{ echo encoding_normalize::utf8_normalize("PLEASE TRANSLATE");} 
			else echo "PLEASE TRANSLATE";
		}
		// print "</SPAN>";
		echo ";";
	}
	echo "\n";
}

?>
