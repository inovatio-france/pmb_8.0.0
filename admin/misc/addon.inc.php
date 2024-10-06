<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: addon.inc.php,v 1.8.2.16 2024/09/04 09:33:13 jparis Exp $

use Pmb\CMS\Orm\VersionOrm;
use Pmb\CMS\Models\PortalModel;
use Pmb\CMS\Models\PortalRootModel;
use Pmb\CMS\Models\PageModel;
use Pmb\CMS\Models\LayoutModel;
use Pmb\CMS\Models\ConditionModel;
use Pmb\CMS\Models\LayoutElementModel;
use Pmb\CMS\Models\LayoutContainerModel;

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if( !function_exists('traite_rqt') ) {
	function traite_rqt($requete="", $message="") {

		global $charset;
		$retour="";
		if($charset == "utf-8"){
			$requete=encoding_normalize::utf8_normalize($requete);
		}
		pmb_mysql_query($requete) ;
		$erreur_no = pmb_mysql_errno();
		if (!$erreur_no) {
			$retour = "Successful";
		} else {
			switch ($erreur_no) {
				case "1060":
					$retour = "Field already exists, no problem.";
					break;
				case "1061":
					$retour = "Key already exists, no problem.";
					break;
				case "1091":
					$retour = "Object already deleted, no problem.";
					break;
				default:
					$retour = "<font color=\"#FF0000\">Error may be fatal : <i>".pmb_mysql_error()."<i></font>";
					break;
			}
		}
		return "<tr><td><font size='1'>".($charset == "utf-8" ? encoding_normalize::utf8_normalize($message) : $message)."</font></td><td><font size='1'>".$retour."</font></td></tr>";
	}
}
echo "<table>";

/******************** AJOUTER ICI LES MODIFICATIONS *******************************/

