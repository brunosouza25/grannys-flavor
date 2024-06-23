<?php

namespace App\Controller\APP;


use App\Entity\Cart;
use App\Entity\Guestcontact;
use App\Entity\SystemConfig;
use App\Service\CartService;
use App\Service\OrderCartService;
use App\Service\SessionService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function Symfony\Component\String\s;


class CostumerController extends AbstractController
{

    private $sessionService;
    private $cartService;
    private $orderCartService;
    public function __construct(SessionService $sessionService, CartService $cartService, OrderCartService $orderCartService)
    {
        $this->sessionService = $sessionService;
        $this->cartService = $cartService;
        $this->orderCartService = $orderCartService;
    }


    /**
     * @Route("/register_costumer")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        return $this->render('costumer/register.html.twig');
    }
    /**
     * @Route("/login")
     */
    public function index2(ManagerRegistry $doctrine): Response
    {
        return $this->render('costumer/index.html.twig');
    }

    /**
     * @Route("/reset_password")
     */
    public function reset_password(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $code = $request->get('code');

        $user = $doctrine->getRepository(Guestcontact::class)->findOneBy(['email' => $email]);
        if($user->getCode() != $code){
            return new JsonResponse(0);
        }

        $user->setPassword($userPasswordHasher->hashPassword($user, $password));
        $user->setCode('');
        $doctrine->getManager()->flush();
        return new JsonResponse(1);
    }



    /**
     * @Route("/singUp")
     */
    public function singUp( Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $guestemail = $request->get('email');
        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findby(['email'=>$guestemail]);
        //dd($getContactGuest);

        if($getContactGuest == null ){
           $session = $this->sessionService->checkSession();

            $addGuestData = new Guestcontact();
            $addGuestData->setName($request->get('username'));
            $addGuestData->setSession(null);
            $addGuestData->setEmail($guestemail);
            $addGuestData->setLastname('');
            $addGuestData->setContact($request->get('contact-number'));
            $addGuestData->setState(0);
            $addGuestData->setSession($session);
            $addGuestData->setCode(rand(0,999999));
            $date = new \DateTime(date('Y-m-d H:i:s'));

            $addGuestData->setSigninDate($date);
            $password = $userPasswordHasher->hashPassword($addGuestData, $request->get('password'));
            $addGuestData->setPassword($password);

            $doctrine->getManager()->persist($addGuestData);
            $doctrine->getManager()->flush();

                function generateRandomString($length = 23) {
                    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
                }
                $valueSession = generateRandomString();
                setcookie("session", $valueSession, time()+(10*365*24*60*60));  /* expire in 1 hour */



            return new JsonResponse([
                'state' => '0'
            ]);

        }else{
            return new JsonResponse([
                'state' => '1',
                'email' => $guestemail
            ]);
        }
    }

    /**
     * @Route("/singin")
     */
    public function logIn(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher, AuthenticationUtils $authenticationUtils): Response
    {
        $guestemail = $request->get('email');
        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findOneBy(['email' => $guestemail]);

        //criar session recover e retornar para o front
        $session = $this->sessionService->checkSession();
        if (!$getContactGuest == null) {
            if ($userPasswordHasher->isPasswordValid($getContactGuest, $request->get('password'))) {

                if ($getContactGuest->getSession() != $session) {
                    $productsCart = $doctrine->getRepository(Cart::class)->findBy(['session' => $getContactGuest->getSession()]);
                    $this->orderCartService->updateSession($getContactGuest->getSession(), $session);
                    $orderCart = $this->orderCartService->checkSessionOrderCart($session);
                    foreach ($productsCart as $productCart) {
                        $productCart->setSession($session);
                        $productCart->setOrderCartId($orderCart['id']);
                    }
                }

                $getContactGuest->setSession($session);


                $doctrine->getManager()->persist($getContactGuest);
                $doctrine->getManager()->flush();

//                if ($this->getUser()) {
//                    if($this->getUser()->getRoles() == 'ROLE_USER'){
//                        return $this->redirectToRoute('admin/app_dashboard');
//                    }else{
//                        return $this->redirectToRoute('app/app_dashboard');
//                    }
//
//                }
                return new JsonResponse([
                    'state' => '0',
                ]);
            }else {
                return new JsonResponse([
                    'state' => '1',
                    'email' => $request->get('email')
                ]);
            }
        } else {
            return new JsonResponse([
                'state' => '1'
            ]);
        }
    }

