<?php

namespace App\Controller;

use App\Entity\Guestcontactaddress;
use App\Entity\Images;
use App\Entity\Guestcontact;
use App\Entity\Categories;
use App\Entity\Texts;
use App\Entity\Vouchers;
use App\Entity\Zonemap;
Use App\Entity\SystemConfig;
use App\Entity\Zonemapdrawing;
use App\Repository\CategoriesRepository;
use App\Service\SessionService;
use App\Service\VouchersService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isNull;

class DefaultController extends AbstractController
{
    private $sessionService;
    private $vouchersService;
    public function __construct(SessionService $sessionService, VouchersService $vouchersService)
    {
        $this->sessionService = $sessionService;
        $this->vouchersService = $vouchersService;
    }

    /**
     * @Route("/")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $categories = $doctrine->getRepository(Categories::class)->findBy(array('state'=>'1', 'menustate' => '1'));

        $banner1 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner1']);
        $banner2 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);

        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
        $texts = $doctrine->getRepository(Texts::class)->findBy(['id' => [1,2]]);
        $name = '';

        $session = $this->sessionService->checkSession();

        $costumer = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
        if (!is_null($costumer)) {
            $name = $costumer->getName();
        }

        $en = $doctrine->getManager();
        $conn = $en->getConnection();

        $query = 'SELECT MAX(price) AS maxPrice, MIN(price) AS minPrice FROM products;';

        $rangeInfo = $conn->query("$query")->fetch();

        return $this->render('default/index.html.twig', [
            'titlePage' => $configs->getCompanyName(),
            'banner1' => $banner1,
            'banner2' => $banner2,
            'categories' => $categories,
            'texts' => $texts,
            'name' => $name,
            'minPrice' => $rangeInfo['minPrice'],
            'maxPrice' => $rangeInfo['maxPrice'],
        ]);

    }

    /**
     * @Route("/tela_produtos")
     */
    public function telaProdutos(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Categories::class)->findBy(array('state'=>'1', 'menustate' => '1'));

        $banner1 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner1']);
        $banner2 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);

        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
        $texts = $doctrine->getRepository(Texts::class)->findBy(['id' => [1,2]]);
        $name = '';

        $session = $this->sessionService->checkSession();

        $costumer = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
        if (!is_null($costumer)) {

            $name = $costumer->getName();
        }

        return $this->render('default/products.html.twig', [
            'titlePage' => $configs->getCompanyName(),
            'banner1' => $banner1,
            'banner2' => $banner2,
            'categories' => $categories,
            'texts' => $texts,
            'name' => $name
        ]);

    }


    /**
     * @Route("/reload", name="app_default_reload")
     */
    public function reload(ManagerRegistry $doctrine): Response
    {

        return $this->render('default/reload.html.twig', [

        ]);
    }

    /**
     * @Route("/index", name="app_default")
     */
    public function index2(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Categories::class)->findBy(array('state'=>'1', 'menustate' => '1'));

        $banner1 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner1']);
        $banner2 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);

        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
        $texts = $doctrine->getRepository(Texts::class)->findBy(['id' => [1,2]]);

        return $this->render('default/index.html.twig', [
            'titlePage' => $configs->getCompanyName(),
            'banner1' => $banner1,
            'banner2' => $banner2,
            'categories' => $categories,
            'texts' => $texts
        ]);
    }

    /**
     * @Route("/repairs_custom", name="repairs_custom")
     */
    public function repairs_custom(ManagerRegistry $doctrine): Response
    {
        $banner2 = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);


        return $this->render('repairs_and_custom/index.html.twig', [
        'banner2' => $banner2
        ]);
    }


    /**
     * @Route("/terms")
     */
    public function terms(): Response
    {
        return $this->render('details/termsAndConditions.html.twig');
    }

    /**
     * @Route("/privacy-policy")
     */
    public function privacy(): Response
    {
        return $this->render('termsandcondition.html.twig');
    }


    /**
     * @Route("/about-us")
     */
    public function costumerfavorites(): Response
    {
        return $this->render('aboutus/index.html.twig');
    }

