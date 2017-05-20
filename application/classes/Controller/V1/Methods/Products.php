<?php defined('SYSPATH') or die('No direct script access.');

class Controller_V1_Methods_Products extends Controller_V1_Methods_Method{
    private $bench;
    private $result;
    private $available_params = array(
        'provider' => array(1,2,3,4,5,6,7,8),
        'status' => array(0,1),
        'category' => '/(.*?)/is',
        'page' => '/\d+/is',
        'limit' => '/\d+/is'
    );
    public function before()
    {
        $params = new Validation($_GET);
        $params->rule('provider', 'in_array', array(':value',$this->available_params['provider']));
        $params->rule('status', 'in_array', array(':value',$this->available_params['status']));
        //echo $this->request->param('category');
        //print_r($this->request);
        //print_r();
        if($params->check())
        {
            //print_r($this->request->param());
            $this->bench = Profiler::start('products',$this->request->action());
            if($this->request->query('category') && $this->request->query('category')!=0){
                $products = DB::select()->from('products')->where('status','=',$this->request->query('status'))->where('categories','=',$this->request->query('category'))->order_by('price','DESC')->order_by('article','ASC')->limit($this->request->query('limit'))->offset($this->request->query('page')*$this->request->query('limit'));
                //echo $this->request->param('category');
            }

                else
                    $products = DB::select()->from('products')->where('status','=',$this->request->query('status'))->order_by('price','DESC')->order_by('article','ASC')->limit($this->request->query('limit'))->offset($this->request->query('page')*$this->request->query('limit'));
            //print_r($products);
            //$products = ORM::factory('Products')->find_all();
            //$products = DB::select()->from('products')->where('pid','=',4)->execute();
            //print_r($db);
            $products = $products->execute()->as_array();
            //print_r($products);
            $this->result = array(
                'status'    =>  200,
                'method'    =>  'products',
                'query' => Database::instance()->last_query,
                'data'  =>  array(),
            );
            $i = 0;
            $sorted = array();
            foreach($products as $product){
                $sorted[$product['article']][]=  array(
                    'title' =>  $product['title'],
                    'description'   =>  $product['description'],
                    'status'    =>  (int)$product['status'],
                    'article'   =>  $product['article'],
                    'price' =>  (int)$product['price_retail'],
                    'alias' =>  $product['alias'],
                    'pid' => $product['pid'],
                    'images' => $this->get_images($product['article'],$product['pid']),
                );
                /*if($i<10){
                    $this->result['data'][$product['id']] = array(
                        'title' =>  $product['title'],
                        'description'   =>  $product['description'],
                        'status'    =>  (int)$product['status'],
                        'article'   =>  $product['article'],
                        'price' =>  (int)$product['price_retail'],
                        'alias' =>  $product['alias'],
                    );
                }*/

                //print_r($product);
            }
            //print_r($sorted);
            //$final = array();
            foreach($sorted as $key=>$ar){
                //if(count($ar)>1){
                    if($i<500){
                        $price = array();
                        //array_multisort($ar['price'],SORT_ASC,SORT_NUMERIC);
                        foreach ($ar as $k => $row) {
                            $price[$k]  = $row['price'];
                            //$edition[$key] = $row['edition'];
                        }

                        asort($price);
                        $res = array();
                        foreach($price as $k=>$row){
                            //print $k.'<br/>';
                            $res[] = $ar[$k];
                        }
                        //print_r($price);
                        //print_r($res);
                        //print_r($price);
// Sort the data with volume descending, edition ascending
// Add $data as the last parameter, to sort by the common key
                        //array_multisort($price,$ar);
                        $this->result['data'][$key] = $res;
                        $i++;
                    }
                //}
            }
            $this->result['rows'] = $i;
        }
        else
        {
            $this->result = array(
                'status'    =>  406,
                'method'    =>  'products',
                'error'  =>  'Invalid format is specified in the request.',
            );
        }
    }

    public function after()
    {

    }
    public function action_json()
    {

        /*$validation = Validation::factory($_GET)
            ->rule('');*/
        $this->response->headers('Content-Type', 'application/json; charset=utf-8');
        Profiler::stop($this->bench);
        $this->result['stats'] = Profiler::total($this->bench);
        echo json_encode($this->result);
        //Profiler::stop($bench);
        //echo View::factory('profiler/stats');
        //$this->response->body('hello!');
    }

    public function action_xml(){
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><catalog date=\"".date('d.m.Y')."\"></catalog>");

        // function call to convert array to xml
        Profiler::stop($this->bench);
        $stats = Profiler::total($this->bench);
        $this->result['stats'] = array('time'=>$stats[0],'memory'=>$stats[1]);
        $this->array_to_xml($this->result,$xml,'Product');
        $this->response->headers('Content-Type', 'application/xml; charset=utf-8');

        //saving generated xml file
        echo $xml->asXML();
    }

    public function get_images($article,$pid)
    {
        $images = array();
        $pre = array('','_1','_2','_3','_4','_5');
        foreach($pre as $v){
            $p = DOCROOT.'/images/'.$pid.'/r_'.$article.$v.'.jpg';
            if(file_exists($p)){
                $images[]='/images/'.$pid.'/r_'.$article.$v.'.jpg';
            }
        }
        return implode(',',$images);
    }
}