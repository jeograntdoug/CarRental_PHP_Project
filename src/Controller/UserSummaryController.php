<?php

    namespace App\Controller;

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;
    use Slim\Views\Twig;
    use DB;

    class UserSummaryController
    {

        public function getProfile(Request $request, Response $response, array $args)
        {
            $view = Twig::fromRequest($request);
            if (isset($_SESSION['userId'])) {
                $userId = $_SESSION['userId'];
                $userInfo = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $userId);
                $orderSummary = DB::query("SELECT COUNT(*) as 'count', SUM(totalPrice) as 'expense' FROM orders WHERE userId = %s", $userId)[0];
                $reservationSummary = DB::query("SELECT COUNT(*) as 'count' FROM reservations WHERE userId = %s", $userId)[0];

                return $view->render($response, 'summary_profile.html.twig', [
                    'userInfo' => $userInfo,
                    'orderSummary' => $orderSummary,
                    'reservationSummary' => $reservationSummary
                ]);
            } else {
                return $view->render($response, 'login.html.twig', []);
            }
        }


    }