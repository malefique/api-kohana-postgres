<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: Олег
 * Date: 30.03.2015
 * Time: 10:31
 */
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

abstract class Phelper_Provider {
    protected $data;
    protected $provider_url;
    protected $provider_name;
    protected $provider_cookie;
    protected $ldata;
    protected $saved_object;
    protected $provider_id;
    protected $db;
    protected $use_cookie = false;
    protected $log_path;
    protected function get_data($method = false, $params = NULL){
        $this->saved_object = APPPATH.'/media/providers/'.$this->provider_name;
        if(!file_exists($this->saved_object)){
            $ch=curl_init($this->provider_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if($this->use_cookie)
                curl_setopt($ch,CURLOPT_COOKIE,$this->provider_cookie);
            if($method === true)
            {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                        array(
                            'logon_login' => $params['login'],
                            'logon_passwd'=> $params['password']
                        )
                    )
                );
            }
            //curl_setopt($ch, CURLOPT_HEADER, 1);
            //print_r(curl_getinfo($ch));
            $this->data=curl_exec($ch);
            curl_close($ch);
            //$this->data = file_get_contents($this->provider_url);
            //echo $this->data;
            $this->save_data();
        }
        else
            $this->data = file_get_contents($this->saved_object);
    }
    public function save_data(){
        $f = fopen($this->saved_object,'w+');
        fwrite($f,$this->data);
        fclose($f);
        /*$this->log();*/
    }

    protected function log(){
        $this->log_path = APPPATH.'/media/providers/cron.log';
        $f = fopen($this->log_path,'a+');
        fwrite($f,"Cronjob done for ".$this->provider_id." ".date('d.m.Y h:i:s')."\n");
        fclose($f);
    }
    abstract function load_object();
    abstract function to_insert($a);
    abstract function to_update($a);
    abstract function set_inactive();
    protected function load_existed_models(){
        //$db = ORM::factory('Products')->find_all(array('pid'=>$this->provider_id));
        $db = DB::select()->from('products')->where('pid','=',$this->provider_id)->execute()->as_array();
        /*$this->db = $db->obj();
        $q = $this->db->prepare("SELECT `outid` FROM `models` WHERE `sklad` = {$this->provider_id}");
        //echo "SELECT `outid` FROM `models` WHERE `sklad` = {$this->provider_id}";
        $q->execute();*/
        $a = array();
        foreach($db as $v){
            $a[]=$v['vid'];
        }
        return $a;
    }

    protected function set_price($id,$pz){
        $db = ORM::factory('Products',array('pid'=>$this->provider_id,'article_original'=>$id))->find();
        /*$q = $this->db->prepare("SELECT `nacenka`,`price_zak` FROM `models` WHERE `outid` = ?");
        $q->execute(array($id));
        $r = $q->fetchAll();*/
        if($db->price_retail > $db->price)
            return $db->price_retail;
        else
            return 0;
    }

    protected function load_existed_articles(){
        //orm is too sloooooww(((
        $db = DB::select()->from('products')->where('pid','=',$this->provider_id)->execute()->as_array();
        //$db = ORM::factory('Products')->find_all(array('pid'=>$this->provider_id));
        /*$q = $this->db->prepare("SELECT `artikul`,`id` FROM `models` WHERE `zamid` = 0");
        //echo "SELECT `outid` FROM `models` WHERE `sklad` = {$this->provider_id}";
        $q->execute();*/
        $a = array();
        $b = array();
        //print_r($db);
        foreach($db as $v){
            $a[]=$v['article'];
            $b[$v['article']]=$v['id'];
        }
        return array($a,$b);
    }

    protected function encode($s){
        $s = strtolower($s);
        $from = array("'а'","'б'","'в'","'г'","'д'","'е'","'ё'","'ж'","'з'","'и'","'й'","'к'","'л'","'м'","'н'","'о'","'п'","'р'","'с'","'т'","'у'","'ф'","'х'","'ц'","'ч'","'ш'","'щ'","'ъ'","'ы'","'ь'","'э'","'ю'","'я'",
            "'\('","'\)'","' '","'\,'","'\-'","'\"'","'!'","'«'","'»'","'&'",
            "'А'","'Б'","'В'","'Г'","'Д'","'Е'","'Ё'","'Ж'","'З'","'И'","'Й'","'К'","'Л'","'М'","'Н'","'О'","'П'","'Р'","'С'","'Т'","'У'","'Ф'","'Х'","'Ц'","'Ч'","'Ш'","'Щ'","'Ъ'","'Ы'","'Ь'","'Э'","'Ю'","'Я'","'\.'","'\''","'®'","'#'","'\?'","'№'","'%'","'\/'","':'","'`'");

        $to = array("a","b","v","g","d","e","io","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","sch","","y","","je","ju","ya",
            "","","_","","-","","","_","_","_","a","b","v","g","d","e","io","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","sch","","y","","je","ju","ya","","","","","","","","_","_","_","_");
        $out = preg_replace($from,$to,$s);
        $out = preg_replace('~&quot;~',"",$out);
        $out = preg_replace("~__~","_",$out);
        return $out;
    }

    protected function utf2win($s){
        return iconv('utf-8','windows-1251',$s);
    }

    protected function save_image($opts){
        if(!file_exists($opts['path'])){
            $f = @file_get_contents($opts['url']);
            if(strpos(@$http_response_header[0],'200')!==false && $this->is_image($opts['url'])){
                //save
                $fp = fopen($opts['path'],'w+');
                fwrite($fp,$f);
                fclose($fp);
                //resize yo
                @Image_Imagick::factory($opts['path'])->resize('150')->save($opts['path_resized']);
                /*$img = new SimpleImage($opts['path']);
                $img->fit_to_width(150)->save($opts['path_resized']);*/
                return $opts['name'];
            }
            else
                return '';
        }
        else
        {
            return $opts['name'];
        }
    }

    protected function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
    protected function is_image($filename) {
        $headers = get_headers( $filename );
        $image_exist = implode(',',$headers);
        if (strpos($image_exist, 'image') !== false)
            return true;
        else
            return false;
    }
}