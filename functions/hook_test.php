<?php

function hook_test() {
	return 'I am in '. __FILE__. ' being called from line '
	. __LINE__. ' of the '. __FUNCTION__. ' function<br />';
}