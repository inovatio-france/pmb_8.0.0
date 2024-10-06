<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: options_text.class.php,v 1.1 2021/05/10 07:03:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once ($class_path."/options/options.class.php");

class options_text extends options {
    
	public function init_default_parameters() {
		parent::init_default_parameters();
		$this->parameters['SIZE'][0]['value'] = '50';
		$this->parameters['MAXSIZE'][0]['value'] = '255';
		if($this->display_type != 'custom_action') {
			$this->parameters['REPEATABLE'][0]['value'] = '';
			$this->parameters['ISHTML'][0]['value'] = '';
		}
	}
	
    public function get_content_form() {
        global $msg;
        $content_form = $this->get_line_content_form($msg["procs_options_text_taille"], 'SIZE', 'number');
        $content_form .= $this->get_line_content_form($msg["procs_options_text_max"], 'MAXSIZE', 'number');
        if($this->display_type != 'custom_action') {
	        $content_form .= $this->get_line_content_form($msg["persofield_textrepeat"], 'REPEATABLE', 'checkbox');
	        $content_form .= $this->get_line_content_form($msg["persofield_textishtml"], 'ISHTML', 'checkbox');
        }
        return $content_form;
    }
    
    public function set_parameters_from_form() {
    	global $SIZE, $MAXSIZE, $REPEATABLE, $ISHTML;
        
    	parent::set_parameters_from_form();
		$this->parameters['SIZE'][0]['value'] = intval($SIZE);
		$this->parameters['MAXSIZE'][0]['value'] = intval($MAXSIZE);
		if($this->display_type != 'custom_action') {
			$this->parameters['REPEATABLE'][0]['value'] = $REPEATABLE ? 1 : 0;
			$this->parameters['ISHTML'][0]['value'] = $ISHTML ? 1 : 0;
		}
	}
}
?>