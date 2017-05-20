<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: Олег
 * Date: 30.03.2015
 * Time: 11:09
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
set_time_limit(700);
class Phelper_Provider6 extends Phelper_Provider{
    private $models;
    private $articles;
    public function __construct(){
        $bench = Profiler::start('providers','6');
        $this->provider_id = 6;
        $this->provider_name = 'provider_'.$this->provider_id.'_'.date('d_m_y').'.csv';
        $this->provider_url = 'http://sex-opt.ru/catalogue/db_export/?type=csv&fields=code:code,discount:discount,barcode:barcode,article:article,image:image,title:title,image1:image1,group_code:group_code,image2:image2,description:description,group_title:group_title,material:material,collection:collection,category_code:category_code,size:size,category_title:category_title,length:length,width:width,fixed_price:fixed_price,msk:msk,color:color,pieces:pieces,weight:weight,brand_code:brand_code,battery:battery,brand_title:brand_title,waterproof:waterproof,start_price:start_price,country:country,price:price,manufacturer:manufacturer&columns_separator=%3B&text_separator=%22&basenames=1';
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
        $updates = array();
        $i = 0;
        $prev_dir = '';
        $current_dir = 0;
        //$brands = $this->additional_load('http://www.inspiritcompany.ru/pricelist/get_file.php?file=IK_Brend.csv');
        foreach ($this->ldata as $item){
            if($i>=0){
                /*
                 *
                    code = 0
                    article = 1
                    title = 2
                    group_code = 3
                    group_title = 4
                    category_code = 5
                    category_title = 6
                    msk = 7
                    start_price = 8
                    price = 9
                    discount = 10
                    image = 11
                    image1 = 12
                    image2 = 13
                    material = 14
                    size = 15
                    length = 16
                    width = 17
                    color = 18
                    weight = 19
                    battery = 20
                    waterproof = 21
                    country = 22
                    manufacturer = 23
                    barcode = 24
                    description = 25
                    collection = 26
                    fixed_price = 27
                    pieces = 28
                    brand_code = 29
                    brand_title = 30

                 * */
                $article = URL::translit($item[1]);
                if($item[11] != '')
                    $this->save_image(array(
                        'url' => 'http://img.sex-opt.ru/images/'.$item[11],
                        'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'.jpg',
                        'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'.jpg',
                        'name' => $article.'.jpg',
                    ));
                if($item[12] != '')
                    $this->save_image(array(
                        'url' => 'http://img.sex-opt.ru/images/'.$item[12],
                        'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'_1.jpg',
                        'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'_1.jpg',
                        'name' => $article.'_1.jpg',
                    ));
                if($item[13] != '')
                    $this->save_image(array(
                        'url' => 'http://img.sex-opt.ru/images/'.$item[13],
                        'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'_2.jpg',
                        'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'_2.jpg',
                        'name' => $article.'_1.jpg',
                    ));
                //print_r($item);
                $sizes = null;
                $amount = ($item[8]?1:0);
                $fields = $this->item_work($item);
                $related = null;
                $color = ($item[18]!=''?$this->color_work($item[18]):null);
                /*print_r($fields);
                print_r($color);*/
                if(in_array($article,$this->articles[0])){
                    if(in_array($item[0],$this->models)){
                        //$price = $this->set_price($article,$price_zak);
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$item[9],
                                'price_trade'   =>  (int)$item[9],
                                'price_retail'  =>  (int)$item[9],
                                'amount' => $amount,
                                'status' => ($amount>0?1:0),
                            ),
                            'where' => array(
                                'id' => $this->articles[1][$article],
                                'pid' => $this->provider_id,
                            ),
                        );
                        /*if($sizes)
                            $updates[] = array(
                                'data' => $sizes,
                                'where' => array(
                                    'product_id' => $this->articles[1][$article],
                                )
                            );*/
                    }
                    else{
                        $cid = $this->dir_work($item[6].':'.$item[4]);
                        //$color = null;
                        $brand = $this->brand_work($item[30]);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$item[2],
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($item[2]),
                                'price' =>  (int)$item[9],
                                'price_trade'   =>  (int)$item[9],
                                'price_retail'  =>  (int)$item[9],
                                'description'   =>  (string)$item[25],
                                'fields'    =>  $fields,
                                'sizes' =>  $sizes,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cid,
                                'article_original'  => (string)$item[1],
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => $item[24],
                                'vid'   => $item[0],
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
                else{
                    if(in_array($item[0],$this->models)){
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$item[9],
                                'price_trade'   =>  (int)$item[9],
                                'price_retail'  =>  (int)$item[9],
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
                        $cid = $this->dir_work($item[6].':'.$item[4]);
                        //$color = null;
                        $brand = $this->brand_work($item[30]);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$item[2],
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($item[2]),
                                'price' =>  (int)$item[9],
                                'price_trade'   =>  (int)$item[9],
                                'price_retail'  =>  (int)$item[9],
                                'description'   =>  (string)$item[25],
                                'fields'    =>  $fields,
                                'sizes' =>  $sizes,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cid,
                                'article_original'  => (string)$item[1],
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => $item[24],
                                'vid'   => $item[0],
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
                //1
            }
            /*if($i>7999)
             {
            //list($is_brand, $prod_id, $cat_id, $articul, $title, $description, $price, $quantity, $brand_id, $sale, $img) = explode(';', $item);
            //print_r($item);
            $code = $item[0];
            $article = $item[1];
            $title = $item[2];
            $group_code = $item[3];
            $group_title = $item[4];
            $category_code = $item[5];
            $category_title = $item[6];
            $tmn = $item[7];
            $msk = $item[8];
            $nsk = $item[9];
            $start_price = $item[10];
            $price = $item[11];
            $discount = $item[12];
            $image = $item[13];
            $image1 = $item[14];
            $image2 = $item[15];
            $material = $item[16];
            $size = $item[17];
            $length = $item[18];
            $width = $item[19];
            $color = $item[20];
            $weight = $item[21];
            $battery = $item[22];
            $waterproof = $item[23];
            $country = $item[24];
            $manufacturer = $item[25];
            $barcode = $item[26];
            $new = $item[27];
            $hit = $item[28];
            $description = $item[29];
            $collection = $item[30];
            $video = $item[31];
            $url = $item[32];
            $rst = $item[33];
            $spb = $item[34];
            $fixed_price = $item[35];
            $pieces = $item[36];
            $brand_code = $item[37];
            $brand_title = $item[38];
            $created = $item[39];
            if(intval($is_brand) == 1)
            {
                if(!empty($prod_id))
                {
                    $current_dir = $this->dir_work($prev_dir.':'.$title);
                }
                else
                {
                    $current_dir = $this->dir_work($title);
                }
                //print_r(array($current_dir,$prev_dir,$title));
                $prev_dir = $title;
            }
            else
            {
                $article = URL::translit($articul);
                $sizes = null;
                $amount = intval($quantity);
                $fields = null;
                $related = null;
                //print_r($item->Изображения);
                $cpk = 0;

                foreach(explode(',',$img) as $pk=>$pic) {
                    if ($cpk == 0)
                        $pre = '';
                    else
                        $pre = '_' . $cpk;
                    $this->save_image(array(
                        'url' => $pic,
                        'path' => DOCROOT . '/images/' . $this->provider_id . '/' . $article . $pre . '.jpg',
                        'path_resized' => DOCROOT . '/images/' . $this->provider_id . '/r_' . $article . $pre . '.jpg',
                        'name' => $article . $pre . '.jpg',
                    ));
                    $cpk++;
                }

                $this->save_image(array(
                    'url' => $img,
                    'path' => DOCROOT.'/images/'.$this->provider_id.'/'.$article.'.jpg',
                    'path_resized' => DOCROOT.'/images/'.$this->provider_id.'/r_'.$article.'.jpg',
                    'name' => $article.'.jpg',
                ));
                if(in_array($article,$this->articles[0])){
                    if(in_array($cat_id,$this->models)){
                        //$price = $this->set_price($article,$price_zak);
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price,
                                'price_retail'  =>  (int)$price,
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
                        $cid = $current_dir;
                        $color = null;
                        $brand = $this->brand_work($brand_id,$brands);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$title,
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($title),
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price,
                                'price_retail'  =>  (int)$price,
                                'description'   =>  (string)$description,
                                'fields'    =>  $fields,
                                'sizes' =>  $sizes,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cid,
                                'article_original'  => (string)$articul,
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => '',
                                'vid'   => $cat_id,
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
                else{
                    if(in_array($cat_id,$this->models)){
                        $update[]= array(
                            'data'=> array(
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price,
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
                        $cid = $current_dir;
                        $color = null;
                        $brand = $this->brand_work($brand_id,$brands);
                        $insert[]= array(
                            'data' => array(
                                'title' =>  (string)$title,
                                'pid'   =>  $this->provider_id,
                                'article' =>    $article,
                                'alias' =>  URL::translit($title),
                                'price' =>  (int)$price,
                                'price_trade'   =>  (int)$price,
                                'price_retail'  =>  (int)$price,
                                'description'   =>  (string)$description,
                                'fields'    =>  $fields,
                                'sizes' =>  $sizes,
                                'related_products'  => $related,
                                'colors'    => $color,
                                'categories' => $cid,
                                'article_original'  => (string)$articul,
                                'amount'    => $amount,
                                'brand' => $brand,
                                'scode' => '',
                                'vid'   => $cat_id,
                                'status' => ($amount>0?1:0),
                            ),
                        );
                    }
                }
            }*/
            //}
          $i++;
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
        foreach(explode(':',$str) as $v){
            $record = DB::select()->from('categories')->where('title','=',UTF8::ucfirst($v))->and_where('parent_id','=',$last_id)->execute();
            /*$record = $m->where('title','=',UTF8::ucfirst($v))->and_where('parent_id','=',$last_id)->find();
            $m = ORM::factory('Categories');
            if($record->loaded())*/
            //print_r();
            if($record->count()>0)
                $last_id = $record[0]['id'];
            else
            {
                /*$m->values(array(
                    'title' => UTF8::ucfirst($v),
                    'alias' => URL::translit($v),
                    'parent_id' => $last_id,
                ))->save();*/
                $id = DB::insert('categories',array('title','alias','parent_id'))->values(array(
                    UTF8::ucfirst($v),
                    URL::translit($v),
                    $last_id
                ))->execute();
                //echo $m->id;
                //$last_id = $m->id;
                $last_id = $id[0];
            }
        }
        return $last_id;
    }

    public function item_work($item)
    {
        /*
         * code = 0
                    article = 1
                    title = 2
                    group_code = 3
                    group_title = 4
                    category_code = 5
                    category_title = 6
                    msk = 7
                    start_price = 8
                    price = 9
                    discount = 10
                    image = 11
                    image1 = 12
                    image2 = 13
                    material = 14
                    size = 15
                    length = 16
                    width = 17
                    color = 18
                    weight = 19
                    battery = 20
                    waterproof = 21
                    country = 22
                    manufacturer = 23
                    barcode = 24
                    description = 25
                    collection = 26
                    fixed_price = 27
                    pieces = 28
                    brand_code = 29
                    brand_title = 30*/
        $fields = array();
        if(trim($item[15]) != '') $fields['size'] = (string)$item[15];
        if(trim($item[26]) != '') $fields['collection'] = (string)$item[26];
        if(trim($item[14]) != '') $fields['material'] = (string)$item[14];
        if(trim($item[19]) != '') $fields['weight'] = (string)$item[19];
        if(trim($item[21]) != '') $fields['waterproof'] = (string)$item[21];
        if($item[16] != '') $fields['length'] = (string)$item[16];
        if($item[17] != '') $fields['width'] = (string)$item[17];
        if($item[20] != '') $fields['power'] = (string)$item[20];
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
        //$brand = $brands[$brand_id]['title'];
        //print_r($brand);
        $record = DB::select()->from('brands')->where('title','=',UTF8::ucfirst($brand))->execute();
        if(count($record)>0)
            $brand_id = $record[0]['id'];
        else{
            $id = DB::insert('brands',array('title','alias'))->values(array(UTF8::ucfirst($brand),URL::translit($brand)))->execute();
            $brand_id = $id[0];
        }
        //print_r($brand_id);
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

    public function str_dir_tree(SimpleXMLElement $obj)
    {
        $sorted = array();
        foreach($obj->category as $k=>$v){
            //print_r($v);
            $sorted[(int)$v->attributes()->id] = array(
                'i'=>$k,
                'id'=>(int)$v->attributes()->id,
                'pid'=>($v->attributes()->parentId?(int)$v->attributes()->parentId:0),
                'path' => (string)$v,
                /*'id'=>$v['id'][0],
                'pid'=>(isset($v['parentId'])?$v['parentId'][0]:0)*/
                /*
                 *
                 * */
            );
        }
        $ns = $sorted;

        foreach($sorted as $k=>$v){
            if($v['pid']!=0){
                //echo $sorted[$v['pid']]['path'];
                $ns[$k]['path'] = $sorted[$v['pid']]['path'].':'.$ns[$k]['path'];
            }

        }

        ksort($ns,SORT_NUMERIC);
        return $ns;
    }

    public function additional_load($url)
    {
        $c = Request::factory($url);
        $c->method = 'GET';
        $ec = explode("\n",$c->execute()->body());
        // beautifier csv to array
        $res = array();
        unset($ec[0]);
        //print_r($ec);

        foreach($ec as $k=>$v){
            $tt = explode(";",$v);
            //print_r($tt);
            if(count($tt)>2){
                $res[$tt[0]] = array(
                    'id' => $tt[0],
                    'title' => $tt[1],
                );
            }

        }

        return $res;
    }
}