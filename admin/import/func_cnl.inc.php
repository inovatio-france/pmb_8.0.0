<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: func_cnl.inc.php,v 1.21 2022/01/03 10:20:17 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// +-------------------------------------------------+

// Attention, n'a pas �t� modifi� pour le multi-thesaurus

// +-------------------------------------------------+

// DEBUT param�trage propre � la base de donn�es d'importation :
//	les champs UNIMARC lus qui vont �tre stock�s dans des champs personnalis�s sont pr�cis�s ici
$id949_c = 1 ;
$id949_b = 2 ;
// aucun code statistique n'est fourni dans la zone d'exemplaires du CNL 997, il faut pr�ciser l'id ici
$idcode_stat_expl = 1 ;
// r�cup�ration du 606 : r�cup en cat�gories en essayant de classer :
//	les sujets sous le terme "Recherche par terme" 
		$id_rech_theme = 1 ;
//	les pr�cisions g�ographiques sous le terme "Recherche g�ographique" 
		$id_rech_geo = 2 ;
//	les pr�cisions de p�riode sous le terme "Recherche chronologique" 
		$id_rech_chrono = 3 ;
// FIN param�trage 

function recup_noticeunimarc_suite($notice) {
	global $info_949		;
	global $info_997		;

	$info_949 = array();
	$info_997 = array();

	$record = new iso2709_record($notice, AUTO_UPDATE); 
	for ($i=0;$i<count($record->inner_directory);$i++) {
		$cle=$record->inner_directory[$i]['label'];
		switch($cle) {
			case "949": // infos CNL
				$info_949=$record->get_subfield($cle,"a","b","c");
				break;
			case "997": // infos expl CNL
				$info_997=$record->get_subfield($cle,"3","a","c","d","t");
				break;
			default:
				break;
	
			} /* end of switch */
	
		} /* end of for */
	} // fin recup_noticeunimarc_suite = fin r�cup�ration des variables propres au CNL
	
