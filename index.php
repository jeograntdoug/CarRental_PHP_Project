<?php

require "setup.php";

//s?#-K;#"&6S0Msa:

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Views\Twig;

const PRODUCTS_PER_PAGE = 5;

$app->get('/forbidden', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'error_forbidden.html.twig');
});

$app->get('/error_internal', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'error_internal.html.twig');
});

function isInteger($input){
    return(ctype_digit(strval($input)));
}

$app->get('/category/{id}', function (Request $request, Response $response, array $args) {
    $view = Twig::fromRequest($request);

    $categories = DB::query( "SELECT * FROM categories");

    $categoryId = $args['id'];

    if(!isInteger($categoryId) 
        || $categoryId < 1 
        || $categoryId > count($categories))
    {
        return $response->withHeader("Location","/forbidden",403);
    }


    $totalProducts = DB::queryFirstField("SELECT COUNT(*) AS 'count' FROM products");

    $totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);
    
    $get = $request->getQueryParams();

    $currentPage = 1;
    $productStart = 0;

    if(isset($get['page'])){
        $currentPage = $get['page'];
    }

    if($currentPage < 1){
        $currentPage = 1;
    } else if($currentPage > $totalPages){
        $currentPage = $totalPages;
    }

    $productStart = ($currentPage - 1) * PRODUCTS_PER_PAGE;

    $products = DB::query(
        "SELECT p.id AS 'productId', p.name AS 'productName'
            , p.description, p.unitPrice, p.pictureFilePath
            , c.name AS 'categoryName'
        FROM products AS p
        JOIN categories AS c 
        ON c.id = p.categoryId
        WHERE c.id = %i
        LIMIT %i,%i ",$categoryId, $productStart, PRODUCTS_PER_PAGE
    );

    return $view->render($response, 'index.html.twig',[
        'products' => $products,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'categories' => $categories
    ]);
});

$app->get('/', function (Request $request, Response $response, array $args) {

    $categories = DB::query( "SELECT * FROM categories");
    $view = Twig::fromRequest($request);

    $currentPage = 1;
    $productStart = 0;

    $totalProducts = DB::queryFirstField("SELECT COUNT(*) AS 'count' FROM products");
    $totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);
    
    $get = $request->getQueryParams();
    if(isset($get['page'])){
        $currentPage = $get['page'];
    }

    if($currentPage < 1) {
        // could show an error page here instead
        $currentPage = 1;
    } else if($currentPage > $totalPages) {
        // could show an error page here instead
        $currentPage = $totalPages;
    }

    $productSkip = ($currentPage - 1) * PRODUCTS_PER_PAGE;

    $products = DB::query(
        "SELECT p.id AS 'productId', p.name AS 'productName'
            , p.description, p.unitPrice, p.pictureFilePath
            , c.name AS 'categoryName'
        FROM products AS p
        JOIN categories AS c 
        ON c.id = p.categoryId
        ORDER BY p.id LIMIT %i,%i ", $productSkip, PRODUCTS_PER_PAGE
    );

    return $view->render($response, 'index.html.twig',[
        'products' => $products,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'categories' => $categories
    ]);
});

// +++ PAGINATION USING AJAX

$app->get('/ajax/productpage/{pageNo:[0-9]+}', function (Request $request, Response $response, array $args) {
    $view = Twig::fromRequest($request);
    $pageNo = $args['pageNo'];
    $productSkip = ($pageNo - 1) * PRODUCTS_PER_PAGE;
    $productsList = DB::query("SELECT * FROM products ORDER BY id LIMIT %i,%i ", $productSkip, PRODUCTS_PER_PAGE);    
    return $view->render($response, 'ajax_productpage.html.twig', [ 'productsList' => $productsList ]);
});

// ajax pagination index
$app->get('/ap', function (Request $request, Response $response, array $args) {

    $categories = DB::query( "SELECT * FROM categories");
    $view = Twig::fromRequest($request);

    $totalProducts = DB::queryFirstField("SELECT COUNT(*) AS 'count' FROM products");
    $totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);

    return $view->render($response, 'ap_index.html.twig',[
        'totalPages' => $totalPages,
        'categories' => $categories
    ]);
});



// --- PAGINATION USING AJAX

$app->get('/login', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    if (isset($_SESSION['user'])) {
        //TODO: Add loging message
        return $response->withHeader('Location', '/');
    }

    return $view->render($response, 'login.html.twig');
});

