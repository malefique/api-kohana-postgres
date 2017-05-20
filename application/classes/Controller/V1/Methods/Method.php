<?php defined('SYSPATH') or die('No direct script access.');

class Controller_V1_Methods_Method extends Controller{
    public function array_to_xml($student_info,&$xml_student_info,$title='Element')
    {
        foreach($student_info as $key => $value)
        {
            if(is_array($value))
            {
                if(!is_numeric($key))
                {
                    $subnode = $xml_student_info->addChild($key);
                    $this->array_to_xml($value, $subnode);
                }
                else{
                    $subnode = $xml_student_info->addChild($title);
                    $subnode->addAttribute('id',$key);
                    $this->array_to_xml($value, $subnode);
                }
            }
            else {
                $xml_student_info->addChild($key,htmlspecialchars($value));
            }
        }
    }
}