switch ($pmb_bdd_subversion) {
    case 0:
        // GN - Ajout d'un message qui indique si l'emprunteur possède l'exemplaire
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'selfservice' and sstype_param='already_loaned' "))==0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
            VALUES (0, 'selfservice', 'already_loaned', 'l\'emprunteur possède l\'exemplaire', '1', 'Ajout d\'un message qui indique si l\'emprunteur possède l\'exemplaire') ";
            echo traite_rqt($rqt,"INSERT selfservice_already_loaned INTO parametres") ;
        }

        // GN - Ajout d'un message pour la gestion du statut de la réservations de l'exemplaire
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'selfservice' and sstype_param='expl_status' "))==0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
            VALUES (0, 'selfservice', 'expl_status', '', '1', 'Ajout d\'un message pour la gestion des statuts de l\'exemplaire') ";
            echo traite_rqt($rqt,"INSERT selfservice_expl_status INTO parametres") ;
        }
    case 1:
        // DG - Ajout index sur num_object et type_object de la table vedette_link
        $add_index = true;
        $req = "SHOW INDEX FROM vedette_link WHERE Key_name='i_object' ";
        $res = pmb_mysql_query($req);
        if($res && pmb_mysql_num_rows($res)){
            $add_index=false;
        }
        if($add_index){
            $rqt = "ALTER TABLE vedette_link ADD INDEX i_object(num_object, type_object)";
            echo traite_rqt($rqt,"alter table vedette_link add index i_object");
        }

    case 2:
        // JP - Ajout d'un paramètre pour la taille maximum d'un logo dans le contenu éditorial
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param= 'cms' AND sstype_param='img_pics_max_size'"))==0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
					VALUES (0, 'cms', 'img_pics_max_size', '640', 'Taille maximale des logos du contenu éditorial, en largeur ou en hauteur', '', 0)";
            echo traite_rqt($rqt, "INSERT cms_img_pics_max_size = 640 INTO parameters");
        }

    case 3:
    	// QV - [Refonte Portail] Correction des types et sous-types des pages        
        $query = "SELECT id FROM portal_version";
        $res = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($res) > 0) {
            while ($row = pmb_mysql_fetch_assoc($res)) {
                PortalModel::$instances = [];
    		    PortalModel::$nbInstance = 0;
    		    
    		    PageModel::$instances = [];
    		    PageModel::$nbInstance = 0;
    		    
    		    LayoutModel::$instances = [];
    		    LayoutModel::$nbInstance = 0;
    		    
    		    ConditionModel::$instances = [];
    		    ConditionModel::$nbInstance = 0;
    		    
    		    LayoutElementModel::$instances = [];
    		    LayoutElementModel::$nbInstance = 0;
    		    
    		    LayoutContainerModel::$instances = [];
    		    LayoutContainerModel::$nbInstance = 0;
    		    
                $portal = PortalModel::getPortal($row["id"]);
     			$pages = $portal->getPages();
    			if (empty($pages)) {
    				// Aucune page
    				continue;
    			}

    			$isModified = false;
    			array_walk($pages, function ($page) use (&$isModified) {

    				if ($page->type == '39' && intval($page->subType / 100) == 40) {
    					// Cas specifique pour les animations (correction du type de page)
    					$page->type = '40';
    					$isModified = true;
    				}

    				if ($page->type == '35' && $page->subType == '3401') {
    					// Cas specifique pour les segments (correction du sous-type de page)
    					$page->subType = '3501';
    					$isModified = true;
    				}

    				if ($page->type == '34' && $page->subType == '3301') {
    					// Cas specifique pour les univers (correction du sous-type de page)
    					$page->subType = '3401';
    					$isModified = true;
    				}

    				return $page;
    			});

				if (!$isModified) {
					// Aucune modification
					continue;
				}

				$portal->pages = $pages;
				$properties_serialised = \encoding_normalize::json_encode($portal->serialize());
				if (!empty($properties_serialised)) {
				    $version = new VersionOrm($row["id"]);
					$version->properties = gzcompress($properties_serialised);
					$version->save();
				}
    		}
    		echo traite_rqt("SELECT 1", "[Refonte Portail] Correction des types et sous-types des pages");
    	}
    case 4:

        // GN - Ajout d'une table pour stocker les paramètres des listes de lecture pour l'IA
        $rqt = "CREATE TABLE IF NOT EXISTS ai_shared_list (
                    id_ai_shared_list int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    settings_ai_shared_list mediumblob NOT NULL)";
        echo traite_rqt($rqt,"CREATE TABLE ai_shared_list");

        // GN - JP - Ajout d'une colonne FLAG pour l'indexation des notices de la liste de lecture dans le module IA
        $rqt = "ALTER TABLE opac_liste_lecture_notices ADD opac_liste_lecture_flag_ia int(1) UNSIGNED NOT NULL DEFAULT 0";
        echo traite_rqt($rqt,"ALTER TABLE opac_liste_lecture_notices ADD opac_liste_lecture_flag_ia");

        // GN - JP - Ajout d'un paramètre pour le nombre d'éléments à indexer par passe dans le module IA
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param='ai' AND sstype_param='index_nb_elements'")) == 0) {
            $rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                        VALUES ('ai', 'index_nb_elements', '5', 'Nombre d\'éléments traités par passe d\'indexation', '', 0)";
            echo traite_rqt($rqt, 'INSERT index_nb_elements INTO parametres artificial_intelligence');
        }

        // GN - JP - Ajout d'un paramètre pour la taille maximun d'un fichier upload dans le module IA
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param='ai' AND sstype_param='upload_max_size'")) == 0) {
            $rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                        VALUES ('ai', 'upload_max_size', '100', 'Poids maximun d\'un fichier en Mo lors de l\'upload', '', 0)";
            echo traite_rqt($rqt, 'INSERT upload_max_size INTO parametres artificial_intelligence');
        }

        // GN - JP - Création de la table des documents associés à la liste de lecture dans le module IA
        $rqt = "CREATE TABLE IF NOT EXISTS ai_shared_list_docnum (
                    id_ai_shared_list_docnum INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name_ai_shared_list_docnum VARCHAR(255) NOT NULL DEFAULT '',
                    content_ai_shared_list_docnum MEDIUMTEXT NOT NULL DEFAULT '',
                    mimetype_ai_shared_list_docnum VARCHAR(255) NOT NULL DEFAULT '',
                    extfile_ai_shared_list_docnum VARCHAR(20) NOT NULL DEFAULT '',
                    path_ai_shared_list_docnum VARCHAR(255) NOT NULL DEFAULT '',
                    hash_name_ai_shared_list_docnum MEDIUMTEXT NOT NULL DEFAULT '',
                    hash_binary_ai_shared_list_docnum MEDIUMTEXT NOT NULL DEFAULT '',
                    num_list_ai_shared_list_docnum INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    flag_ai_shared_list_docnum INT(1) UNSIGNED NOT NULL DEFAULT 0
            )";
        echo traite_rqt($rqt,"CREATE TABLE ai_shared_list_docnum");

    case 5:
    	// JP & TS & QV - Ajout de la colonne num_list_ai_session_semantique
    	$rqt = "ALTER TABLE ai_session_semantique ADD ai_session_semantique_type INT(11) UNSIGNED NOT NULL DEFAULT 0";
    	echo traite_rqt($rqt, "ALTER TABLE ai_session_semantique ADD ai_session_semantique_type");

    	// JP & TS & QV - Ajout de la colonne num_list_ai_session_semantique
    	$rqt = "CREATE TABLE ai_session_shared_list (
            num_ai_session_semantique INT(11) UNSIGNED NOT NULL,
            num_empr INT(11) UNSIGNED NOT NULL,
            num_shared_list INT(11) UNSIGNED NOT NULL,
            UNIQUE KEY (num_ai_session_semantique, num_empr, num_shared_list)
        )";
    	echo traite_rqt($rqt, "CREATE TABLE ai_session_shared_list");
    	
    case 6:
        //JP - Ajout d'un paramètre pour le remplacement du champ identifiant par le champ mail dans le formulaire du lecteur à l'OPAC
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param='empr' AND sstype_param='username_with_mail'")) == 0) {
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, gestion, comment_param)
                    VALUES (0, 'empr', 'username_with_mail', '0', '1', 'Activer le remplacement du champ identifiant par le champ mail dans le formulaire changement de profil à l\'OPAC\n 0: Non \n 1: Oui') ";
            echo traite_rqt($rqt, 'INSERT username_with_mail INTO parametres');
        }
        
    case 7:
        //JP - Nombre de notices max diffusées dans une bannette par mail
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'dsi' AND sstype_param = 'bannette_max_nb_notices_per_mail'")) == 0){
            $rqt = "INSERT INTO parametres (type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                    VALUES ('dsi', 'bannette_max_nb_notices_per_mail', '100', 'Nombre maximum de notices diffusées dans une bannette par mail.', '', 0)";
            echo traite_rqt($rqt, "INSERT dsi_bannette_max_nb_notices_per_mail INTO parametres");
        }
    case 8:
        // JP - Ajout d'une table pour gérer la diffusion manuelle
        $rqt = "CREATE TABLE IF NOT EXISTS dsi_send_queue (
            id_send_queue INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            channel_type VARCHAR(255) NOT NULL DEFAULT '',
            settings mediumblob NOT NULL,
            num_subscriber_diffusion INT(11) UNSIGNED NOT NULL,
            num_diffusion_history INT(11) UNSIGNED NOT NULL,
            flag INT(1) UNSIGNED NOT NULL DEFAULT 0
        )";
        echo traite_rqt($rqt, "CREATE TABLE dsi_send_queue");
    case 9:
        //JP - QV - Activer la mise en cache des images dans les animations
        if (pmb_mysql_num_rows(pmb_mysql_query("SELECT 1 FROM parametres WHERE type_param = 'animations' AND sstype_param ='active_image_cache' ")) == 0){
            $rqt = "INSERT INTO parametres (id_param, type_param, sstype_param, valeur_param, comment_param, section_param, gestion)
                    VALUES (0, 'animations', 'active_image_cache', '0', 'Activer la mise en cache des images dans les animations\n 0: Non \n 1: Oui', '', 0)";
            echo traite_rqt($rqt,"INSERT animations_active_image_cache INTO parametres");
        }
    case 10:
        // TS - Ajout d'une nouvelle option du parametre resa_alert_localized pour les notifications aux utilisateurs du site de retrait
        $rqt = "update parametres set comment_param='Mode de notification par email des nouvelles réservations aux utilisateurs ? \n0 : Recevoir toutes les notifications \n1 : Notification des utilisateurs du site de gestion du lecteur \n2 : Notification des utilisateurs associés à la localisation par défaut en création d\'exemplaire \n3 : Notification des utilisateurs du site de gestion et de la localisation d\'exemplaire \n4 : Notification des utilisateurs du site de retrait' where type_param= 'pmb' and sstype_param='resa_alert_localized' ";
        echo traite_rqt($rqt,"update pmb_resa_alert_localized into parametres");
    case 11:
    	// RT - Ajout d'un paramètre utilisateur permettant de définir un propriétaire par défaut en import d'exemplaires UNIMARC
    	$rqt = "ALTER TABLE users ADD deflt_import_lenders TINYINT UNSIGNED DEFAULT 1 NOT NULL ";
    	echo traite_rqt($rqt, "ALTER TABLE users ADD deflt_import_lenders");
}

/******************** JUSQU'ICI **************************************************/
/* PENSER à faire +1 au paramètre $pmb_subversion_database_as_it_shouldbe dans includes/config.inc.php */
/* COMMITER les deux fichiers addon.inc.php ET config.inc.php en même temps */

echo traite_rqt("update parametres set valeur_param='".$pmb_subversion_database_as_it_shouldbe."' where type_param='pmb' and sstype_param='bdd_subversion'","Update to $pmb_subversion_database_as_it_shouldbe database subversion.");
echo "<table>";