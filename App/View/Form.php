<?php
// Auto-add the session token (ignored if not using sessions)
if(isset($validation) AND $error = $validation->error('token'))
{
	print \Core\HTML::tag('div', $error, array('class'=>'form_error'));
}

print \Core\HTML::tag('input', 0, array('type' => 'hidden', 'value' => session('token'), 'name' => 'token'));


foreach($fields as $field => $data)
{
	print "\n\n<div".(isset($data['div'])?\Core\HTML::attributes($data['div']):'').'>';

	if( ! isset($data['attributes']['type']) OR ! in_array($data['attributes']['type'], array('hidden','submit','reset','button')))
	{
		print \Core\HTML::tag('label', $data['label'], array('for'=>$field));
	}

	if($data['type'] === 'select') // Select box
	{
		print \Core\HTML::select($field, $data['options'], $data['value'], $data['attributes']);
	}
	elseif($data['type'] === 'textarea') // Textarea
	{
		print \Core\HTML::tag($data['type'], str($data['value']), $data['attributes']);
	}
	elseif($data['attributes']['type'] === 'datetime') // Special datetime type
	{
		print \Core\HTML::datetime($data['value'], $field);
	}
	else // a normal input
	{
		print \Core\HTML::tag($data['type'], 0, $data['attributes']+array('value' => str($data['value'])));
	}

	if(isset($validation) AND $error = $validation->error($field))
	{
		print \Core\HTML::tag('div', $error, array('class'=>'form_error'));
	}

	print "\n</div>";
}
