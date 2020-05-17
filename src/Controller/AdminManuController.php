<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use App\Validator;
use DB;


class AdminManuController
{
    protected $records_per_page = 20;

    protected $itemTitle;

    protected $tableName;

    protected $fieldList;

    protected $validator;


    public function index (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'admin/item_list.html.twig',[
            'itemTitle' => $this->itemTitle,
            'itemKeyList' => $this->fieldList,
            'currentPage' => 1
        ]);
    }


    public function showAll (Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);

        $get = $this->parseGetRequest($request);

        $startResv = ($get['currentPage'] - 1) * $this->records_per_page;

        $resvList = DB::query(
            "SELECT %l FROM %l
            ORDER BY %l %l
            LIMIT %i, %i",
            implode(",",$this->fieldList),
            $this->tableName,
            $get['sortBy'], $get['order'], $startResv, $this->records_per_page
        );

        return $view->render($response, 'admin/cards/item_card.html.twig', [
            'itemList' => $resvList,
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

        if(empty($errorList)){
            DB::insert($this->tableName,$jsonData);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }

    public function delete (Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        DB::delete($this->tableName,'id=%i', $id);
        $response->getBody()->write(json_encode(DB::affectedRows()));

        return $response;
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

        if(empty($errorList)){
            DB::update($this->tableName,$data,'id=%s',$id);
        }

        $response->getBody()->write(json_encode([
            'errorList' => $errorList
        ]));

        return $response;
    }


    private function parseGetRequest($request)
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

    private function getCurrentPage($get, $totalPage)
    {
        if(isset($get['page'])){
            $page = $get['page'] > $totalPage ? $totalPage : $get['page'];
            $page = $get['page'] < 1 ? 1 : $get['page'];
            return $page;
        }     
        return 1;
    }

    private function getSortBy($get)
    {
        if(isset($get['sortBy'])){
            $fieldList = DB::queryFirstColumn("DESCRIBE " . $this->tableName);
            return in_array($get['sortBy'], $fieldList) 
                    ? $get['sortBy'] : 'id';
        }
        return 'id';
        
    }

    private function getOrder($get)
    {
        if(isset($get['order'])){
            return strtoupper($get['order']) == 'DESC' ? 'DESC' : 'ASC';
        }     
        return 'ASC';
    }  
}