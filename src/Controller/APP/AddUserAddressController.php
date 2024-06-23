<?php

namespace App\Controller\APP;

use App\Entity\User;
use App\Entity\UserAddress;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use telesign\sdk\messaging\MessagingClient;

/** @Route("/app", name="app") */

class AddUserAddressController extends AbstractController
{
    /**
     * @Route("/add/user/address", name="/app_add_user_address")
     */
    public function index(): Response
    {
        return $this->render('APP/add_user_address/index.html.twig', [
            'titlePage' => 'Adicionar Morada',
        ]);
    }

    /**
     * @Route("/save/user/address", name="/app_save_user_address")
     */
    public function Save(Request $request, ManagerRegistry $doctrine): Response
    {

        $street = $request->get('street');
        $city = $request->get('city');
        $postal = $request->get('postalcode');
        $userId = $request->get('userID');

        $saveAddres = new UserAddress();
        $saveAddres->setUserid($userId);
        $saveAddres->setStreet($street);
        $saveAddres->setCity($city);
        $saveAddres->setPostalcode($postal);

        $doctrine->getManager()->persist($saveAddres);
        $doctrine->getManager()->flush();

        return $this->render('APP/add_user_address/index.html.twig', [
            'titlePage' => 'Adicionar Morada',
        ]);
    }

    /**
     * @Route("/location/user/address", name="/app_location_user_address")
     */
    public function Locationuser(Request $request, ManagerRegistry $doctrine): Response
    {

        $getuserLocation = $doctrine->getRepository(UserAddress::class)->findOneBy(array('userid' => $this->getUser()->getId()));

        return $this->render('APP/add_user_address/confirmlocation.html.twig', [
            'titlePage' => 'Adicionar Morada',
            'useraddress' => $getuserLocation
        ]);
    }

    /**
     * @Route("/location-confirm/user/address", name="/app_location_confirm_user_address")
     */
    public function Locationuserconfirm(Request $request, ManagerRegistry $doctrine): Response
    {
        $gltd = $request->get('Gltd');
        $glng = $request->get('Glng');
        $getuserLocation = $doctrine->getRepository(UserAddress::class)->findOneBy(array('userid' => $this->getUser()->getId()));

        $getuserLocation->setLantitude($gltd);
        $getuserLocation->setLongitude($glng);
        $doctrine->getManager()->persist($getuserLocation);
        $doctrine->getManager()->flush();

        $savecity = $doctrine->getRepository(User::class)->find($this->getUser()->getId());
        $savecity->setCity($getuserLocation->getCity());
        $doctrine->getManager()->persist($savecity);
        $doctrine->getManager()->flush();


        return new Response();
    }

    /**
     * @Route("/phone/user/confirm", name="/app_phone_user_confirm")
     */
    public function phoneUserConfirm(Request $request, ManagerRegistry $doctrine): Response
    {

        $getuserdata = $doctrine->getRepository(User::class)->find($this->getUser()->getId());

        $code = rand(00000,99999);

        $getuserdata->setConfirmcode($code);
        $doctrine->getManager()->persist($getuserdata);
        $doctrine->getManager()->flush();

        $customer_id = "135BBB56-B8CC-404C-8FE1-09219DC133FC";
        $api_key = "LJmzI3AhkNQXsVryn4Xrk2WRhIi2NzTaJWM3YNxx3PpKDewhMladeGj6Yw82Ti/R7j+4h7wMUa1pGfkbC1FADQ==";
        $phone_number = '351'.$getuserdata->getPhone();
        $message = 'Código de Verificação é: '.$code.'. Obrigado';
        $message_type = "ARN";
        $messaging = new MessagingClient($customer_id, $api_key);
        $response = $messaging->message($phone_number, $message, $message_type);

        return $this->render('APP/add_user_address/phoneconfirm.html.twig', [
            'titlePage' => 'Adicionar Morada',
        ]);
    }


    /**
     * @Route("/phone/user/confirm/state", name="/app_phone_user_confirm_state")
     */
    public function phoneUserConfirmState(Request $request, ManagerRegistry $doctrine): Response
    {

        $code = $request->get('code');


        $getuserdata = $doctrine->getRepository(User::class)->find($this->getUser()->getId());

        if($getuserdata->getConfirmcode() == $code){

            $getuserdata->setPhonestate(1);
            $doctrine->getManager()->persist($getuserdata);
            $doctrine->getManager()->flush();
            $state = 1;
        }else{
            $state = 0;
        }

//
//        $code = rand(00000,99999);
//
//        $getuserdata->setConfirmcode($code);
//        $doctrine->getManager()->persist($getuserdata);
//        $doctrine->getManager()->flush();
//
//        $customer_id = "135BBB56-B8CC-404C-8FE1-09219DC133FC";
//        $api_key = "LJmzI3AhkNQXsVryn4Xrk2WRhIi2NzTaJWM3YNxx3PpKDewhMladeGj6Yw82Ti/R7j+4h7wMUa1pGfkbC1FADQ==";
//        $phone_number = '351'.$getuserdata->getPhone();
//        $message = 'Código de Verificação é: '.$code.'. Obrigado';
//        $message_type = "ARN";
//        $messaging = new MessagingClient($customer_id, $api_key);

      return new JsonResponse([
          'state' => $state
      ]);
    }

}
