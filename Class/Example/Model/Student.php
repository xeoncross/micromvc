<?php
/**
 * Student Model
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Example\Model;

class Student extends \Micro\ORM
{
	public static $table = 'student';
	public static $foreign_key = 'student_id';
	public static $cascade_delete = TRUE;

	public static $has = array(
		'car' => '\Example\Model\Car',
		'memberships' => '\Example\Model\Membership'
	);

	public static $belongs_to = array(
		'dorm' => '\Example\Model\Dorm',
	);

	public static $has_many_through = array(
		'clubs' => array(
			'student_id' => '\Example\Model\Membership',
			'club_id' => '\Example\Model\Club'
		),
	);
}
