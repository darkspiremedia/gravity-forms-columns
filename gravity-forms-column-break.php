<?php
/*
Plugin Name: Gravity Forms: Column Break
Plugin URI: https://github.com/darkspiremedia/gravity-forms-column-break
Description: Adds a column break field to Gravity Forms.
Author: Darkspire Media
Twitter: @darkspireinc
Author URI: http://darkspire.media
Version: 1.02
License: GPL
Copyright: Darkspire, Inc.
Credit: http://www.jordancrown.com/revisited-multi-column-gravity-forms/
*/

if(!class_exists('GF_Field_Column') && class_exists('GF_Field')){
    class GF_Field_Column extends GF_Field {

        public $type = 'column';

        public function get_form_editor_field_title() {
            return esc_attr__('Column Break', 'gravityforms');
        }

        public function is_conditional_logic_supported() {
            return false;
        }

        function get_form_editor_field_settings() {
            return array(
                'column_description',
                'css_class_setting'
            );
        }

        public function get_field_input($form, $value = '', $entry = null) {
            return '';
        }

        public function get_field_content($value, $force_frontend_label, $form) {

            $is_entry_detail = $this->is_entry_detail();
            $is_form_editor = $this->is_form_editor();
            $is_admin = $is_entry_detail || $is_form_editor;

            if($is_admin) {
                $admin_buttons = $this->get_admin_buttons();
                return $admin_buttons.'<label class=\'gfield_label\'>'.$this->get_form_editor_field_title().'</label>{FIELD}<hr>';
            }

            return '';
        }

    }
}

function register_gf_field_column(){
    if(!class_exists('GFForms') || !class_exists('GF_Field_Column')) return;
    GF_Fields::register(new GF_Field_Column());
}
add_action('init', 'register_gf_field_column', 20);

function add_gf_field_column_settings($placement, $form_id){
    if($placement == 0) {
        $description = 'Column breaks should be placed between fields to split form into separate columns. You do not need to place any column breaks at the beginning or end of the form, only in the middle.';
        echo '<li class="column_description field_setting">'.$description.'</li>';
    }
}
add_action('gform_field_standard_settings', 'add_gf_field_column_settings', 10, 2);

function filter_gf_field_column_container($field_container, $field, $form, $css_class, $style, $field_content){
	if(IS_ADMIN) return $field_container;
	if($field['type'] == 'column'){
		$gf_column_count = 0;
		foreach($form['fields'] as $f){if($f['type'] == 'column'){$gf_column_count++;}}
		$column_index = 1;
		foreach($form['fields'] as $form_field){
			if($form_field['id'] == $field['id']) break;
			if($form_field['type'] == 'column') $column_index++;
		}
		return '</ul><ul class="'.GFCommon::get_ul_classes($form).' column column_'.$column_index.' '.$field['cssClass'].'columns_'.$gf_column_count.'">';
	}
	return $field_container;
}
add_filter('gform_field_container', 'filter_gf_field_column_container', 10, 6);

function gf_field_column_scripts(){
	if(!is_admin()){
		wp_enqueue_style('gf-field-column',plugins_url('gravity-forms-column-break.css',__FILE__),false);
	}
}
add_action('wp_enqueue_scripts','gf_field_column_scripts');

//Added to check if Gravity Forms is installed on activation.
function gf_field_column_activate() {

    if (class_exists('RGFormsModel')) {
            
            return true;
            
        }   else {
            
            $html = '<div class="error">';
                $html .= '<p>';
                    $html .= _e( 'Warning: Gravity Forms is not installed or activated. This plugin does not function without Gravity Forms!' );
                $html .= '</p>';
            $html .= '</div>';
            echo $html;
            
        }
}
register_activation_hook( __FILE__, 'gf_field_column_activate' );