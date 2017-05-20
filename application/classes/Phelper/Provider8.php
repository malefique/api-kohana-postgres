<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: Олег
 * Date: 30.03.2015
 * Time: 11:09
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class Phelper_Provider8 extends Phelper_Provider{
    private $models;
    private $articles;
    public function __construct(){
        $bench = Profiler::start('providers','8');
        $this->provider_id = 8;
        $this->provider_name = 'provider_'.$this->provider_id.'_'.$this->datechanger('d_m_y').'.csv';
        $this->provider_url = 'http://djero.ru/in/Opt_DjagaDjaga_'.$this->datechanger('d.m.Y').'.csv';
        $this->use_cookie = false;
        $this->articles = $this->load_existed_articles();
        $this->models = $this->load_existed_models();

        $this->get_data(false);

        $this->load_object();
        $this->log();
        Profiler::stop($bench);
        //echo View::factory('profiler/stats');
    }

    public function load_object(){
        if (($handle = fopen($this->saved_object, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 0, ';','"')) !== FALSE) {
                $this->ldata[]=$row;
            }
            fclose($handle);
        }
        unset($this->ldata[0]);
        $insert = array();
        $update = array();
        $i = 0;
        foreach($this->ldata as $k=>$v){
            //$v = str_replace('"','',$v);
            //print_r($v);
            list($vid,$link,$article_or,$name,$description,$price_trade,$brand,$material,$color,$size,$pic,$category) = $v;
            $amount = 50;
            //list($vid,$scode,$article_or,$name,$price_trade,$amount,$category,$brand,$img,$price,$description) = explode(';',$v);
                //if($i<10){
                $article = URL::translit($article_or);

                //if($item['Размер'] == '')
                    $sizes = null;
                //else
                //    $sizes = $this->sizes_work($item);
                $amount = (int)$amount;
                $fields = null;//$this->item_work($item);
                //if(isset($item->СписокСопутствующаяНоменклатура))
                //    $related = $this->related_work($item->СписокСопутствующаяНоменклатура);
                //else
                    $related = null;
                //print_r($item->Изображения);
                /*$cpk = 0;
                foreach($item->Изображения->Изображение as $pk=>$pic){
                    if($cpk == 0)
                        $pre = '';
                    else
                        $pre = '_'.$cpk;*/
                    /*$this->save_image(array(
                        'url' => $img,
                        'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'.jpg',
                        'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'.jpg',
                        'name' => $article.'.jpg',
                    ));*/
                    //$cpk++;
                //}
                //print_r($this->articles);
                if(in_array($article,$this->articles[0])){
                    if(in_array((int)$vid,$this->models)){
                        //$price = $this->set_price($article,$price_zak);
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price_trade,
                                'price_retail'  =>  (int)$price,
                                'amount' => $amount,
                                'status' => ($amount>0?1:0),
                            ),
                            'where' => array(
                                'id' => $this->articles[1][$article],
                                'pid' => $this->provider_id,
                            ),
                        );

                    }
                    else{
                        $cat_id = $this->dir_work($category);
                        $color = null;//$this->color_work($item['Цвет']);
                        $brand = $this->brand_work($brand);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$name,
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($name),
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price_trade,
                                'price_retail'  =>  (int)$price,
                                'description'   =>  (string)$description,
                                'fields'    =>  $fields,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cat_id,
                                'article_original'  => (string)$article_or,
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => (string)$scode,
                                'vid'   => (int)$vid,
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
                else{
                    if(in_array((int)$vid,$this->models)){
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price_trade,
                                'price_retail'  =>  (int)$price,
                                'amount' => $amount,
                                'status' => ($amount>0?1:0),
                            ),
                            'where' => array(
                                'id' => $this->articles[1][$article],
                                'pid' => $this->provider_id,
                            ),
                        );
                    }
                    else{
                        $cat_id = $this->dir_work($category);
                        $color = null;//$this->color_work($item['Цвет']);
                        $brand = $this->brand_work($brand);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$name,
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($name),
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price_trade,
                                'price_retail'  =>  (int)$price,
                                'description'   =>  (string)$description,
                                'fields'    =>  $fields,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cat_id,
                                'article_original'  => (string)$article_or,
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => (string)$scode,
                                'vid'   => (int)$vid,
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
               // }
                $i++;
            //}


        }
        //print_r($insert);
        //print_r($update);
        //print_r($updates);
        $this->set_inactive();
        $this->to_insert($insert);
        $this->to_update($update);
        //$this->to_updates($updates);
    }

    public function set_inactive(){
        DB::update('products_'.$this->provider_id)->set(array('status'=>0))->execute();
    }

    public function to_insert($a){
        $i = 0;

        //
        foreach($a as $k=>$v){
            /*$m = ORM::factory('Products');
            $m->set_table($this->provider_id);*/
            $sizes = @$v['data']['sizes'];
            unset($v['data']['sizes']);
            $product = DB::insert('products_'.$this->provider_id,array_keys($v['data']))->values(array_values($v['data']))->execute();
            //$m->values($v['data'])->save();
            $pid = $product[0];
            /*if($sizes)
            {
                $sa = array();
                foreach($sizes as $t=>$tv) {
                    //$s = ORM::factory('Sizes');
                    $sizes[$t]['product_id'] = $pid;
                    $sz = DB::insert('sizes',array_keys($sizes[$t]))->values(array_values($sizes[$t]))->execute();
                    //$s->values($sizes[$t])->save();
                    $sa[]=$sz[0];
                }
                DB::update('products_'.$this->provider_id)->set(array('sizes'=>implode(',',$sa)))->execute();
                //$m->where('id','=',$pid)->set('sizes',implode(',',$sa))->save();
            }*/
            $i++;
        }
        //echo "Added: ".$i."<br/>";
        /*foreach($a as $k=>$v){
            $values = array();
            $keys = array();
            foreach($v['data'] as $key=>$value){
                $keys[]='`'.$key.'`';
                $values[]='?';
            }
            $q = $this->db->prepare("INSERT INTO `models` (".implode(',',$keys).") VALUES (".implode(",",$values).");");
            //echo "INSERT INTO `models` (".implode(',',$keys).") VALUES (".implode(",",$values).");\n";
            //print_r(array_values($v['data']));

            $q->execute(array_values($v['data']));

            $i++;
            $lid = $this->db->lastInsertId();
            //echo '<br />'.$lid;
            $q = $this->db->prepare("INSERT INTO `model_part_xref` (`part_id`,`model_id`) VALUES (?,?);");
            $q->execute(array($v['data']['partid'],$lid));
            if($v['data']['zamid']){
                $q = $this->db->prepare("SELECT `zam` FROM `models` WHERE `id` = ?");
                $q->execute(array($v['data']['zamid']));
                $zm = $q->fetchAll();
                //$q = $this->db->prepare("UPDATE `models` SET `zam` = ".($zm[0]['zam']?','.$lid:$lid)." WHERE `id`=");
                // $
            }
        }*/
        echo "Added: ".$i."<br/>";
    }
    public function to_update($a){
        /*$i = 0;
        foreach($a as $k=>$v){
            $values = array();
            $keys = array();
            $where = array();
            foreach($v['data'] as $key=>$value){
                $keys[]='`'.$key.'` = ?';
            }
            foreach($v['where'] as $key=>$value){
                $where[]='`'.$key.'` = ?';
            }
            $q = $this->db->prepare("UPDATE `models` SET ".implode(',',$keys)." WHERE ".implode(' AND ',$where)."");
            //echo "UPDATE `models` SET ".implode(',',$keys)." WHERE ".implode(' AND ',$where)."";
            $q->execute(array_merge(array_values($v['data']),array_values($v['where'])));
            //print_r(array_merge(array_values($v['data']),array_values($v['where'])));
            $i++;
        }*/
        $i = 0;

        foreach($a as $k=>$v){
            DB::update('products')->set($v['data'])->
            where('pid','=',$this->provider_id)->
            and_where('id','=',$v['where']['id'])->execute();
            /*$m = ORM::factory('Products');
            $m->
                where('pid','=',$this->provider_id)->
                and_where('id','=',$v['where']['id'])->
                find()->
                values($v['data'])->
                save();*/
            $i++;
        }
        echo "Updated: ".$i."<br/>";
    }

    public function to_updates($a)
    {
        $i = 0;

        foreach($a as $k=>$v){
            foreach($v['data'] as $t=>$vs) {
                DB::update('sizes')->set($vs)->where('product_id', '=', $v['where']['product_id'])->
                and_where('vid', '=', $vs['vid'])->execute();
                /*$m = ORM::factory('Sizes');
                $m->
                where('product_id', '=', $v['where']['product_id'])->
                and_where('vid', '=', $vs['vid'])->
                find()->
                values($vs)->
                save();*/
                $i++;
            }
        }
        echo "Sizes updated: ".$i."<br/>";
    }

    public function dir_work($str)
    {
        $last_id = 0;
        $record = DB::select()->from('categories')->where('title','=',UTF8::ucfirst($str))->and_where('parent_id','=',$last_id)->execute();
        /*$record = $m->where('title','=',UTF8::ucfirst($v))->and_where('parent_id','=',$last_id)->find();
        $m = ORM::factory('Categories');
        if($record->loaded())*/
        //print_r($record);
        if(count($record)>0)
            $last_id = $record[0]['id'];
        else
        {
            /*$m->values(array(
                'title' => UTF8::ucfirst($v),
                'alias' => URL::translit($v),
                'parent_id' => $last_id,
            ))->save();*/
            $id = DB::insert('categories',array('title','alias','parent_id'))->values(array(
                UTF8::ucfirst($str),
                URL::translit($str),
                $last_id
            ))->execute();
            //echo $m->id;
            //$last_id = $m->id;
            $last_id = $id[0];
        }

        return $last_id;
    }

    public function item_work($item)
    {
        $fields = array();
        $fields['length'] = (string)$item['Длина'];
        $fields['diameter'] = (string)$item['Диаметр'];
        $fields['weight'] = (string)$item['ВесИзделия'];
        return json_encode($fields);
    }

    public function color_work($color)
    {
        $record = DB::select()->from('colors')->where('title','=',UTF8::ucfirst($color))->execute();
        /*$m = ORM::factory('Colors');
        $record = $m->where('title','=',UTF8::ucfirst($color))->find();*/
        //if($record->loaded())
        if($record)
            //$color_id = $record->id;
            $color_id = $record[0]['id'];
        else
        {
            /*$m->values(array(
                'title' => UTF8::ucfirst($color),
            ))->save();
            //echo $m->id;
            $color_id = $m->id;*/
            $id = DB::insert('colors',array('title'))->values(array(UTF8::ucfirst($color)))->execute();
            $color_id = $id[0];
        }
        return $color_id;
    }

    public function brand_work($brand)
    {
        $record = DB::select()->from('brands')->where('title','=',UTF8::ucfirst($brand))->execute();
        if(count($record)>0)
            $brand_id = $record[0]['id'];
        else{
            $id = DB::insert('brands',array('title','alias'))->values(array(UTF8::ucfirst($brand),URL::translit($brand)))->execute();
            $brand_id = $id[0];
        }
        /*$m = ORM::factory('Brands');
        $record = $m->where('title','=',UTF8::ucfirst($brand))->find();
        if($record->loaded())
            $brand_id = $record->id;
        else
        {
            $m->values(array(
                'title' => UTF8::ucfirst($brand),
                'alias' => URL::translit($brand),
            ))->save();
            //echo $m->id;
            $brand_id = $m->id;
        }*/
        return $brand_id;
    }

    public function sizes_work($i)
    {
        $a = array();
        foreach($i->Наличие as $t){
            $a[] = array(
                'title' => (string)$t['Размер'],
                'amount'  => $this->set_amount($t['Количество']),
                'article'   => URL::translit($t['Артикул']),
                'price' =>  (int)$t['Цена'],
                'price_trade'   =>  (int)$t['ЦенаСоСкидкой'],
                'price_retail'  =>  ceil($t['Цена']*2.5),
                'scode' => (string)$t['Штрихкоды'],
                'vid'   => (int)$t['id'],
            );
        }
        return $a;
    }

    public function set_amount($str)
    {
        $ostatok = 0;
        switch($str){
            case 'Доступен для заказа':
                $ostatok = 50;
                break;
            case 'Ограниченное количество':
                $ostatok = 10;
                break;
            case 'Остаток меньше 5':
                $ostatok = 2;
                break;
            default:
                $ostatok = 0;
                break;
        }
        return $ostatok;
    }

    public function related_work($r)
    {
        $a = array();
        foreach($r->СопутствующаяНоменклатура as $v)
        {
            $a[] = (int)$v['id'];
        }
        return implode(',',$a);
    }

    public function datechanger($format)
    {
        $current = date('Y-m-d');
        return date($format, strtotime($current)-86400);
    }
}