<?php
// Shows an example of using the ORM
class Example_Controller_School extends Controller
{

	public function action()
	{
		$this->load_database();

		// You can over-ride this in certain models if needed,
		// allowing you to use multiple databases.
		// Model_Name::$db = new DB(config('other_database'));

		// New Dorm
		$d = new Example_Model_Dorm();
		$d->name = 'Dorm 1';
		$d->save();

		// New Student in Dorm
		$s1 = new Example_Model_Student();
		$s1->name = 'Mary';
		$s1->dorm_id = $d->id;
		$s1->save();

		// New Student in Dorm
		$s2 = new Example_Model_Student();
		$s2->name = 'Jane';
		$s2->dorm_id = $d->id;
		$s2->save();

		// New Car for student
		$c = new Example_Model_Car();
		$c->name = 'Truck';
		$c->student_id = $s1->id;
		$c->save(); // Insert

		$c->name = $s1->name. '\'s Truck'; // Change car name
		$c->save(); // Update

		// New Softball club
		$c = new Example_Model_Club();
		$c->name = 'Softball';
		$c->save();

		// Mary is in softball
		$m = new Example_Model_Membership();
		$m->club_id = $c->id;
		$m->student_id = $s1->id;
		$m->save();

		// Jane is in softball
		$m = new Example_Model_Membership();
		$m->club_id = $c->id;
		$m->student_id = $s2->id;
		$m->save();

		$this->content = dump('Created school objects');

		$clubs = Example_Model_Club::fetch();
		foreach($clubs as $club)
		{
			$club->load();
			foreach($club->students() as $student)
			{
				/*
				 * This student may have already been removed
				 */
				if($student->load())
				{
					$this->content .= dump('Removing '. $student->name. ' and her records');

					// Remove their car, club membership, and them
					$student->delete();
				}
			}
			$club->delete();
		}

		foreach(Example_Model_Dorm::fetch() as $dorm)
		{
			$this->content .= dump('Removing the '. $dorm->name. ' dorm');
			$dorm->delete(); // Delete the dorm
		}

		$this->content .= dump('Removed school objects');

		// Load the view file
		$this->content .= new View('school', 'example');

		// Load global theme sidebar
		$this->sidebar = new View('sidebar');

	}
}