    /**
     * @Route("/logout")
     */
    public function logOut(ManagerRegistry $doctrine): Response
    {
        $this->sessionService->destroySession();

        return $this->render('costumer/index.html.twig');
    }

    /**
     * @Route("/user_status")
     */
    public function userStatus(ManagerRegistry $doctrine): Response
    {
        $session = $this->sessionService->checkSession();
        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        if(!is_null($getContactGuest)){
            $array['name'] = $getContactGuest->getName();
            return new JsonResponse($array);
        }

        return new JsonResponse(false);

    }

    /**
     * @Route("/update_user")
     */
    public function updateUser(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $session = $this->sessionService->checkSession();
        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        $getContactGuest->setName($request->get('name'));
        $getContactGuest->setContact($request->get('phone'));
        $getContactGuest->setEmail($request->get('emailUserName'));

        $en->persist($getContactGuest);
        $en->flush();

        return new Response();
    }
    /**
     * @Route("/recover_send")
     */
    public function recoverSend(Request $request, ManagerRegistry $doctrine): Response
    {
        $guestemail = $request->get('email');
        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findOneby(['email'=>$guestemail]);

        if (is_null($getContactGuest)) {
            return new JsonResponse();
        }

        $getContactGuest->setCode(rand(10000000, 99999999));

        $doctrine->getManager()->flush();

        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        //Server settings
//        $mail->SMTPDebug  = SMTP::DEBUG_CLIENT;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'mail.grannysflavor.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'email@grannysflavor.com';                     //SMTP username
        $mail->Password   = '?WFbLEr$b-kl';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;


                                 //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS
            //Recipients
            $mail->setFrom($mail->Username, 'Grannys Flavor');

            $mail->addAddress($guestemail, $getContactGuest->getName());     //Add a recipient
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Recuperação de senha';

            $url = $_SERVER['HTTP_HOST'].'/recover_password';
            $code = $getContactGuest->getCode();
            $mail-> Body ="
                <html>
                    <body>
                        <h1>Codigo de Recuperação de e-mail</h1>
                        <p>Código: $code</p>
                        <p>Clique no link abaixo para recuperar sua senha</p>
                        <a target='_blank' href='https://$url'>$url</a>
                    </body>
                </html>";

        $mail->send();

        return new JsonResponse([
            'statusEmail' => '1',
            'email' => $guestemail
        ]);
    }
    /**
     * @Route("/recover_confirmation")
     */
    public function recoverConfirm(Request $request, ManagerRegistry $doctrine): Response
    {
        $guestemail = $request->get('email');

        $contact = $doctrine->getRepository(Guestcontact::class)->findOneby(['email'=>$guestemail]);

           if ($request->get('code') == $contact->getCode()){
               return new JsonResponse([
                   'statusEmail' => '1'

               ]);
           }


        return new JsonResponse([
            'statusEmail' => '0'
        ]);
    }
    /**
     * @Route("/recover_password")
     */
    public function recoverPassword(): Response
    {
        return $this->render('costumer_password_recover/resetpassword.html.twig');
    }

    /**
     * @Route("/costumer_profile")
     */
    public function costumerProfile(ManagerRegistry $doctrine): Response
    {
        $session = $this->sessionService->checkSession();

        $user = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        if (is_null($user)) {
            header("Location: /login");
            exit;
        }

        return $this->render('costumer_profile/index.html.twig');
    }

