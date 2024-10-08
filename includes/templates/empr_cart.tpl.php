<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: empr_cart.tpl.php,v 1.37 2022/05/12 06:53:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

// templates pour la gestion des paniers

global $empr_cart_content_form, $msg, $current_module, $liaison_tpl, $empr_cart_choix_quoi, $empr_cart_choix_quoi_edition;

// template pour le formulaire d'un panier
$empr_cart_content_form = "
<!--	type	-->
<!--memo_contexte-->
<div class='row'>
	<label class='etiquette' for='cart_name'>".$msg['caddie_name']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='cart_name' name='cart_name' value='!!name!!' />
	!!infos_creation!!
</div>
<div class='row'>
	<label class='etiquette' for='cart_comment'>".$msg['caddie_comment']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='cart_comment' name='cart_comment' value='!!comment!!' />
</div>
<div class='row'>
	<label class='etiquette' for='autorisations_all'>".$msg["caddie_autorisations_all"]."</label>
	<input type='checkbox' id='autorisations_all' name='autorisations_all' value='1' !!autorisations_all!! />
</div>
<div class='row'>
	<label class='etiquette' for='form_type'>".$msg['caddie_autorisations']."</label>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_cocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,1);'>
	<input type='button' class='bouton_small align_middle' value='".$msg['tout_decocher_checkbox']."' onclick='check_checkbox(document.getElementById(\"auto_id_list\").value,0);'>
</div>
<div class='row'>
	!!autorisations_users!!
</div>
<div class='row'>
	<label class='etiquette' for='classementGen_!!object_type!!'>".$msg['empr_caddie_classement_list']."</label>
</div>
<div class='row'>
	<select data-dojo-type='dijit/form/ComboBox' id='classementGen_!!object_type!!' name='classementGen_!!object_type!!'>
		!!classements_liste!!
	</select>
</div>
<div id='div_acces_rapide' class='row'>
	<label class='etiquette' for='acces_rapide'>".$msg["caddie_fast_access"]."</label>&nbsp;<input type='checkbox' id='acces_rapide' name='acces_rapide' !!acces_rapide!! >
</div>
<div id='div_favorite_color' class='row'>
	<label class='etiquette' for='favorite_color'>".$msg["caddie_favorite_color"]."</label>&nbsp;<input type='color' id='favorite_color' name='favorite_color' value='!!favorite_color!!' list='favorite_colors'>
	<datalist id='favorite_colors'>
		!!datalist_options_favorite_colors!!
	</datalist>
</div>
<!-- liaisons -->
<input type='hidden' name='form_actif' value='1'>
";

$liaison_tpl = "
<div id='el0Parent' class='parent' >
	<h3>
	<img src='".get_url_icon('minus.gif')."' class='img_moins align_bottom' name='imEx' id='el0Img' title='$msg[empr_caddie_used_in]' border='0' onClick=\"expandBase('el0', true); return false;\" />
	$msg[empr_caddie_used_in]
	</h3>
</div>
<div id='el0Child' class='child'>
	<!-- info_liaisons -->
</div>
<div class='row'>&nbsp;</div>";

// $empr_cart_choix_quoi : template form choix des �l�ments � traiter
$empr_cart_choix_quoi = "
<script type='text/javascript'>
	function test_form(form) {
		if(!form.elt_flag.checked && !form.elt_no_flag.checked) {
			alert('".addslashes($msg['caddie_no_elements_for_cart'])."');
			return false;
		}
		return true;
	}
</script>
<hr />
<form class='form-$current_module' name='maj_proc' method='post' action='!!action!!' >
	<h3>!!titre_form!!</h3>
	<!--	Contenu du form	-->
	<div class='form-contenu'>
		<div class='row'>
			<input type='checkbox' name='elt_flag' id='elt_flag' value='1' !!elt_flag_checked!!><label for='elt_flag'>$msg[caddie_item_marque]</label>
		</div>
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<input type='checkbox' name='elt_no_flag' id='elt_no_flag' value='1' !!elt_no_flag_checked!!><label for='elt_no_flag'>$msg[caddie_item_NonMarque]</label>
		</div>
	</div>
	<!-- Boutons -->
	<div class='row'>
		<input type='button' class='bouton' value='$msg[76]' onClick='document.location=\"!!action_cancel!!\"' />&nbsp;
		<input type='submit' class='bouton' value='!!bouton_valider!!' onClick=\"if(!test_form(this.form)) {return false;} else {!!onclick_valider!!}\"/>&nbsp;
	</div>
</form>
";

// $empr_cart_choix_quoi_edition : template form choix des �l�ments � �diter
$empr_cart_choix_quoi_edition = "
<hr />
<form class='form-$current_module' name='maj_proc' method='post' action='!!action!!' target='_blank' >
	<h3>!!titre_form!!</h3>
	<!--	Contenu du form	-->
	<div class='form-contenu'>
		<div class='row'>
			<input type='checkbox' name='elt_flag' id='elt_flag' value='1'><label for='elt_flag'>$msg[caddie_item_marque]</label>
		</div>
		<div class='row'>
			<input type='checkbox' name='elt_no_flag' id='elt_no_flag' value='1'><label for='elt_no_flag'>$msg[caddie_item_NonMarque]</label>
		</div>
	</div>
	<!-- Boutons -->
	<div class='row'>
		<input type='button' class='bouton' value='$msg[76]' onClick='document.location=\"!!action_cancel!!\"' />&nbsp;
		<!-- !!boutons_supp!! -->
	</div>
</form>
";