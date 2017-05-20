<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: Олег
 * Date: 30.03.2015
 * Time: 11:09
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class Phelper_Provider2 extends Phelper_Provider{
    private $models;
    private $articles;
    public function __construct(){
        $bench = Profiler::start('providers','2');
        $this->provider_id = 2;
        $this->provider_name = 'provider_'.$this->provider_id.'_'.date('d_m_y').'.csv';
        $this->provider_url = 'http://www.kazanova.su/files/csv/msk-new.csv';
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
        $this->ldata = file($this->saved_object);
        unset($this->ldata[0]);
        $insert = array();
        $update = array();
        $updates = array();
        $i = 0;
        foreach ($this->ldata as $row){
            if(trim($row)!='')
            {
                //if($i<10)
                //{
                    $item = explode(';',iconv('CP1251','UTF8',$row));
                    $article = URL::translit($item[0]);
                    $amount = $item[6];
                    $color = $this->color_work($item[15]);
                    $fields = $this->item_work($item);
                    $sizes = null;
                    $related = null;
                    //print_r(array($article,$amount,$color,$item[15]));
                    //print_r($item);
                    if($item[5] != '')
                    {
                        $this->save_image(array(
                            'url' => $item[5],
                            'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'.jpg',
                            'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'.jpg',
                            'name' => $article.'.jpg',
                        ));
                    }
                    if(in_array($article,$this->articles[0])){
                        if(in_array($item[1],$this->models)){
                            //$price = $this->set_price($article,$price_zak);
                            $update[]= array(
                                'data'=> array(
                                    'price' =>  (int)$item[20],
                                    'price_trade'   =>  (int)$item[4],
                                    'price_retail'  =>  (int)$item[3],
                                    'amount' => $amount,
                                    'status' => ($amount>0?1:0),
                                ),
                                'where' => array(
                                    'id' => $this->articles[1][$article],
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
                            $cat_id = $this->dir_work($item[7]);
                            $brand = $this->brand_work($item[12],$item[13]);
                            $insert[]= array(
                                'data' => array(
                                    'title' =>  (string)$item[2],
                                    'pid'   =>  $this->provider_id,
                                    'article' =>    $article,
                                    'alias' =>  URL::translit($item[2]),
                                    'price' =>  (int)$item[20],
                                    'price_trade'   =>  (int)$item[4],
                                    'price_retail'  =>  (int)$item[3],
                                    'description'   =>  (string)$item[8],
                                    'fields'    =>  $fields,
                                    'related_products'  => $related,
                                    'colors'    => $color,
                                    'categories' => $cat_id,
                                    'article_original'  => (string)$item[0],
                                    'amount'    => $amount,
                                    'brand' => $brand,
                                    'scode' => (string)$item[11],
                                    'vid'   => $item[1],
                                    'status' => ($amount>0?1:0),
                                ),
                            );
                        }
                    }
                    else{
                        if(in_array($item[1],$this->models)){
                            $update[]= array(
                                'data'=> array(
                                    'price' =>  (int)$item[20],
                                    'price_trade'   =>  (int)$item[4],
                                    'price_retail'  =>  (int)$item[3],
                                    'amount' => $amount,
                                    'status' => ($amount>0?1:0),
                                ),
                                'where' => array(
                                    'id' => $this->articles[1][$article],
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
                            $cat_id = $this->dir_work($item[7]);
                            $brand = $this->brand_work($item[12],$item[13]);
                            $insert[]= array(
                                'data' => array(
                                    'title' =>  (string)$item[2],
                                    'pid'   =>  $this->provider_id,
                                    'article' =>    $article,
                                    'alias' =>  URL::translit($item[2]),
                                    'price' =>  (int)$item[20],
                                    'price_trade'   =>  (int)$item[4],
                                    'price_retail'  =>  (int)$item[3],
                                    'description'   =>  (string)$item[8],
                                    'fields'    =>  $fields,
                                    'related_products'  => $related,
                                    'colors'    => $color,
                                    'categories' => $cat_id,
                                    'article_original'  => (string)$item[0],
                                    'amount'    => $amount,
                                    'brand' => $brand,
                                    'scode' => (string)$item[11],
                                    'vid'   => $item[1],
                                    'status' => ($amount>0?1:0),
                                ),
                            );
                        }
                    }
                //}

                $i++;
            }
        }
        //print_r($insert);
        //print_r($update);
        //print_r($updates);
        $this->set_inactive();
        $this->to_insert($insert);
        $this->to_update($update);
        /*$this->to_updates($updates);*/
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
        echo "Added: ".$i."<br/>";
    }
    public function to_update($a){
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
        if($item[16] != '') $fields['length'] = (string)$item[16];
        if($item[17] != '') $fields['diameter'] = (string)$item[17];
        if($item[18] != '') $fields['power'] = (string)$item[18];
        if($item[19] != '') $fields['pkg'] = (string)$item[19];
        return json_encode($fields);
    }

    public function color_work($color)
    {
        $record = DB::select()->from('colors')->where('title','=',UTF8::ucfirst($color))->execute();
        /*$m = ORM::factory('Colors');
        $record = $m->where('title','=',UTF8::ucfirst($color))->find();*/
        //if($record->loaded())
        if(count($record)>0)
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
        $record = DB::select()->from('brands')->where('title','=',UTF8::ucfirst($brand))->execute();
        if(count($record)>0)
            $brand_id = $record[0]['id'];
        else{
            $id = DB::insert('brands',array('title','country','alias'))->values(array(UTF8::ucfirst($brand),UTF8::ucfirst($country),URL::translit($brand)))->execute();
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
}