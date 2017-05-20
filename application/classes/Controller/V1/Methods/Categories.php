<?php defined('SYSPATH') or die('No direct script access.');

class Controller_V1_Methods_Categories extends Controller_V1_Methods_Method{
    private $bench;
    private $result;
    private $available_params = array(
        ''
    );
    public function before()
    {
        $this->bench = Profiler::start('categories',$this->request->action());
        $cts = DB::select()->from('categories');
        //print_r($products);
        //$products = ORM::factory('Products')->find_all();
        //$products = DB::select()->from('products')->where('pid','=',4)->execute();
        //print_r($db);
        $cts = $cts->execute()->as_array();
        $this->result = array(
            'status'    =>  200,
            'method'    =>  'categories',
            'data'  =>  array(),
        );
        $i = 0;
        $sorted = array();
        foreach($cts as $category){
            $this->result['data'][$category['id']]=  array(
                'title' =>  $category['title'],
                'alias' =>  $category['alias'],
                'pid' => $category['parent_id'],
            );
            $i++;
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
        //$final = array();
        /*foreach($sorted as $key=>$ar){
            if(count($ar)>1){
                if($i<10){
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
            }
        }*/
        $this->result['rows'] = $i;
    }

    public function after()
    {
        Profiler::stop($this->bench);
    }
    public function action_json()
    {

        /*$validation = Validation::factory($_GET)
            ->rule('');*/
        $this->response->headers('Content-Type', 'application/json; charset=utf-8');
        echo json_encode($this->result);
        //Profiler::stop($bench);
        //echo View::factory('profiler/stats');
        //$this->response->body('hello!');
    }

    public function action_xml(){
        $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><catalog date=\"".date('d.m.Y')."\"></catalog>");

        // function call to convert array to xml
        $title = "Category";
        $this->array_to_xml($this->result,$xml,$title);
        $this->response->headers('Content-Type', 'application/xml; charset=utf-8');
        //saving generated xml file
        echo $xml->asXML();
    }
}