function import_new_notice_suite() {
	global $notice_id ;
	
	global $info_949 ;
	global $id949_c, $id949_b ;
	
	global $info_606_a, $info_606_j, $info_606_x, $info_606_y, $info_606_z ;
	global $id_rech_theme, $id_rech_geo, $id_rech_chrono ; 
	
	/* 
	echo "<pre>";
	print_r ($info_949);
	print_r ($info_997);
	echo "</pre>";
	*/
	
	// 949$c est stock� dans un champ personnalis� "liste de valeur"
	// ce champ personnalis� a l'id $id949_c
	// le code de la liste de valeur (949$c) est dans notices_custom_lists.notices_custom_list_value WHERE notices_custom_champ=$id949_c
	// TRAITEMENT :
	//	Chercher si 949$c est pr�sent dans notices_custom_lists.notices_custom_list_value where notices_custom_champ=$id949_c
	//	Cr�er si besoin = INSERT INTO notices_custom_lists (notices_custom_champ, notices_custom_list_value, notices_custom_list_lib, ordre) VALUES ($id949_c, $info_949['c'], $info_949['c']." -Import�", 0 )
	//	Rechercher si l'enregistrement existe d�j� dans notices_custom_values = SELECT 1 FROM notices_custom_values WHERE notices_custom_champ=$id949_c AND notices_custom_origine=$notice_id AND notices_custom_small_text=$info_949['c']
	//	Cr�er si besoin
	// CORRECTION suite � test d'import : $c contient $a."texte � la con" on ne retient donc que $a
	$info_949[0]['c'] = $info_949[0]['a'] ; 
	$rqt = "select count(1) from notices_custom_lists where notices_custom_champ='".$id949_c."' and notices_custom_list_value='".$info_949[0]['c']."'" ;
	if (!pmb_mysql_result(pmb_mysql_query($rqt),0,0)) {
		$rqt_ajout = "INSERT INTO notices_custom_lists (notices_custom_champ, notices_custom_list_value, notices_custom_list_lib, ordre) VALUES ('".$id949_c."', '".$info_949[0]['c']."', '".$info_949[0]['c']." -Import�','0')" ;
		pmb_mysql_query($rqt_ajout) ;
	}
	import_records::insert_value_custom_field($id949_c, $notice_id, $info_949[0]['c']);
	
	// 949$b est stock� dans un champ personnalis� texte
	// ce champ personnalis� a l'id $id949_b
	// TRAITEMENT :
	//	Rechercher si l'enregistrement existe d�j� dans notices_custom_values = SELECT 1 FROM notices_custom_values WHERE notices_custom_champ=$id949_b AND notices_custom_origine=$notice_id
	//	Cr�er si besoin
	import_records::insert_value_custom_field($id949_b, $notice_id, $info_949[0]['b']);

	// les champs $606 sont stock�s dans les cat�gories
	//	$a >> en sous cat�gories de $id_rech_theme
	// 		$j en compl�ment de $a
	//		$x en sous cat�gories de $a
	// $y >> en sous cat�gories de $id_rech_geo
	// $z >> en sous cat�gories de $id_rech_chrono
	// TRAITEMENT :
	// pour $a=0 � size_of $info_606_a
	//	pour $j=0 � size_of $info_606_j[$a]
	//		concat�ner $libelle_j .= $info_606_j[$a][$j]
	//	$libelle_final = $info_606_a[0]." ** ".$libelle_j
	//	Rechercher si l'enregistrement existe d�j� dans categories = 
	//		SELECT categ_id FROM categories WHERE categ_parent='".$id_rech_theme."' AND categ_libelle='".addslashes($libelle_final)."' "
	//	Cr�er si besoin et r�cup�rer l'id $categid_a
	//	$categid_parent =  $categid_a
	//	pour $x=0 � size_of $info_606_x[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//			SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_x[$a][$x])."' "
	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	//	$categid_parent =  $id_rech_geo
	//	pour $y=0 � size_of $info_606_y[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//			SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_y[$a][$y])."' "
	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	//	$categid_parent =  $id_rech_chrono
	//	pour $y=0 � size_of $info_606_z[$a]
	//		Rechercher si l'enregistrement existe d�j� dans categories = 
	//			SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_z[$a][$y])."' "
	//		Cr�er si besoin et r�cup�rer l'id $categid_parent
	//
	$nb_infos_606_a = count($info_606_a);
	for ($a = 0; $a < $nb_infos_606_a; $a++) {
	    $nb_infos_606_j = count($info_606_j[$a]);
	    for ($j = 0; $j < $nb_infos_606_j; $j++) {
			if (!$libelle_j) $libelle_j .= $info_606_j[$a][$j] ;
				else $libelle_j .= " ** ".$info_606_j[$a][$j] ;
		}
		if (!$libelle_j) $libelle_final = $info_606_a[$a][0] ;
		else $libelle_final = $info_606_a[$a][0]." ** ".$libelle_j ;
		if (!$libelle_final) break ; 
		$rqt_a = "SELECT categ_id FROM categories WHERE categ_parent='".$id_rech_theme."' AND categ_libelle='".addslashes($libelle_final)."' " ;
		$res_a = pmb_mysql_query($rqt_a) ;
		if (pmb_mysql_num_rows($res_a)) {
			$categid_a = pmb_mysql_result($res_a, 0, 0) ;
		} else {
			$rqt_ajout = "insert into categories set categ_parent='".$id_rech_theme."', categ_libelle='".addslashes($libelle_final)."', index_categorie=' ".strip_empty_words($libelle_final)." ' " ;
			pmb_mysql_query($rqt_ajout);
			$categid_a = pmb_mysql_insert_id();
		}
		// r�cup des sous-categ en cascade sous $a
		$categ_parent =  $categid_a ;
		$nb_info_606_x = count($info_606_x[$a]);
		for ($x = 0; $x < $nb_info_606_x; $x++) {
			$rqt_x = "SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_x[$a][$x])."' " ;
			$res_x = pmb_mysql_query($rqt_x) ;
			if (pmb_mysql_num_rows($res_x)) {
				$categ_parent = pmb_mysql_result($res_x, 0, 0) ;
			} else {
				$rqt_ajout = "insert into categories set categ_parent='".$categ_parent."', categ_libelle='".addslashes($info_606_x[$a][$x])."', index_categorie=' ".strip_empty_words($info_606_x[$a][$x])." ' " ;
				pmb_mysql_query($rqt_ajout);
				$categ_parent = pmb_mysql_insert_id();
			}
		} // fin r�cup des $x en cascade sous l'id de la cat�gorie 606$a
		
		if ($categ_parent != $id_rech_theme) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', notcateg_categorie='".$categ_parent."' " ;
			pmb_mysql_query($rqt_ajout);
		}
				
		// r�cup des categ g�o � loger sous la categ g�o principale
		$categ_parent =  $id_rech_geo ;
		$nb_info_606_y = count($info_606_y[$a]);
		for ($y = 0; $y < $nb_info_606_y; $y++) {
			$rqt_y = "SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_y[$a][$y])."' " ;
			$res_y = pmb_mysql_query($rqt_y) ;
			if (pmb_mysql_num_rows($res_y)) {
				$categ_parent = pmb_mysql_result($res_y, 0, 0) ;
			} else {
				$rqt_ajout = "insert into categories set categ_parent='".$categ_parent."', categ_libelle='".addslashes($info_606_y[$a][$y])."', index_categorie=' ".strip_empty_words($info_606_y[$a][$y])." ' " ;
				pmb_mysql_query($rqt_ajout);
				$categ_parent = pmb_mysql_insert_id();
			}
		} // fin r�cup des $y en cascade sous l'id de la cat�gorie principale th�me g�o
		
		if ($categ_parent != $id_rech_geo) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', notcateg_categorie='".$categ_parent."' " ;
			pmb_mysql_query($rqt_ajout);
		}
		
		// r�cup des categ chrono � loger sous la categ chrono principale
		$categ_parent =  $id_rech_chrono ;
		$nb_info_606_z = count($info_606_z[$a]);
		for ($z = 0; $z < $nb_info_606_z; $z++) {
			$rqt_z = "SELECT categ_id FROM categories WHERE categ_parent='".$categ_parent."' AND categ_libelle='".addslashes($info_606_z[$a][$z])."' " ;
			$res_z = pmb_mysql_query($rqt_z) ;
			if (pmb_mysql_num_rows($res_z)) {
				$categ_parent = pmb_mysql_result($res_z, 0, 0) ;
			} else {
				$rqt_ajout = "insert into categories set categ_parent='".$categ_parent."', categ_libelle='".addslashes($info_606_z[$a][$z])."', index_categorie=' ".strip_empty_words($info_606_z[$a][$z])." ' " ;
				pmb_mysql_query($rqt_ajout);
				$categ_parent = pmb_mysql_insert_id() ;
			}
		} // fin r�cup des $z en cascade sous l'id de la cat�gorie principale th�me chrono
		
		if ($categ_parent != $id_rech_chrono) {
			// insertion dans la table notices_categories
			$rqt_ajout = "insert into notices_categories set notcateg_notice='".$notice_id."', notcateg_categorie='".$categ_parent."' " ;
			pmb_mysql_query($rqt_ajout);
		}
	}
	
} // fin import_new_notice_suite
			