$app->post('/login', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    if (isset($_SESSION['user'])) {
        return $response->withHeader('Location', '/');
    }

    $loginInfo = $request->getParsedBody();

    if ($loginInfo != null) {
        if (isset($loginInfo['email']) && isset($loginInfo['password'])) {
            $user = DB::queryFirstRow("SELECT * FROM users WHERE email= %s", $loginInfo['email']);
            if ($loginInfo['password'] === $user['password']) {
                unset($user['password']);
                $_SESSION['user'] = $user;

                return $response
                    ->withHeader('Location', '/');
            }
        }
    }

    return $view->render($response, 'login.html.twig', [
        'error' => "Email doesn't match password."
    ]);
});

$app->get('/logout', function (Request $request, Response $response) {
    unset($_SESSION['user']);
    return $response->withHeader('Location', '/');
});

$app->get('/register', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'register.html.twig');
});

$app->post('/register', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);

    if (isset($_SESSION['user'])) {
        return $response->withHeader('Location', '/');
    }

    $registerInfo = $request->getParsedBody();

    $email = $registerInfo['email'];
    $name = $registerInfo['name'];
    $password = $registerInfo['password'];
    $errors = [];

    if (strlen($name) < 5 || strlen($name) > 20) {
        $errors['name'] = "Name must be 5~20 chars";
        $registerInfo['name'] = '';
    } 

    if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $errors['email'] = "Invalid Email";
        $email = '';
    } elseif (isEmailTaken($email)) {
        $errors['email'] = "User is already exist.";
        $email= '';
    }


    if (strlen($password) < 6 || strlen($password) > 100
        || preg_match("/[a-z]/", $password) == false
        || preg_match("/[A-Z]/", $password) == false
        || preg_match("/[0-9#$%^&*()+=-\[\]';,.\/{}|:<>?~]/", $password) == false) {
        $errors['password'] = "Password must be 6~100 characters,
                            must contain at least one uppercase letter, 
                            one lower case letter, 
                            and one number or special character.";
    } elseif ($password !== $_POST['confirm']) {
        $errors['password'] = "Passwords must be same.";
    }

    if (empty($errors)) {
        DB::insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'isAdmin' => 'false'
        ]);
        $_SESSION['user'] = DB::queryFirstRow("SELECT * FROM users WHERE email = %s",$email);

        return $response->withHeader('Location', '/');
    }

    return $view->render($response, 'register.html.twig', [
        'errors' => $errors,
        'prevInput' => [
            'name' => $name,
            'email' => $email
        ]
    ]);
});

$app->get('/register/isemailtaken/[{email}]', function (Request $request, Response $response, array $args){
    $error = '';

    if(isset($args['email'])){
        $error = isEmailTaken($args['email']) ? "It's already taken." :'';
    }

    $response->getBody()->write($error);
    return $response;
});


$app->get('/addToCart', function (Request $request, Response $response) {

    $sessionId = $_COOKIE['PHPSESSID'];
    $get = $request->getQueryParams();

    $message = 'failed';
    if(isset($get['quantity']) && isset($get['productId'])){

        $productId = $get['productId'];
        
        if($get['quantity'] > 0){
            $product = DB::queryFirstRow(
                "SELECT id
                FROM products 
                WHERE id = %i",$productId
            );


            if( isset($product['id'])){

                $quantity = $get['quantity'];

                $cartItem = DB::queryFirstRow(
                    "SELECT quantity 
                    FROM cartItems 
                    WHERE sessionId = %s
                    AND productId = %i", $sessionId, $productId
                );

                if(isset($cartItem['quantity'])){
                    $quantity += $cartItem['quantity'];
                }

                DB::insertUpdate("cartItems",[
                    'sessionId' => $sessionId,
                    'productId' => $productId,
                    'quantity' => $quantity
                ]);

                $message = "succeed";
            }
        }
    }
    
    $response->getBody()->write($message);
    return $response;
});

$app->get('/cart', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'cart.html.twig');
});
$app->run();


function isEmailTaken($email)
{
    $users = DB::queryFirstRow("SELECT COUNT(*) AS 'count' FROM users WHERE email = %s", $email);

    if ($users['count'] == 0) {
        return false;
    } elseif ($users['count'] == 1) {
        return true;
    } else {
        echo "Internal Error: duplicate username.";//FIXME : Log instead of echoing
        return true;
    }
}