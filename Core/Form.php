<?php
/**
 * Form
 *
 * Creates HTML forms from arrays describing the available fields . Format:
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
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Core;

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
	public function __construct($validation = NULL, array $attributes = NULL, $view = 'Form')
	{
		parent::__construct($view);
		$this->set(array('attributes' => $attributes, 'validation' => $validation));
	}


	/**
	 * Set the form fields
	 *
	 * @param array $fields
	 */
	public function fields(array $fields)
	{
		foreach($fields as $field => &$options)
		{
			$defaults = array(
				'label' => ucwords($field),
				'value'=> post($field),
				'type' => 'text',
				'attributes' => array('id' => $field, 'name' => $field)
			);

			$options = $options + $defaults;

			if($options['type'] != 'select' AND $options['type'] != 'textarea')
			{
				$options['attributes']['type'] = $options['type'];
				$options['type']='input';
			}
		}

		$this->fields = $fields;
	}


	/**
	 * Return the current HTML form as a string
	 */
	public function __toString()
	{
		if( ! $this->attributes)
		{
			$this->attributes = array();
		}
		return HTML::tag('form', parent::__toString(), $this->attributes + array('method' => 'post'));
	}

}

// END