// TRAITEMENT DES EXEMPLAIRES ICI
function traite_exemplaires () {
	global $nb_expl_ignores;
	
	global $prix, $notice_id, $tdoc_codage, $book_lender_id, 
		$sdoc_codage, $book_statut_id, $locdoc_codage,
		$cote_mandatory ;
		
	global $info_997 ;
	global $idcode_stat_expl ;
	
	// lu en 010$d de la notice
	$price = $prix[0];
	
	$nb_infos_997 = count($info_997);
	// la zone 997 est r�p�table ?
	for ($nb_expl = 0; $nb_expl < $nb_infos_997; $nb_expl++) {
		/* RAZ expl */
		$expl = array();
		
		/* pr�paration du tableau � passer � la m�thode */
		if (!$info_997[$nb_expl]['a']) $expl['cb'] = "ABS $notice_id" ;
			else $expl['cb'] = $info_997[$nb_expl]['a'];
		$expl['notice']     = $notice_id ;
		
		// Type de document
		$data_doc=array();
		$data_doc['tdoc_libelle'] = $info_997[$nb_expl]['t']." -Import�";
		$data_doc['duree_pret'] = 0 ; /* valeur par d�faut */
		$data_doc['tdoc_codage_import'] = $info_997[$nb_expl]['t'] ;
		if ($tdoc_codage) $data_doc['tdoc_owner'] = $book_lender_id ;
			else $data_doc['tdoc_owner'] = 0 ;
		$expl['typdoc'] = docs_type::import($data_doc);
		
		// cote du document : $3 ?
		$expl['cote'] = $info_997[$nb_expl]['3'];
                      	
		// Section
		$data_doc=array();
		$data_doc['section_libelle'] = $info_997[$nb_expl]['d']." -Import�";
		$data_doc['sdoc_codage_import'] = $info_997[$nb_expl]['d'] ;
		if ($sdoc_codage) $data_doc['sdoc_owner'] = $book_lender_id ;
			else $data_doc['sdoc_owner'] = 0 ;
		$expl['section'] = docs_section::import($data_doc);
		
		// Statut : choisi lors de l'import
		$expl['statut'] = $book_statut_id;
		
		// Localisation
		$data_doc=array();
		$data_doc['location_libelle'] = $info_997[$nb_expl]['c']."-Import�";
		$data_doc['locdoc_codage_import'] = $info_997[$nb_expl]['c'] ;
		if ($locdoc_codage) $data_doc['locdoc_owner'] = $book_lender_id ;
			else $data_doc['locdoc_owner'] = 0 ;
		$expl['location'] = docs_location::import($data_doc);
		
		// Code statistique : fix� dans le param�trage
		$expl['codestat'] = $idcode_stat_expl;
		
		$expl['prix']       = $price;
		$expl['expl_owner'] = $book_lender_id ;
		$expl['cote_mandatory'] = $cote_mandatory ;
		
		$expl_id = exemplaire::import($expl);
		if ($expl_id == 0) {
			$nb_expl_ignores++;
		}
                      	
	} // fin for
} // fin traite_exemplaires	TRAITEMENT DES EXEMPLAIRES JUSQU'ICI

// fonction sp�cifique d'export de la zone 995
function export_traite_exemplaires ($ex=array()) {
	return import_expl::export_traite_exemplaires($ex);
}