<?php
/**
 * Created by PhpStorm.
 * User: ����
 * Date: 30.03.2015
 * Time: 9:47
 */

class Phelper_Provider3 extends Phelper_Provider{
    private $models;
    private $articles;
    public function __construct(){
        $bench = Profiler::start('providers','3');
        $this->provider_id = 3;
        $this->provider_name = 'provider_'.$this->provider_id.'_'.date('d_m_y').'.xml';
        $this->provider_url = 'http://astkol.com/catalog/xml/?u=2143&p=d42d69e7f0bd978777cca54687d3f1b7';
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
        $this->ldata = simplexml_load_file($this->saved_object);
        $insert = array();
        $update = array();
        $updates = array();
        $i = 0;
        //print_r($this->ldata);
        foreach ($this->ldata->item as $item){
            //echo $item->name.'<br/>';

            //    foreach($group->������������ as $item){
                //if($i<10){
                $article = URL::translit($item->art);

                //if($item['������'] == '')
                    $sizes = null;
                //else
                  //  $sizes = $this->sizes_work($item);
                $amount = (int)$item->qty;
                $fields = $this->item_work($item);
                //if(isset($item->�������������������������������))
                //    $related = $this->related_work($item->�������������������������������);
                //else
                    $related = null;
                //print_r($item->�����������);
                /*$cpk = 0;
                foreach($item->�����������->����������� as $pk=>$pic){
                    if($cpk == 0)
                        $pre = '';
                    else
                        $pre = '_'.$cpk;*/
                    $this->save_image(array(
                        'url' => $item->img,
                        'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'.jpg',
                        'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'.jpg',
                        'name' => $article.'.jpg',
                    ));
                    //$cpk++;
                //}
                //print_r($this->articles);
                if(in_array($article,$this->articles[0])){
                    if(in_array((int)$item->id,$this->models)){
                        //$price = $this->set_price($article,$price_zak);
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$item->price_base,
                                'price_trade'   =>  (int)$item->price,
                                'price_retail'  =>  (int)((int)$item->price_base*2),
                                'amount' => $amount,
                                'status' => ($amount>0?1:0),
                            ),
                            'where' => array(
                                'id' => @$this->articles[1][$article],
                                'pid' => $this->provider_id,
                            ),
                        );
                        if($sizes)
                            $updates[] = array(
                                'data' => $sizes,
                                'where' => array(
                                    'product_id' => $this->articles[1][$article],
                                )
                            );
                    }
                    else{
                        $cat_id = $this->dir_work($item->gruppa);
                        $color = null;
                        $brand = $this->brand_work($item->producer,$item->country);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$item->name,
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($item->name),
                                'price' =>  (int)$item->price_base,
                                'price_trade'   =>  (int)$item->price,
                                'price_retail'  =>  (int)((int)$item->price_base*2),
                                'description'   =>  null,
                                'fields'    =>  $fields,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cat_id,
                                'article_original'  => (string)$item->art,
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => (string)$item->cipher,
                                'vid'   => (int)$item->id,
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
                else{
                    if(in_array((int)$item->id,$this->models)){
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$item->price_base,
                                'price_trade'   =>  (int)$item->price,
                                'price_retail'  =>  (int)((int)$item->price_base*2),
                                'amount' => $amount,
                                'status' => ($amount>0?1:0),
                            ),
                            'where' => array(
                                'id' => @$this->articles[1][$article],
                                'pid' => $this->provider_id,
                            ),
                        );
                        if($sizes)
                            $updates[] = array(
                                'data' => $sizes,
                                'where' => array(
                                    'product_id' => $this->articles[1][$article],
                                )
                            );
                    }
                    else{
                        $cat_id = $this->dir_work($item->gruppa);
                        $color = null;
                        $brand = $this->brand_work($item->producer,$item->country);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$item->name,
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($item->name),
                                'price' =>  (int)$item->price_base,
                                'price_trade'   =>  (int)$item->price,
                                'price_retail'  =>  (int)((int)$item->price_base*2),
                                'description'   =>  null,
                                'fields'    =>  $fields,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cat_id,
                                'article_original'  => (string)$item->art,
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => (string)$item->cipher,
                                'vid'   => (int)$item->id,
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
                // }
                $i++;
            }
        //}
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
            if($sizes)
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
            }
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
        $fields['matherial'] = (string)$item->matherial;
        $fields['vibration'] = ($item->vibration==''?0:1);
        $fields['outlet'] = $item->outlet;
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

    public function brand_work($brand,$country)
    {
        //print_r(array($brand,$country));
        $record = DB::select()->from('brands')->where('title','=',UTF8::ucfirst($brand))->execute();
        if(count($record)>0)
            $brand_id = $record[0]['id'];
        else{
            $id = DB::insert('brands',array('title','alias','country'))->values(
                array(
                    UTF8::ucfirst($brand),
                    URL::translit($brand),
                    UTF8::ucfirst($country)
                ))->execute();
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
        foreach($i->������� as $t){
            $a[] = array(
                'title' => (string)$t['������'],
                'amount'  => $this->set_amount($t['����������']),
                'article'   => URL::translit($t['�������']),
                'price' =>  (int)$t['����'],
                'price_trade'   =>  (int)$t['�������������'],
                'price_retail'  =>  ceil($t['����']*2.5),
                'scode' => (string)$t['���������'],
                'vid'   => (int)$t['id'],
            );
        }
        return $a;
    }

    public function set_amount($str)
    {
        $ostatok = 0;
        switch($str){
            case '�������� ��� ������':
                $ostatok = 50;
                break;
            case '������������ ����������':
                $ostatok = 10;
                break;
            case '������� ������ 5':
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
        foreach($r->������������������������� as $v)
        {
            $a[] = (int)$v['id'];
        }
        return implode(',',$a);
    }
}