<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: upload_frame.php,v 1.3 2022/09/14 14:38:50 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// définition du minimum nécéssaire
$base_path="../..";
$base_auth = "CATALOGAGE_AUTH";
$base_title = "\$msg[7]";    

$include_path = "$base_path/includes";
$class_path = "$base_path/classes";

require_once "$base_path/includes/init.inc.php";
require_once "$class_path/upload_folder.class.php";
require_once $include_path.'/templates/upload_folder.tpl.php';

print "<div id='att' style='z-Index:1000'></div>";

print $up_frame;
