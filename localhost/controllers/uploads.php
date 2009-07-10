<?php

class uploads extends controller {

	public function index() {

		//Load the upload library
		$this->library('upload');

		//If there is a file given to upload *AND* the upload was a success!
		if($this->upload->exists() && $this->upload->do_upload()) {
			//Show success page!
			$this->views['content'] = $this->view('uploads/done');
			return;
		}

		//Else show the upload form
		$this->views['content'] = $this->view('uploads/form');

	}

}
?>