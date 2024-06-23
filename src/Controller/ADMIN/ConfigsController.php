<?php

namespace App\Controller\ADMIN;

use App\Entity\Gallery;
use App\Entity\Guestcontact;
use App\Entity\Images;
use App\Entity\PayByrdConfig;
use App\Entity\StripeConfig;
use App\Entity\SystemConfig;
use App\Entity\Texts;
use App\Entity\Vivawallet;
use App\Service\SessionService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin", name="admin/") */
class ConfigsController extends AbstractController
{
    private $sessionService;
    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * @Route("/images", name="images")
     * Template to select new images
     */
    public function images(ManagerRegistry $doctrine): Response
    {
        $images = $doctrine->getRepository(Images::class)->findAll();

        return $this->render('ADMIN/configs/selectImages.html.twig', [
            'titlePage' => 'Imagens',
            'images' => $images
        ]);
    }

    /**
     * @Route("/select_images", name="select_images")
     * Function to change Company's Images
     */
    public function selectImages(ManagerRegistry $doctrine, Request $request): Response
    {

        if (isset($_FILES) && !empty($_FILES)) {
            $en = $doctrine->getManager();
            $type = $request->get('type');
            $image = $en->getRepository(Images::class)->findOneBy(['type' => $request->get('type')]);
            if (!empty($image)) {
                $en->remove($image);
                $en->flush();
            }

            $path = "uploads/images/" . $type . '.' . explode('/', $_FILES['products_uploaded']['type'][0])[1];

            if (file_exists($path)) {
                unlink($path);
            }

            copy($_FILES['products_uploaded']['tmp_name'][0], $path);

            $uploadImg = new Images();
            $uploadImg->setPath($path);
            $uploadImg->setType($type);
            $en->persist($uploadImg);
            $en->flush();
        }
        return new Response();
    }

    /**
     * @Route("/get_configs", name="get_configs")
     * Template to get configs
     */
    public function get_configs(ManagerRegistry $doctrine): Response
    {
        $images = $doctrine->getRepository(Images::class)->findAll();

        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
        $vivawallet = $doctrine->getRepository(Vivawallet::class)->find(1);
        $stripe = $doctrine->getRepository(StripeConfig::class)->find(1);
//        dd($configs);
        return $this->render('ADMIN/configs/getConfigs.html.twig', [
            'titlePage' => 'Vivawallet',
            'images' => $images,
            'configs' => $configs,
            'stripe' => $stripe
        ]);
    }

    /**
     * @Route("/get_config", name="get_config")
     * Template to get configs
     */
    public function get_config(ManagerRegistry $doctrine): Response
    {
        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
        $session = $this->sessionService->checkSession();
        $userName = '';

        $user = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        if (!is_null($user)) {
            $userName = $user->getName();
        }



        return new JsonResponse([
            'companyname' => $configs->getCompanyName(),
            'address' => $configs->getAddress(),
            'phone1' => $configs->getPhone1(),
            'email' => $configs->getEmailusername(),
            'emailContact' => $configs->getEmailContact(),
            'hoursopen' => $configs->getHoursOpen(),
            'instagram' => $configs->getInstagram(),
            'facebook' => $configs->getFacebook(),
            'tripadvisorlink' => $configs->getTripadvisorLink(),
            'youtube' => $configs->getYoutubeLink(),
            'userName' => $userName,
            'contactEmail'=>$configs->getEmailusername(),
            'contactPhone'=>$configs->getPhone1(),
            'contactAddress'=>$configs->getAddress()
        ]);

    }

    /**
     * @Route("/get_id_user", name="get_id_user")
     * Template to get configs
     */
    public static function get_id_user(ManagerRegistry $doctrine): int
    {

        $idUser = '';
        if (isset($_COOKIE['session'])) {
            $user = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $_COOKIE['session']]);

            if (!is_null($user)) {
                $idUser = $user->getId();
            }

        }
        return $idUser;
    }
    public function get_multiple_configs(ManagerRegistry $doctrine, $multipleConfigs): Response
    {
        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);

        return new JsonResponse([
            'companyname' => $configs->getCompanyName(),
            'address' => $configs->getAddress(),
            'phone1' => $configs->getPhone1(),
            'email' => $configs->getEmailusername(),
            'hoursopen' => $configs->getHoursOpen(),
            'instagram' => $configs->getInstagram(),
            'facebook' => $configs->getFacebook(),
        ]);

    }

    /**
     * @Route("/set_configs", name="set_configs")
     * Route to set configs
     */
    public function set_configs(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
        $stripe = $doctrine->getRepository(StripeConfig::class)->find(1);


        $configs->setCompanyName($request->get('companyname'));
        $configs->setAddress($request->get('address'));
        $configs->setEmailhost($request->get('emailHost'));
        $configs->setEmailusername($request->get('emailUserName'));
        $configs->setEmailContact($request->get('emailContact'));
        $configs->setNif($request->get('nif'));
        $configs->setPhone1($request->get('phone1'));
        $configs->setPhone2($request->get('phone2'));
        $configs->setInstagram($request->get('instagram'));
        $configs->setFacebook($request->get('facebook'));
        $configs->setFixedFee($request->get('fee'));
        $configs->setYoutubeLink($request->get('youtubeLink'));


        if (is_null($stripe)) {

            $newStripe = new StripeConfig();
            $newStripe->setToken($request->get('token'));
            $newStripe->setDevToken($request->get('tokenDev'));
            $en->persist($newStripe);

        } else {
            $stripe->setToken($request->get('token'));
            $stripe->setDevToken($request->get('tokenDev'));
        }

        $en->persist($configs);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/get_texts", name="get_texts")
     * Template to get configs
     */
    public function get_texts(ManagerRegistry $doctrine): Response
    {
        $texts = $doctrine->getRepository(Texts::class)->findAll();

        return $this->render('ADMIN/configs/texts.html.twig', [
            'titlePage' => 'Imagens',
            'texts' => $texts,
        ]);
    }

    /**
     * @Route("/set_text", name="set_text")
     * Route to set text
     */
    public function set_text(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $texts = $doctrine->getRepository(Texts::class)->findAll();

        foreach ($texts as $text) {
            $text->setDescription($request->get($text->getType() . "-text"));
            $text->setSubject($request->get($text->getType() . "-subject"));
            $en->persist($text);
        }

        $en->flush();

        return new Response();
    }


}
