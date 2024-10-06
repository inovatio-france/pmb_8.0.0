<?php
 // +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: options_date_box.php,v 1.11 2021/05/10 07:03:49 dgoron Exp $

//Gestion des options de type text
$base_path = "../..";
$base_auth = "CATALOGAGE_AUTH|ADMINISTRATION_AUTH";
$base_title = "";
include ($base_path."/includes/init.inc.php");

require_once ("$class_path/options/options_controller.class.php");
require_once ("$class_path/options/options_date_box.class.php");

options_controller::set_model_class_name('options_date_box');
options_controller::set_display_type('custom_field');
options_controller::proceed();