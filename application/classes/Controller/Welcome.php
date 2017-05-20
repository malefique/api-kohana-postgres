<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller {

	public function action_index()
	{
        phpinfo();
		//$this->response->body(View::factory('dummy'));
	}

    /*public function action_test(){
        $this->response->body('working!');
    }*/
} // End Welcome
