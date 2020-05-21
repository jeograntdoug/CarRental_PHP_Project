<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use App\Validator;
use DB;


class AdminController
{
    protected $records_per_page = 20;

    protected $itemTitle;

    protected $tableName;

    protected $fieldList = [];

    protected $fieldListInHeader = [];

    protected $validator;

    protected $itemTemplate = 'item_card.html.twig';


    public function home(Request $request, Response $response){
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/index.html.twig');
    }

    public function index (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'admin/item_list.html.twig',[
            'itemTitle' => $this->itemTitle,
            'itemKeyListInHeader' => $this->fieldListInHeader,
            'itemKeyList' => $this->fieldList,
            'currentPage' => 1
        ]);
    }


    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $get = $this->parseGetRequest($request);

        $startItem = ($get['currentPage'] - 1) * $this->records_per_page;

        $itemList = DB::query(
            "SELECT %l FROM %l
            ORDER BY %l %l
            LIMIT %i, %i",
            implode(",",$this->fieldList),
            $this->tableName,
            $get['sortBy'], $get['order'], 
            $startItem, $this->records_per_page
        );

        return $view->render($response, 'admin/cards/' . $this->itemTemplate, [
            'itemTitle' => $this->itemTitle,
            'itemList' => $itemList,
            'currentPage' => $get['currentPage'],
            'totalPage' => $get['totalPage']
        ]);
    }

    public function create (Request $request, Response $response, array $args)
    {
        $jsonData = json_decode($request->getBody(), true);

        $errorList = [];
        if($this->validator != null){
            $errorList = call_user_func($this->validator,$jsonData);
        }

        if(empty($errorList))
        {
            DB::insert($this->tableName,$jsonData);
            $response->getBody()->write(json_encode(DB::insertId()));
            return $response;
        } 
        else 
        {
            $response->getBody()->write(json_encode([
                'errorList' => $errorList
            ]));
            return $response->withStatus(400);
        }
    }

    public function delete (Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        DB::delete($this->tableName,'id=%i', $id);
        $response->getBody()->write(json_encode(DB::affectedRows()));

        return $response;
    }

    public function show (Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $item = DB::queryFirstRow(
            "SELECT %l FROM %l
            WHERE id = %i",
            implode(",",$this->fieldList),
            $this->tableName, $id
        );

        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/cards/item_detail.html.twig',[
            'item' => $item
        ]);;    
    }

    public function edit (Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $jsonData = json_decode($request->getBody(), true);

        $data = [
            $this->fieldList[$jsonData['fieldIndex']] => $jsonData['value']
        ];

        $errorList = [];
        if($this->validator != null){
            $errorList = call_user_func($this->validator,$data, false);
        }

        if(empty($errorList))
        {
            DB::update($this->tableName,$data,'id=%s',$id);
            $response->getBody()->write(json_encode(DB::affectedRows()));
            return $response;
        } 
        else 
        {
            $response->getBody()->write(json_encode([
                'errorList' => $errorList
            ]));
            return $response->withStatus(400);
        }
    }


    protected function parseGetRequest($request)
    {
        $get = $request->getQueryParams();

        $totalResv = DB::queryFirstField(
            "SELECT COUNT(*) FROM %l", $this->tableName
        );
        $totalPage = ceil( $totalResv / $this->records_per_page) ;

        return [
            'currentPage' => $this->getCurrentPage($get, $totalPage),
            'sortBy' => $this->getSortBy($get),
            'order' => $this->getOrder($get),
            'totalPage' => $totalPage
        ];

    }

    protected function getCurrentPage($get, $totalPage)
    {
        if(isset($get['page'])){
            $page = $get['page'] > $totalPage ? $totalPage : $get['page'];
            $page = $get['page'] < 1 ? 1 : $get['page'];
            return $page;
        }     
        return 1;
    }

    protected function getSortBy($get)
    {
        if(isset($get['sortBy'])){
            if(in_array($get['sortBy'], $this->fieldList)){
                return $get['sortBy'];
            } else if(($index = array_search(strtoupper($get['sortBy']), $this->fieldListInHeader)) !== false){
                return $this->fieldList[$index];
            }
        }
        return 'id';
        
    }

    protected function getOrder($get)
    {
        if(isset($get['order'])){
            return strtoupper($get['order']) == 'DESC' ? 'DESC' : 'ASC';
        }     
        return 'ASC';
    }  
}