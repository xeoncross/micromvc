<?php
/**
 * Form
 *
 * Creates HTML forms from arrays describing the available fields. Format:
 * 
 * $fields = array(
 *	'all_inputs' => array(
 *		'value' => '',
 *		'label' => '',
 *		'type' => '',
 *		'div' => array(),
 *		'attributes' => array()
 *	),
 *	'input_type' => array('type' => 'text'),
 *	'select_type' => array('type' => 'select', 'options' => $options);
 *	'textarea_type' => array('type' => 'textarea'),
 * );
 * 
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Form extends View
{

/**
 * Create an HTML form containing the given fields
 *
 * @param object $validation object
 * @param array $attributes
 * @param string $view
 * @return string
 */
public function __construct($validation = NULL, array $attributes = array(), $view = 'form')
{
	parent::__construct($view);$this->set(array('attributes'=>$attributes,'validation'=>$validation));
}


/**
 * Set the form fields
 * 
 * @param array $fields
 */
public function fields(array $fields)
{
	$a='attributes';$t='type';foreach($fields as$f=>&$o){$o=$o+array('label'=>ucwords($f),'value'=>post($f),$t=>'text',$a=>array('id'=>$f,'name'=>$f));if($o[$t]!='select'&&$o[$t]!='textarea'){$o[$a][$t]=$o[$t];$o[$t]='input';}}$this->fields=$fields;
}

/**
 * Return the current HTML form as a string
 */
public function __toString()
{
	return html::tag('form',parent::__toString(),$this->attributes+array('method'=>'post'));
}

}

// END