    /**
     * @Route("/costumerform", name="/costumerform")
     * Template to get configs
     */
    public function get_configs(ManagerRegistry $doctrine): Response
    {
        $session = $this->sessionService->checkSession();

        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        $name = $getContactGuest->getName();
        $contact = $getContactGuest->getContact();
        $email = $getContactGuest->getEmail();

        return $this->render('costumer_profile/costumerform.html.twig', [
            'name' => $name,
            'phone' => $contact,
            'email' => $email
        ]);
    }

    /**
     * @Route("/update_cart_quantity", name="/update_cart_quantity")
     * Template to get configs
     */
    public function updateCartQuantity(ManagerRegistry $doctrine, Request $request): Response
    {
        $quantity = $request->get('quantity');
        $cartId = $request->get('cartId');

        $this->cartService->updateCartQuantity($quantity, $cartId);
        return new Response();
    }
    /**
     * @Route("/costumerPW", name="/costumerPW")
     */
    public function costumerPW(): Response
    {
        return $this->render('costumer_profile/costumerPW.html.twig');
    }

    /**
     * @Route("/costumePWset", name="/costumerPWset")
     * Template to get configs
     */
    public function changePassword(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $session = $this->sessionService->checkSession();
        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        if (trim($request->get('password1')) == '' || trim($request->get('password2')) == '') {
            return new JsonResponse([
                'state' => '0',
            ]);
        }

        if ($request->get('password1') == $request->get('password2')) {

            $getContactGuest->setPassword($userPasswordHasher->hashPassword($getContactGuest, $request->get('password1')));
            return new JsonResponse([
                'state' => '0',
            ]);
        }

        return new JsonResponse([
            'state' => '1',
        ]);
    }

    /**
     * @Route("/get_costumer_orders", name="get_costumer_orders")
     */
    public function getOrders(ManagerRegistry $doctrine, Request $request): Response
    {
        $limit = $request->get('limit');
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $session = $this->sessionService->checkSession();

        $userId = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session])->getId();

        $query = $qb->select('a')
            ->from('App\Entity\Orders', 'a')
            ->setMaxResults($limit)
            ->where("a.user_id = $userId")
            ->orderBy('a.id', 'desc')
            ->getQuery();

        $conn = $en->getConnection();

        $fee = $doctrine->getRepository(SystemConfig::class)->find(1)->getFixedFee();

        $orders = $query->getArrayResult();
        $newOrders = [];
        foreach ($orders as $orderArray) {
            $order = (object)$orderArray;
            $total = $conn->query("SELECT SUM(price * qtd) as total FROM  orderlist WHERE orderid = $order->id;")->fetch()['total'];

            $costumer = $conn->query("SELECT name FROM guestcontact WHERE id = $order->user_id;")->fetch()['name'];

            $deliveryAddress =  $conn->query("SELECT * FROM guestcontactaddress WHERE id = $order->delivery_address_id;")->fetch();
            if(!empty($order->invoicing_address_id)) {

                $invoicingAddress =  $conn->query("SELECT * FROM guestcontactaddress WHERE id = $order->invoicing_address_id;")->fetch();
                $order->invoicing_address_id = $invoicingAddress;

            }
            $payment = $conn->query("SELECT * FROM order_payments WHERE order_id = $order->id;")->fetch();

            if (!is_null($order->voucher)) {
                $total -= $order->voucher_discount;
            }

            $order->costumer = $costumer;
            $order->deliveryAddress = $deliveryAddress;
            $order->total = number_format($total + $fee, 2);
            $order->paymentStatus = $payment;

            $newOrders[] = $order;
        }

        $array['orders'] = $newOrders;
        return new JsonResponse($array);
    }

    /**
     * @Route("/costumerfavorites", name="costumerfavorites")
     */
    public function costumerfavorites(): Response
    {
        return $this->render('costumer_profile/costumerfavorites.html.twig');
    }

}