//    /**
//     * @Route("/index", name="app_default")
//     */
//    public function index2(ManagerRegistry $doctrine): Response
//    {
//        $categories = $doctrine->getRepository(Categories::class)->findBy(array('state'=>'1', 'menustate' => '1'));
//        $images = $doctrine->getRepository(Images::class)->findAll();
//        $configs = $doctrine->getRepository(SystemConfig::class)->find(1);
//
//        return $this->render('default/index.html.twig', [
//            'titlePage' => $configs->getCompanyName(),
//            'images' => $images,
//            'categories' => $categories
//        ]);
//    }

    /**
     * @Route("/check-zone-price-by-address", name="app_check_poly_zone_by_address")
     */
    public function polycheckprice(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->sessionService->checkSession();

        $getCordinatesData = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('session' => $session));


        $street = $request->get('Gstreet');
        $city = $request->get('Gcity');
        $postalcode = $request->get('Gpostal');
        $referencePoint = $request->get('GreferencePoint');
        $lantitude = $getCordinatesData->getLantitude();
        $longitude = $getCordinatesData->getLongitude();



        $getZones = $doctrine->getRepository(Zonemap::class)->findAll();
        $getCordinates = $doctrine->getRepository(Zonemapdrawing::class)->findAll();


        return $this->render('ordering/check-zone-price.html.twig', [
            'titlePage' => 'Localização',
            'zones' => $getZones,
            'cordinates' => $getCordinates,
            'ltd' => $lantitude,
            'lng' => $longitude
        ]);
    }

    /**
     * @Route("/get-zone-price-by-address", name="app_get_poly_zone_by_address")
     */
    public function getpolycheckprice(ManagerRegistry $doctrine, Request $request): Response
    {

        $arrayZones = $request->get('test');
        $zonesData = array();
        $session = $this->sessionService->checkSession();
        $en = $doctrine->getManager();
        $getCordinatesData = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('session' => $session));

        if($arrayZones == null){
            $getCordinatesData->setDeliveryzoneid(0);
            $en->persist($getCordinatesData);
            $en->flush();

        }else{

            foreach ($arrayZones as $values){
                $zonesData[] =  $doctrine->getRepository(Zonemap::class)->findBy(array('title'=> $values));
            }

            $lowestPrice = min($zonesData);

            $getCordinatesData->setDeliveryzoneid($lowestPrice['0']->getid());
            $en->persist($getCordinatesData);
            $en->flush();

        }


        $en->persist($getCordinatesData);
        $en->flush();



//        $street = $request->get('Gstreet');
//        $city = $request->get('Gcity');
//        $postalcode = $request->get('Gpostal');
//        $referencePoint = $request->get('GreferencePoint');
//        $lantitude = $request->get('Gltd');
//        $longitude = $request->get('Glng');
//
//
//
//
//        $getZones = $doctrine->getRepository(Zonemap::class)->findAll();
//        $getCordinates = $doctrine->getRepository(Zonemapdrawing::class)->findAll();


        return new Response();
    }



    /**
     * @Route("/vouchers")
     */
    public function vouchers(): Response
    {
        $vouchers = $this->vouchersService->getVouchers();

        return $this->render('vouchers/index.html.twig', [
            'titlePage' => 'Vouchers',
            'vouchers' => $vouchers
        ]);

    }


    /**
     * @Route("/new_voucher")
     */
    public function newVoucher(Request $request): Response
    {
        $voucherName = $request->get('itemName');
        $voucherPercentage = $request->get('itemPercentage');
        $voucherActive = $request->get('itemActive');

        $this->vouchersService->newVoucher($voucherName, $voucherPercentage, $voucherActive);
        return new Response();
    }

    /**
     * @Route("/get_vouchers")
     */
    public function getVouchers()
    {
        return new JsonResponse($this->vouchersService->getVouchers());

    }

    /**
     * @Route("/change_voucher_state")
     */
    public function changeVoucherState(Request $request)
    {
        $voucherId = $request->get('voucherId');
        return new JsonResponse($this->vouchersService->changeVoucherState($voucherId));

    }

    /**
     * @Route("/apply_voucher")
     */
    public function applyVoucher(Request $request)
    {
        $session = $this->sessionService->checkSession();

        $voucher = $request->get('voucher');
        return new JsonResponse($this->vouchersService->applyVoucher($voucher, $session));

    }

    /**
     * @Route("/remove_voucher")
     */
    public function removeVoucher(Request $request)
    {
        $orderCartId = $request->get('orderCartId');

       $this->vouchersService->removeVoucher($orderCartId);
        return new Response();
    }
}
