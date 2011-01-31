<?php
// Auto-add the session token (ignored if not using sessions)
if(isset($validation) AND $error = $validation->error('token'))
{
	print html::tag('div', $error, array('class'=>'form_error'));
}

print html::tag('input', 0, array('type'=>'hidden','value'=>session('token'),'name'=>'token'));


foreach($fields as $field => $data)
{
	print "\n\n<div".(isset($data['div'])?html::attributes($data['div']):'').'>';
	
	if( ! isset($data['attributes']['type']) OR ! in_array($data['attributes']['type'], array('hidden','submit','reset','button')))
	{
		print html::tag('label', $data['label'], array('for'=>$field));
	}
	
	if($data['type'] === 'select') // Select box
	{
		print html::select($field, $data['options'], $data['value'], $data['attributes']);
	}
	elseif($data['type'] === 'textarea') // Textarea
	{
		print html::tag($data['type'], str($data['value']), $data['attributes']);
	}
	elseif($data['attributes']['type'] === 'datetime') // Special datetime type
	{
		print html::datetime($data['value'], $field);
	}
	else // a normal input
	{
		print html::tag($data['type'], 0, $data['attributes']+array('value' => str($data['value'])));
	}

	if(isset($validation) AND $error = $validation->error($field))
	{
		print html::tag('div', $error, array('class'=>'form_error'));
	}
	
	print "\n</div>";
}
