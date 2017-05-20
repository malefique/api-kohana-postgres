<?php
/**
 * Created by PhpStorm.
 * User: Олег
 * Date: 02.08.2015
 * Time: 8:11
 */

class Controller_Providers_Provider extends Controller{
    protected $obj;
    public function action_route()
    {
        $id = $this->request->param('provider');
        $all = 'Phelper_Provider'.$id;
        if(!class_exists($all)){
            throw HTTP_Exception::factory(404,
                'The requested URL :uri was not found on this server.',
                array(':uri' => $this->request->uri())
            )->request($this->request);
        }
        else{
            $this->obj = new $all;
        }
    }
}