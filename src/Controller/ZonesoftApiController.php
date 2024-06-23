<?php

namespace App\Controller;

use App\Entity\Zonesoftapi;
use App\Entity\Zonesoftdata;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ZonesoftApiController extends AbstractController
{
    /**
     * @Route("/auth/login", name="zonesoft_api_login")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $linkprojetct = $en->getRepository(Zonesoftdata::class)->find(1);

        //essa url é uma coluna que falta, mas como não se usa eu decidi comentar e não criar ela, da para se chegar no banco do
        //integrat na tabela zonesoft
//        $url = $linkprojetct->getLink();


//        $headers = get_headers($url);

        header("Accept: application/json");
        header("Content-Type:application/json");


        $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);


        $app_store_secret = $tokendata->getAppsecret();
        $app_store_username= $tokendata->getAppkey();


        $data = json_decode(file_get_contents("php://input"));

        // var_dump(http_response_code());

        if($data == null){
            $JsonTokenReturn = [
                "header" => [
                    "statusCode" => 401,
                    "statusMessage" => "Unauthorized",
                    "status" => "HTTP/1.1 401 Unauthorized",
                ],
            ];

            $data = json_encode($JsonTokenReturn, true);

            print_r($data);
        }else{
            $api_store_username = $data->app_store_username;
            $api_store_secret = $data->app_store_secret;



            if($app_store_username == $api_store_username && $app_store_secret == $api_store_secret){
                function generateRandomString($length = 10) {
                    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
                }

                $tokenString = generateRandomString();

                $tokendata->setToken($tokenString);
                $en->persist($tokendata);
                $en->flush();


                $JsonTokenReturn = [
                    "access_token" => $tokenString, "expires_in" => 2592000

                ];


                $data = json_encode($JsonTokenReturn, true);

                print_r($data);

            }else{

                $JsonTokenReturn = [
                    "header" => [
                        "statusCode" => 401,
                        "statusMessage" => "Unauthorized",
                        "status" => "HTTP/1.1 401 Unauthorized",
                    ],
                ];

                $data = json_encode($JsonTokenReturn, true);

                print_r($data);

            }
        }



        return $this->render('zonesoft_api/index.html.twig');
    }
    /**
     * @Route("/sync/menu", name="zonesoft_api_menu")
     */
    public function indexMenu(ManagerRegistry $doctrine)
    {

        ini_set("allow_url_fopen", 1);

        $en = $doctrine->getManager();

        $token = $en->getRepository(Zonesoftapi::class)->find(1);

        $tonekNr = $token->getToken();

        header("Accept: application/json");
        header('Authorization: '.$tonekNr);






        $data = json_decode(file_get_contents("php://input"));


        $statusCode = http_response_code();

        $response = new Response();
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
        return $response;



        // dd(http_response_code());


        // $JsonTokenReturn = [
        //         $headers
        //      ];

        // if($statusCode == '204'){
        //      $JsonTokenReturn = [];
        // }
        // if($statusCode == 400){
        //      $JsonTokenReturn = [
        //          "error" => "Field validation for families failed on the required tag"
        //          ];
        // }





        // $datar = json_encode($JsonTokenReturn, true);

        // print_r($datar);

        //  return $this->json($JsonTokenReturn);





        // $url = 'https://beta.integrated-erp.com/auth/login';

        // $data = array("app_store_username" => "91A84CF78101728E334BC4637069DE29","app_store_secret" => "588636A8588636A87373E373E3ABAB82AB82F5DA524A28A5A210524A28A5A210");

        // $postdata = json_encode($data);

        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        // $result = curl_exec($ch);
        // curl_close($ch);
        // print_r ($result);


        // return $this->render('zonesoft_api/menu.html.twig', []);
    }

    /**
     * @Route("/order/status", name="zonesoft_api_order")
     */
    public function indexOrder(ManagerRegistry $doctrine)
    {


        ini_set("allow_url_fopen", 1);

        $en = $doctrine->getManager();

        $token = $en->getRepository(Zonesoftapi::class)->find(1);

        $tonekNr = $token->getToken();


        header("Accept: application/json");
        header('Authorization: '.$tonekNr);

        $data = json_decode(file_get_contents("php://input"));


//         if($data != null){
//
//            $orderID = $data->order_id;
//            $status = $data->status;
//
//
//
//        }



        $response = new Response();
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
        return $response;


//        return $this->render('zonesoft_api/index.html.twig');

        // return $this->render('zonesoft_api/order.html.twig', [
        //     'controller_name' => 'ZonesoftApiController',
        // ]);
    }


    /**
     * @Route("/pos/status", name="zonesoft_api_pos")
     */
    public function indexPos(ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $token = $en->getRepository(Zonesoftapi::class)->find(1);

        $tonekNr = $token->getToken();

        header("Accept: application/json");
        header("Access-Control-Allow-Methods: GET");
        header('Authorization: '.$tonekNr);




        $data = json_decode(file_get_contents("php://input"));


        $statusCode = http_response_code();

        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        // $response->setStatusCode(Response::HTTP_NO_CONTENT);
        return $response;


        // return $this->render('zonesoft_api/status.html.twig', []);
    }

    /**
     * @Route("/pos/status/closing", name="zonesoft_api_pos_closing")
     */
    public function indexPosClosing(ManagerRegistry $doctrine)
    {

        $en = $doctrine->getManager();

        $token = $en->getRepository(Zonesoftapi::class)->find(1);

        $tonekNr = $token->getToken();

        header("Accept: application/json");
        header("Access-Control-Allow-Methods: OPTIONS,PUT,DELETE");

        header('Authorization: '.$tonekNr);

        $requestMethod = $_SERVER["REQUEST_METHOD"];


        $data = json_decode(file_get_contents("php://input"));


        $statusCode = http_response_code();

        if($requestMethod == "PUT"){
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
            return $response;
        }

        if($requestMethod == "PUT"){
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
            return $response;
        }





        // return $this->render('zonesoft_api/index.html.twig', [
        //     'controller_name' => 'ZonesoftApiController',
        // ]);
    }
}
