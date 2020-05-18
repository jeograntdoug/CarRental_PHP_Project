<?php
    declare(strict_types=1);

    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

// create a log channel

    $log = new Logger('main');
    $log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
    $log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));


    if (strpos($_SERVER['HTTP_HOST'], "ipd20.com") !== false) {
        // hosting on ipd20.com
        DB::$user = 'cp4966_carrental';
        DB::$password = 'vI4d(+~ALuQ)';
        DB::$dbName = 'cp4966_carrental';
        DB::$encoding = 'utf8'; // defaults to latin1 if omitted

    } else { // local computer
        DB::$user = 'carrental';
        DB::$password = 'QFzyVmD4mYO8Ah8G';  //each localhost has different password
        DB::$dbName = 'carrental';
        DB::$port = 3333;
        DB::$encoding = 'utf8'; // defaults to latin1 if omitted
    }


    DB::$error_handler = 'db_error_handler'; // runs on mysql query errors
    DB::$nonsql_error_handler = 'db_error_handler'; // runs on library errors (bad syntax, etc)

    function db_error_handler($params)
    {
        header("Location: /errors/internal", true, 500);

        global $log;
        $log->error("Database erorr[Connection]: " . $params['error']);

        if ($params['query']) {
            $log->error("Database error[Query]: " . $params['query']);
        }
        die();
    }
