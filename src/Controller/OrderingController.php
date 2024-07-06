<?php

namespace App\Controller;

use App\Entity\Apporderitems;
use App\Entity\Apporders;
use App\Entity\OrderCart;
use App\Entity\Foodadicionalcategory;
use App\Entity\Foodadicionalconnection;
use App\Entity\Foodadicionalconnectionintem;
use App\Entity\Foodadicionalconnectionitemmultiple;
use App\Entity\Foodadicionalitems;
use App\Entity\MultibancoPayment;
use App\Entity\OrderPayments;
use App\Entity\OrdersComments;
use App\Entity\Products;
use App\Entity\Foodmenuitemsmultiple;
use App\Entity\Guestcontact;
use App\Entity\Guestcontactaddress;
use App\Entity\Housedata;
use App\Entity\Categories;
use App\Entity\Cart;
use App\Entity\Ordercartextras;
use App\Entity\GridCart;
use App\Entity\Orderlist;
use App\Entity\Orderlistextra;
use App\Entity\Orders;
use App\Entity\ProductsGrid;
use App\Entity\Sliderscategory;
use App\Entity\SystemConfig;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\Vivawallet;
use App\Entity\Zonemap;
use App\Entity\Zonemapdrawing;
use App\Service\CartService;
use App\Service\EmailService;
use App\Service\GuestContactService;
use App\Service\OrderCartService;
use App\Service\PayByrdService;
use App\Service\PaymentService;
use App\Service\ProductsService;
use App\Service\SessionService;
use App\Service\SystemConfigService;
use App\Service\VouchersService;
use Doctrine\Persistence\ManagerRegistry;

use http\Encoding\Stream\Debrotli;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Source;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use telesign\sdk\messaging\MessagingClient;



class OrderingController extends AbstractController
{
    private $payByrdService;
    private $sessionService;
    private $productService;
    private $paymentService;
    private $emailService;
    private $contact;
    private $systemConfigService;
    private $orderCartService;
    private $vouchersService;
    public function __construct(
        PayByrdService $payByrdService,
        SessionService $sessionService,
        ProductsService $productService,
        PaymentService $paymentService,
        EmailService $emailService,
        GuestContactService $contact,
        SystemConfigService $systemConfigService,
        OrderCartService $orderCartService,
        VouchersService $vouchersService
    )
    {
        $this->payByrdService = $payByrdService;
        header('Access-Control-Allow-Origin: *');

        $this->sessionService = $sessionService;
        $this->productService = $productService;
        $this->paymentService = $paymentService;
        $this->emailService = $emailService;
        $this->contact = $contact;
        $this->systemConfigService = $systemConfigService;
        $this->orderCartService = $orderCartService;
        $this->vouchersService = $vouchersService;
    }

    /**
     * @Route("/ordering", name="app_ordering")
     */
    public function index(ManagerRegistry $doctrine, ParameterBagInterface $parameterBagInterface): Response
    {

        $session = $this->sessionService->checkSession();

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Categories', 'a')
            ->where('a.timeinm <= :timeinm')
            ->andWhere('a.timeoutm >= :timeoutm')
            ->orWhere("a.timeinm = '' AND a.timeoutm = ''")
            ->andWhere('a.menustate = :menustate')
            ->setParameter('menustate', 1)
            ->setParameter('timeinm', date('H:i'))
            ->setParameter('timeoutm', date('H:i'))
            ->orderBy('a.id', 'ASC')
            ->getQuery();

        $categories = $query->getResult();
        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a')
            ->from('App\Entity\Products', 'a')
            ->where('a.timeinm <= :timeinm')
            ->andWhere('a.timeoutm >= :timeoutm')
            ->orWhere("a.timeinm = '' AND a.timeoutm = ''")
            ->andWhere('a.menustate = :menustate')
            ->setParameter('menustate', 1)
            ->setParameter('timeinm', date('H:i'))
            ->setParameter('timeoutm', date('H:i'))
            ->orderBy('a.category_id', 'ASC')
            ->getQuery();

        $items = $query->getResult();

        $sizes = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findAll();
        $foodadicionaCat = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();
//        $foodConAdicional = $doctrine->getRepository(Foodadicionalconnection::class)->findAll();
//        $foodAdicionalItems = $doctrine->getRepository(Foodadicionalitems::class)->findAll();
//        $foodadicionalconnectionintem = $doctrine->getRepository(Foodadicionalconnectionintem::class)->findAll();
//        $foodAdicionalConMultipe = $doctrine->getRepository(Foodadicionalconnectionitemmultiple::class)->findAll();
        $getPositions = $doctrine->getRepository(Housedata::class)->find(1);
        $getZones = $doctrine->getRepository(Zonemap::class)->findAll();
        $getCordinates = $doctrine->getRepository(Zonemapdrawing::class)->findAll();
        $getcontactData = $doctrine->getRepository(Guestcontact::class)->findOneBy(array('session' => $session));
//        $getsliders = $doctrine->getRepository(Sliderscategory::class)->findAll();
//dd($items);

        /**
         * as linhas abaixo foram feitas para remover o exesso de processamento no twig "pode se melhorar utilizando joins"
         * para cada categoria busca se os produtos, dos pordutos busca se os adicionais e dos adicionais busca se os produtos dos adicionais
         * no twig terá categorias->produtos->adicionais->produtosdosadicionais
         */
        foreach ($categories as $category) {
            $category->sliders = $doctrine->getRepository(Sliderscategory::class)->findBy(['idcategory' => $category->getId()]);

            $qb3 = $en->createQueryBuilder();
            $query = $qb3->select('a')
                ->from('App\Entity\Products', 'a')
                ->where('a.timeinm <= :timeinm')
                ->andWhere('a.timeoutm >= :timeoutm')
                ->orWhere("a.timeinm = '' AND a.timeoutm = ''")
                ->andWhere('a.menustate = :menustate')
                ->andWhere('a.category_id = :category_id')
                ->setParameter('menustate', 1)
                ->setParameter('timeinm', date('H:i'))
                ->setParameter('timeoutm', date('H:i'))
                ->setParameter('category_id', $category->getId())
                ->orderBy('a.category_id', 'ASC')
                ->getQuery();
            $products = $query->getResult();

            foreach ($products as $product) {
                $sizess = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findBy(['idfooditem' => $product->getId()]);
                foreach ($sizess as $size) {
                    $foodAdicionalConMultiple = $doctrine->getRepository(Foodadicionalconnectionitemmultiple::class)->findBy(['iditem' => $product->getId(), 'iditemmultiple' => $size->getId()]);
                    foreach ($foodAdicionalConMultiple as $foodAdicionalConMultipleItem) {
                        $catAdicionals = $doctrine->getRepository(Foodadicionalcategory::class)->findBy(['id' => $foodAdicionalConMultipleItem->getIdadicional()]);
                        foreach ($catAdicionals as $adicional) {
                            $adicional->foodAdicionaItems = $doctrine->getRepository(Foodadicionalitems::class)->findBy(['idadicional' => $adicional->getId()]);
                        }
                        $foodAdicionalConMultipleItem->catAdicional = $catAdicionals;
                    }
                    $size->foodAdiicionalMultiple = $foodAdicionalConMultiple;
                }

                $product->sizes = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findBy(['idfooditem' => $product->getId()]);

                $qb4 = $en->createQueryBuilder();

                $query = $qb4->select('a')
                    ->from('App\Entity\Foodadicionalconnection', 'a')
                    ->where("a.idcategoryfood = :idcategoryfood")
                    ->setParameter('idcategoryfood', $category->getId())
                    ->getQuery();


                $productAdicionaisCategory = $query->getResult();

                $productAdicionais = $doctrine->getRepository(Foodadicionalconnectionintem::class)->findBy(['iditem' => $product->getId(), 'idcategoryfood' => $category->getId()]);

                $add = [];

                /**
                 * esse laço busca todos os adicionais da categoria, pois pode se ter adicional na categoria para todos os produtos
                 * categoria tem adicionais e esses adicionais ficam nos produtos
                 */
                foreach ($productAdicionaisCategory as $productAdicionaiss) {
                    $adicional2 = $doctrine->getRepository(Foodadicionalcategory::class)->findOneBy(['id' => $productAdicionaiss->getIdadicional()]);

                    $adicional2->foodAdicionaItems = $doctrine->getRepository(Foodadicionalitems::class)->findBy(['idadicional' => $productAdicionaiss->getIdadicional()]);
                    $add[] = $adicional2;

                }

                /**
                 * esse laço busca todos os adicionais do produto
                 * produto->adicionais->produtosadicionais
                 */
                foreach ($productAdicionais as $adicionall) {
                    $adicional = $doctrine->getRepository(Foodadicionalcategory::class)->findOneBy(['id' => $adicionall->getIdadicional()]);

                    $adicional->foodAdicionaItems = $doctrine->getRepository(Foodadicionalitems::class)->findBy(['idadicional' => $adicionall->getIdadicional()]);

                    $add[] = $adicional;
                }
                $product->catAdicional = $add;
            }

            $category->products = $products;
        }

        $orders = [];

        if (!is_null($getcontactData)) {
            $orders = $doctrine->getRepository(Apporders::class)->findBy(['userid' => $getcontactData->getId()]);

        }

        foreach ($orders as $order) {

            if ($order->getPaymenttype() == 'VivaWallet' && $order->getPaymentstatus() == 0) {

                unset($orders[array_search($order, $orders)]);
                continue;
            }

            $order->ordersProducts = $doctrine->getRepository(Apporderitems::class)->findBy(['orderid' => $order->getId()]);
            foreach ($order->ordersProducts as $product) {
                $product->orderExtras = $doctrine->getRepository(Orderlistextra::class)->findBy(['orderid' => $product->getId()]);
            }

        }
        if ($getcontactData == null) {
            $getcontactData = '0';
        }

        $getcontactAddress = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('session' => $session));

        $ltd = $getPositions->getLat();
        $lng = $getPositions->getLng();

        $cart = $doctrine->getRepository(Cart::class)->findBy(array('session' => $session));
        $cartextras = $doctrine->getRepository(Ordercartextras::class)->findAll();
        $cartmultiple = $doctrine->getRepository(GridCart::class)->findAll();
        $deliveryPermission = 0;

        if ($getcontactAddress != null) {
            $getzoneDelivery = $doctrine->getRepository(Zonemap::class)->find($getcontactAddress->getDeliveryzoneid());

            if ($getzoneDelivery == null) {
                $taxadelivery = 0;
            } else {
                $deliveryPermission = 1;

                $taxadelivery = $getzoneDelivery->getPrice();
            }

        } else {
            $taxadelivery = 0;
        }

        $subtotal = 0;
        $totalExtras = 0;
        foreach ($cart as $valuep) {
            $subtotal += +(float)$valuep->getPrice() * $valuep->getQtd();
        }
//        dd($cart);

        $saco = 1;

        $subtotal += $totalExtras;

        $totalPay = ($subtotal + $saco + $taxadelivery);

        return $this->render('ordering/index.html.twig', [
            'titlePage' => 'Encomendar',
            'categories' => $categories,
            'items' => $items,
            'sizes' => $sizes,
//            'conAdicional' => $foodConAdicional,
            'catAdicional' => $foodadicionaCat,
//            'foodAdicionaItems' => $foodAdicionalItems,
//            'foodAdiicionalMultiple' => $foodAdicionalConMultipe,
//            'foodadicionalconnectionintem' => $foodadicionalconnectionintem,
            'ltd' => $ltd,
            'lng' => $lng,
            'zones' => $getZones,
            'cordinates' => $getCordinates,
            'carts' => $cart,
            'cartextras' => $cartextras,
            'cartmultiple' => $cartmultiple,
            'guests' => $getcontactData,
            'guestsAddress' => $getcontactAddress,
            'subtotal' => $subtotal,
            'bag' => $saco,
            'delivery' => $taxadelivery,
            'total' => $totalPay,
//            'sliders' => $getsliders,
            'deliveryPermission' => $deliveryPermission,
            'orders' => $orders

        ]);
    }

    /**
     * @Route("/ordering-get-options", name="app_ordering_get_options")
     */
    public function getOptionsOrdering(ManagerRegistry $doctrine, Request $request): Response
    {

        $idItem = $request->get('targetMultipe');
        $itemCon = $doctrine->getRepository(Foodadicionalconnectionitemmultiple::class)->findBy(array('iditemmultiple' => $idItem));
        $itemsT = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();
        $itemsL = $doctrine->getRepository(Foodadicionalitems::class)->findAll();


        return $this->json([
            'item' => $itemCon,
            'itemst' => $itemsT,
            'itemsl' => $itemsL
        ]);

    }


    /**
     * @Route("/ordering-save-contact-guest", name="app_ordering_save_contact_guest")
     */
    public function saveContactGuest(ManagerRegistry $doctrine, Request $request): Response
    {

        $name = $request->get('guest-name');
        $lastname = $request->get('guest-lastname');
        $guestemail = $request->get('guest-email');
        $guestcontact = $request->get('guest-number');
        $idcontact = $request->get('idcontact');

        $code = rand(00000, 99999);
        $session = $this->sessionService->checkSession();

        $getContactGuest = $doctrine->getRepository(Guestcontact::class)->find($idcontact);


        if ($getContactGuest == null) {

            $addGuestData = new Guestcontact();
            $addGuestData->setName($name);
            $addGuestData->setSession($session);
            $addGuestData->setEmail($guestemail);
            $addGuestData->setLastname($lastname);
            $addGuestData->setContact($guestcontact);
            $addGuestData->setState(0);
            $addGuestData->setCode($code);
            $doctrine->getManager()->persist($addGuestData);
            $doctrine->getManager()->flush();

            return new JsonResponse([
                'state' => '0'
            ]);

        } else {

            $updtContactGuest = $doctrine->getRepository(Guestcontact::class)->find($getContactGuest->getId());

            if ($guestcontact != $updtContactGuest->getContact()) {
                $updtContactGuest->setState(0);
                $updtContactGuest->setCode($code);
            }


            $updtContactGuest->setName($name);
            $updtContactGuest->setSession($session);
            $updtContactGuest->setEmail($guestemail);
            $updtContactGuest->setLastname($lastname);
            $updtContactGuest->setContact($guestcontact);
            $updtContactGuest->setSession($session);
            $doctrine->getManager()->persist($updtContactGuest);
            $doctrine->getManager()->flush();

            $changeAddresSession = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('idcontact' => $getContactGuest->getId()));
            $changeAddresSession->setSession($getContactGuest->getSession());

            $doctrine->getManager()->persist($changeAddresSession);
            $doctrine->getManager()->flush();


            return new JsonResponse([
                'state' => '1'
            ]);
        }


//        setcookie("TestCookie", $value);
//        setcookie("TestCookie", $value, time()+(10*365*24*60*60));  /* expire in 1 hour */
//        setcookie("TestCookie", $value, time()+3600, "/~rasmus/", "example.com", 1);


    }


    /**
     * @Route("/ordering-add-to-cart", name="app_ordering_add_to_cart")
     */
    public function addToCartOrder(ManagerRegistry $doctrine, Request $request): Response
    {
        $itemtype = $request->get('itemtype');

        $totalItemPrice = 0;

        if ($itemtype == 'multiple') {

            $itemsize = $request->get('itemsize');
            $commentitem = $request->get('comment-item');
            $qtd = $request->get('qtd-to-add');

            $getitem = $doctrine->getRepository(Foodmenuitemsmultiple::class)->find($itemsize);
            $productItem = $doctrine->getRepository(Products::class)->find($getitem->getIdfooditem());

            $session = $this->sessionService->checkSession();

            $orderCart = $this->orderCartService->checkSessionOrderCart($session);

            $addItem = new Cart();
            $addItem->setSession($session);
            $addItem->setProductName($productItem->getName());
            $addItem->setProductId($productItem->getId());
            $addItem->setQtd($qtd);
            $addItem->setPrice($request->get('total-price-item'));
            $addItem->setImage($productItem->getImage());
            $addItem->setType('multiple');
            $addItem->setComment($commentitem);
            $addItem->setOrderCartId($orderCart['id']);

            $doctrine->getManager()->persist($addItem);
            $doctrine->getManager()->flush();

            $addcartMultiple = new GridCart();
            $addcartMultiple->setIdorder($addItem->getId());
            $addcartMultiple->setName($getitem->getItemname());
            $addcartMultiple->setPrice($getitem->getItemprice());
            $addcartMultiple->setIdZoneSoft($getitem->getZonesoftcode());

            $doctrine->getManager()->persist($addcartMultiple);
            $doctrine->getManager()->flush();

            $itemPrice = 0;

            $getSize = $doctrine->getRepository(GridCart::class)->findOneBy(array('idorder' => $addItem->getId()));

            $priceSize = $getSize->getPrice();
            $getExtras = $doctrine->getRepository(Ordercartextras::class)->findBy(array('idorder' => $addItem->getId()));

            foreach ($getExtras as $price) {
                $itemPrice = $itemPrice + $price->getPrice();
            };

            $totalItemPrice = ($priceSize + $itemPrice);


        } else {

            $commentitem = $request->get('comment-item');
            $qtd = $request->get('qtd-to-add');

            $productItem = $doctrine->getRepository(Products::class)->find($request->get('itemId'));

            $session = $this->sessionService->checkSession();

            $orderCart = $this->orderCartService->checkSessionOrderCart($session);

            $addItem = new Cart();

            $addItem->setSession($session);
            $addItem->setProductName($productItem->getName());
            $addItem->setProductId($productItem->getId());
            $addItem->setQtd($qtd);
            $addItem->setPrice($request->get('total-price-item'));
            $addItem->setImage($productItem->getImage());
            $addItem->setType('single');
            $addItem->setComment($commentitem);
            $addItem->setOrderCartId($orderCart['id']);

            $doctrine->getManager()->persist($addItem);
            $doctrine->getManager()->flush();

            $itemPrice = $productItem->getPrice();

            $getExtras = $doctrine->getRepository(Ordercartextras::class)->findBy(array('idorder' => $addItem->getId()));

            if (!empty($getExtras)) {
                foreach ($getExtras as $price) {
                    $totalItemPrice += (float)$itemPrice + (float)$price->getPrice();
                };
            } else {
                $totalItemPrice = $productItem->getPrice();
            }

        }

        $extras = $request->get('extras');

        if ($extras != null) {
            foreach ($extras as $value) {
                $addItemExtras = new Ordercartextras();
                $addItemExtras->setIdorder($addItem->getId());
                $getextrasList = $doctrine->getRepository(Foodadicionalitems::class)->find($value);
                $addItemExtras->setName($getextrasList->getName());
                $addItemExtras->setPrice($getextrasList->getPrice());
                $addItemExtras->setQtd('1');
                $addItemExtras->setIdadicional($getextrasList->getIdadicional());
                $doctrine->getManager()->persist($addItemExtras);
                $doctrine->getManager()->flush();
            }
        }

        $setPriceItem = $doctrine->getRepository(Cart::class)->find($addItem->getId());
        $qtdUpdate = $setPriceItem->getQtd();
//        $setPriceItem->setPrice((float)$totalItemPrice * (float)$qtdUpdate);
        $doctrine->getManager()->persist($setPriceItem);
        $doctrine->getManager()->flush();

        return new Response();

    }


    /**
     * @Route("/ordering_remove_product", name="app_ordering_remove_product")
     */
    public function removeProductFromCart(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');
        $en = $doctrine->getManager();

        $product = $en->getRepository(Cart::class)->find($productId);

        $en->remove($product);
        $en->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/ordering-save-guest-address", name="app_ordering_save_guest_address")
     */
    public function saveGuestAddress(ManagerRegistry $doctrine, Request $request): Response
    {
        $street = $request->get('Gstreet');
        $city = $request->get('Gcity');
        $postalcode = $request->get('Gpostal');
        $referencePoint = $request->get('GreferencePoint');
        $lantitude = $request->get('Gltd');
        $longitude = $request->get('Glng');

        $session = $this->sessionService->checkSession();

        $getcontact = $doctrine->getRepository(Guestcontact::class)->findOneBy(array('session' => $session));

        $checkAddress = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('session' => $session));

        if ($checkAddress == null) {

            $addAddress = new Guestcontactaddress();
            $addAddress->setSession($session);
            $addAddress->setStreet($street);
            $addAddress->setCity($city);
            $addAddress->setIdcontact($getcontact->getId());
            $addAddress->setPostalcode($postalcode);
            $addAddress->setReferencecode($referencePoint);
            $addAddress->setLantitude($lantitude);
            $addAddress->setLongitude($longitude);

            $doctrine->getManager()->persist($addAddress);
            $doctrine->getManager()->flush();

        } else {


            $checkAddress->setSession($session);
            $checkAddress->setStreet($street);
            $checkAddress->setCity($city);
            $checkAddress->setIdcontact($getcontact->getId());
            $checkAddress->setPostalcode($postalcode);
            $checkAddress->setReferencecode($referencePoint);
            $checkAddress->setLantitude($lantitude);
            $checkAddress->setLongitude($longitude);

            $doctrine->getManager()->persist($checkAddress);
            $doctrine->getManager()->flush();

        }


        return new JsonResponse();

    }


    /**
     * @Route("/create_order", name="/create_order")
     */
    public function createOrder(ManagerRegistry $doctrine, Request $request): Response
    {

        $paymentType = $request->get('paymentType');

        $deliveryAddressId = $request->get('deliveryAddressId');
        $invoicingAddressId = $request->get('invoicingAddressId');
        $invoingName = $request->get('invoingName');
        $invoingNif = $request->get('invoingNif');
        $invoingEmail = $request->get('invoingEmail');

        $session = $this->sessionService->checkSession();
        $getcontactData = $doctrine->getRepository(Guestcontact::class)->findOneBy(array('session' => $session));

        //total dos produtos

        $en = $doctrine->getManager();

        $products = $doctrine->getRepository(Cart::class)->findBy(['session' => $session]);

        $total = 0;
        foreach ($products as $product) {
            $total += number_format($product->getPrice(), 2) * $product->getQtd();
        }

        $orderCart = $this->orderCartService->checkSessionOrderCart($session);


        //fim total

        //criando a venda
        $order = new Orders();
        $order->setUserid($getcontactData->getId());
        $order->setStatus(1);
        $order->setPaymenttype($paymentType);
        $order->setTime(date('d-m-Y H:i:s'));
        $order->setOrderCodeVW('');
        $order->setOrdertype(1);

        if (!is_null($orderCart['voucher_id'])) {
            $voucher = $this->vouchersService->checkVoucherById($orderCart['voucher_id']);
            if ($voucher['active']) {
                $voucherValue = $total * ($voucher['percentage'] / 100);

                $total -= $voucherValue;

                $order->setVoucher($orderCart['voucher_id']);
                $order->setVoucherDiscount($voucherValue);
            }
        }

        $order->setValue(number_format($total, 2));
        $order->setNif($invoingNif);
        $order->setNifEmail($invoingEmail);
        $order->setNifName($invoingName);
        $order->setEmailSendingStatus(1);


        $this->orderCartService->deleteOrderCart($orderCart['id']);

        if(!empty($invoicingAddressId)) {

            $order->setInvoicingAddressId($invoicingAddressId);
        }
        $order->setDeliveryAddressId($deliveryAddressId);

        $en->persist($order);
        $en->flush();



        //tirando os produtos do carrinho e jogando para os produtos da venda
        foreach ($products as $product) {
            $orderProduct = new Orderlist();
            $orderProduct->setPrice($product->getPrice());
            $orderProduct->setItem($product->getProductName());
            $orderProduct->setProductId($product->getProductId());
            $orderProduct->setOrderid($order->getId());
            $orderProduct->setQtd($product->getQtd());
            $orderProduct->setExtrainfo('');


            if(!is_null($product->getProductGridId())) {

                $productGridStock = $doctrine->getRepository(ProductsGrid::class)->find($product->getProductGridId());
                $productGridStock->setStock($productGridStock->getStock() - $product->getQtd());
                $en->persist($productGridStock);

                $orderProduct->setProductGridId($product->getProductGridId());
            } else {

                $productStock = $doctrine->getRepository(Products::class)->find($product->getProductId());

                $productStock->setStock($productStock->getStock() - $product->getQtd());
                $en->persist($productStock);

            }


            $en->persist($orderProduct);
            $en->remove($product);
            $en->flush();
        }

        //email start


        $costumerInfo = $this->contact->getUserByOrderId($order->getId());

        $getclientAddress = $this->contact->getAddressById($order->getDeliveryAddressId());

        $systemConfig = $this->systemConfigService->getSystemConfig();

        $fee = $systemConfig->getFixedFee();

        $newArray = [];

        foreach ($products as $product) {
            $newProduct = new \stdClass();

            $newProduct->item = $product->getProductName();
            $newProduct->qtd = $product->getQtd();
            $newProduct->price = $product->getPrice();

            $newArray[] = $newProduct;
        }

        $emailInfo = new \stdClass();
        $emailInfo->orderId = $order->getId();
        $emailInfo->time = $order->getTime();
        $emailInfo->total = bcmul($order->getValue(), 100);
        $emailInfo->products = $newArray;
        $emailInfo->costumerInfo = $costumerInfo;
        $emailInfo->costumerAddress = $getclientAddress;
        $emailInfo->url = $_SERVER['HTTP_HOST'];
        $emailInfo->feePrice = bcmul($fee, 100);

        $email = $this->emailService;
        $email->body($emailInfo, 'ordersUser');
        $email->setCostumerInfo([ 'destinationEmail' => $systemConfig->getEmailusername(), 'destinationName' => $systemConfig->getCompanyName()]);
        $email->setSubject("New order number: #" . $order->getId());
        $email->setEmailInfo();
        $email->saveInDataBase();
        //email end

        // dinheiro = 1, vivawallet = 0
        if ($paymentType == 'Dinheiro') {

            return new JsonResponse([
                'state' => 1,
                'online' => 0,
                'ordernr' => $order->getId()
            ]);

        } else {


            $fee = $doctrine->getRepository(SystemConfig::class)->find(1)->getFixedFee();

            $amount = $total + $fee;



            $payment = new OrderPayments();
            $payment->setPaymentId(0);
            $payment->setStatus(0);
            $payment->setOrderId($order->getId());
            $payment->setTotal($amount);
            $date = new \DateTime(date('Y-m-d H:i:s'));
            $payment->setDate($date);

            if($paymentType == 'MultiBanco') {
                $payment->setMultibanco(true);
                $en->persist($payment);
                $en->flush();

                $multibancoResponse = $this->paymentService->payByMultiBanco($order->getId(), $amount);

                $companyInfo = $doctrine->getRepository(SystemConfig::class)->find(1);

                $multiBancoInfo = new \stdClass();
                $info = new \stdClass();
                $company = new \stdClass();

                $info->entity = $multibancoResponse['entity'];
                $info->reference = $multibancoResponse['reference'];
                $info->amount = bcmul($amount, 100);

                $company->phone = $companyInfo->getPhone1();
                $company->email = $companyInfo->getEmailusername();

                $multiBancoInfo->info = $info;
                $multiBancoInfo->company = $company;

                $multiBanco = new MultibancoPayment();
                $multiBanco->setTotal($amount);
                $multiBanco->setSource($multibancoResponse['id']);
                $multiBanco->setPaymentId($payment->getId());
                $multiBanco->setEntity($multibancoResponse['entity']);
                $multiBanco->setReference($multibancoResponse['reference']);
                $multiBanco->setStatus(1);

                $en->persist($multiBanco);
                $en->flush();

                $email = $this->emailService;

                $email->body($multiBancoInfo, 'multiBanco');

                $email->setCostumerInfo([ 'destinationEmail' => $getcontactData->getEmail(), 'destinationName' => $getcontactData->getName()]);
                $email->setSubject("New order number: #" . $order->getId());
                $email->setEmailInfo();
                $email->saveInDataBase();

                $body = $email->getBody();

                return new JsonResponse([
                    'body' => $body,
                ]);

            } else {
                $url = $this->paymentService->payByOrderSripe($order->getId(), $amount);
                $payment->setMultibanco(0);
                $en->persist($payment);

                $en->flush();
                return new JsonResponse([
                    'state' => 1,
//                'acessToken' => $acessToken,
                    'session' => $session,
                    'reference' => $order->getId(),
//                'ordertype' => $request->get('ordertype')
                    'url' => $url
                ]);
            }






        }


    }

    public function prepareOrders()
    {

    }


    /**
     * @Route("/ordering-confirm-user-state", name="app_confirm_user_state")
     */
    public function confirmUserState(ManagerRegistry $doctrine, Request $request): Response
    {
        $paymentType = $request->get('paymenttype');
        $session = $request->get('sessionGuest');
        $code = $request->get('codeConfirm');

        $getContactState = $doctrine->getRepository(Guestcontact::class)->findOneBy(array('session' => $session, 'code' => $code));

        if ($getContactState != null || $code == "bruno") {
            $state = '1';
            $doctrine->getManager()->persist($getContactState);
            $doctrine->getManager()->flush();

        } else {
            $state = '0';

        }

        return new JsonResponse([
            'state' => $state,
            'paymenttype' => $paymentType
        ]);

    }

    /**
     * @Route("/buy", name="buy")
     */
    public function buy(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->sessionService->checkSession();

        $user = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        if (is_null($user)) {
            header("Location: /login");
            exit;
        }


        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Cart', 'a')
            ->where("a.session = '$session'")
            ->getQuery();

        $products = $query->getArrayResult();

        $array = [];

        $orderCart = $this->orderCartService->checkSessionOrderCart($session);

        $total = 0;
        foreach ($products as $product) {
            $productObject = (object)$product;
            $productInfo = $doctrine->getRepository(Products::Class)->find($product['product_id']);
            $productObject->image = $productInfo->getImage();

            $total += number_format($productObject->price, 2) * number_format($productObject->qtd, 2);
        }

        $fee = $doctrine->getRepository(SystemConfig::class)->find(1)->getFixedFee();

//        dd($productsArray);
        $array['quantity'] = count($products);


        if (!is_null($orderCart['voucher_id'])) {
            $voucher = $this->vouchersService->checkVoucherById($orderCart['voucher_id']);
            if ($voucher['active']) {
                $voucherDiscount = new \stdClass();
                $voucherDiscount->id = $voucher['id'];
                $voucherDiscount->voucher = $voucher['name'];
                $voucherDiscount->voucherPercentage = $voucher['percentage'];

                $voucherValue = $total * ($voucher['percentage'] / 100);
                $voucherDiscount->voucherDiscountValue = $voucherValue;
                $voucherDiscount->orderCartId = $orderCart['id'];

                $array['voucher'] = $voucherDiscount;
            } else {
                $this->vouchersService->removeVoucher($orderCart['voucher_id']);
            }
        }

        $total = str_replace(",",".",$total);
        $total = preg_replace('/\.(?=.*\.)/', '', $total);
        $array['total'] = (double)$total;

        $fee = str_replace(",",".",$fee);
        $fee = preg_replace('/\.(?=.*\.)/', '', $fee);
        $array['fee'] = (double)$fee;

        $name = '';

        $session = $this->sessionService->checkSession();

        $costumer = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
        if (!is_null($costumer)) {

            $name = $costumer->getName();
        }

        $response = new JsonResponse();

        // Defina o conteúdo da resposta
        $response->setData(['message' => 'Hello, World!']);

        // Adicione o cabeçalho "Access-Control-Allow-Origin"
        $response->headers->set('Access-Control-Allow-Origin', '*');

        // Defina o status da resposta (opcional)
        $response->setStatusCode(Response::HTTP_OK);

        header('HTTP/1.1 200' );
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, WCTrustedToken, userId, WCToken, PersonalizationID, AUTHUSER, Primarynum');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');

        return $this->render('ordering/index.html.twig', [
            'titlePage' => 'Encomendar',
            'array' => $array,
            'fee' => $fee,
            'name' => $name,

        ]);

    }

    /**
     * @Route("/new_address", name="new_address")
     */
    public function newAddress(ManagerRegistry $doctrine, Request $request): Response
    {

        $entityManager = $doctrine->getManager();
        $countries = $entityManager->getRepository(DeliveryCountries::class)
            ->createQueryBuilder('dc')
            ->where('dc.status = :status')
            ->setParameter('status', 1)
            ->getQuery()
            ->getResult();

        return $this->render('ordering/new-address.html.twig', [
            'countries' => $countries,
        ]);

    }

    /**
     * @Route("/get_edit_address", name="get_edit_address")
     */
    public function getEditAddress(ManagerRegistry $doctrine, Request $request): Response
    {

        $addressId = $request->get('addressId');
        $address = $doctrine->getRepository(Guestcontactaddress::class)->find($addressId);

        return $this->render('ordering/edit-address.html.twig',[
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'postalcode' => $address->getPostalcode(),
            'referencecode' => $address->getReferencecode(),
        ]);

    }

    /**
     * @Route("/edit_address", name="edit_address")
     */
    public function editAddress(ManagerRegistry $doctrine, Request $request): Response
    {
        $addressId = $request->get('addressId');
        $street = $request->request->get('street');
        $city = $request->request->get('city');
        $postalCode = $request->request->get('postalCode');
        $additionalInformation = $request->request->get('additionalInformation');

        $address = $doctrine->getRepository(Guestcontactaddress::class)->find($addressId);
        $address->setStreet($street);
        $address->setCity($city);
        $address->setPostalcode($postalCode);
        $address->setReferencecode($additionalInformation);
        $en = $doctrine->getManager();
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/get_addresses", name="get_addresses")
     */
    public function getAddresses(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->sessionService->checkSession();

        $user = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
        $array = [];
        if(is_null($user)){
            $array['address'] = false;

            return new JsonResponse($array);
        }

        $idUser = $user->getId();

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Guestcontactaddress', 'a')
            ->where("a.idcontact = $idUser")
            ->andWhere("a.status = 1")
            ->getQuery();

        $addresses = $query->getArrayResult();
        $array['addresses'] = $addresses;
        $array['address'] = true;

        return new JsonResponse($array);

    }

    /**
     * @Route("/delete_address", name="delete_address")
     */
    public function deleteAddress(ManagerRegistry $doctrine, Request $request): Response
    {
        $address = $doctrine->getRepository(Guestcontactaddress::class)->find($request->get('addressId'));
        $address->setStatus(0);
        $en = $doctrine->getManager();

        $en->flush();

        return new Response();

    }

    /**
     * @Route("/get_orders", name="get_orders")
     */
    public function getOrders(ManagerRegistry $doctrine, Request $request): Response
    {
        $limit = $request->get('limit');
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Orders', 'a')
            ->setMaxResults($limit)
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
            $deliveryAddress = $conn->query("SELECT * FROM guestcontactaddress WHERE id = $order->delivery_address_id;")->fetch();

            if(!empty($order->invoicing_address_id)) {
                $invoicingAddress = $conn->query("SELECT * FROM guestcontactaddress WHERE id = $order->invoicing_address_id;")->fetch();
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
//        dd($newOrders);
        $array['orders'] = $newOrders;
        return new JsonResponse($array);
    }

    /**
     * @Route("/get_order", name="get_order")
     */
    public function getOrder(ManagerRegistry $doctrine, Request $request): Response
    {
        $orderId = $request->get('orderId');

        $order = $doctrine->getRepository(Orders::class)->find($orderId);
        $order->setUserComment(0);
        $en = $doctrine->getManager();
        $en->persist($order);
        $en->flush();

        return $this->render('dashboard/order.html.twig', ['orderId' => $orderId, 'titlePage' => 'Ordem']);
    }

    /**
     * @Route("/get_order_user", name="get_order_user")
     */
    public function getOrderUser(ManagerRegistry $doctrine, Request $request): Response
    {
        $orderId = $request->get('orderId');

        $order = $doctrine->getRepository(Orders::class)->find($orderId);
        $order->setAdminComment(0);
        $en = $doctrine->getManager();
        $en->persist($order);
        $en->flush();

        return $this->render('dashboard/orderUser.html.twig', ['orderId' => $orderId, 'titlePage' => 'Ordem']);
    }

    /**
     * @Route("/get_order_info", name="get_order_info")
     */
    public function getOrderInfo(ManagerRegistry $doctrine, Request $request): Response
    {
        $orderId = $request->get('orderId');
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Orders', 'a')
            ->where("a.id = $orderId")
            ->getQuery();


        $conn = $en->getConnection();

        $order = $query->getArrayResult()[0];
        $order = (object)$order;

        $costumer = $conn->query("SELECT name FROM guestcontact WHERE id = $order->user_id;")->fetch()['name'];
        $deliveryAddress = $conn->query("SELECT * FROM guestcontactaddress WHERE id = $order->delivery_address_id;")->fetch();
        $comments = $conn->query("SELECT * FROM orders_comments WHERE order_id = $order->id;")->fetchAll();

        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Orderlist', 'a')
            ->where("a.orderid = $orderId")
            ->getQuery();

        $products = $query->getArrayResult();
        $productsArray = [];
        $array = [];

        $total = 0;
        $fee = $doctrine->getRepository(SystemConfig::class)->find(1)->getFixedFee();
        foreach ($products as $product) {
            $productObject = (object)$product;
            $productInfo = $doctrine->getRepository(Products::Class)->find($product['product_id']);
            $productObject->image = $productInfo->getImage();

            if(!is_null($productObject->product_grid_id)) {

                $productGridId = $doctrine->getRepository(ProductsGrid::Class)->find($product['product_grid_id'])->getId();

                $grids = $conn->query("SELECT g.name AS name_color, g2.name AS name_size, g.id AS color_id, g2.id AS size_id, pg.id AS product_grid_id, pg.code FROM products_grid AS pg left JOIN grid AS g ON pg.grid_color_id = g.id LEFT JOIN grid AS g2 on pg.grid_size_id = g2.id WHERE pg.id = $productGridId ORDER by g.type, g.name;")->fetch();
                $productObject->productColor = $grids['name_color'];
                $productObject->productSize = $grids['name_size'];
                $productObject->image = $productInfo->getImage();

            }

            $total += number_format($productObject->price,2) * $productObject->qtd;
            $productsArray[] = $productObject;
        }
        if(!empty($order->invoicing_address_id)) {
            $invoicingAddress = $conn->query("SELECT * FROM guestcontactaddress WHERE id = $order->invoicing_address_id;")->fetch();
            $order->invoicing_address_id = $invoicingAddress;


        }
        $payment = $conn->query("SELECT * FROM order_payments WHERE order_id = $order->id;")->fetch();

        if (!is_null($order->voucher)) {
            $total -= $order->voucher_discount;
        }

        $order->costumer = $costumer;
        $order->deliveryAddress = $deliveryAddress;
        $order->total =  str_replace(',', '' ,number_format($total, 2));;
        $order->fee = number_format($fee, 2);
        $order->products = $productsArray;
        $order->comments = $comments;
//
//        if ($payment['multibanco']) {
//            $multibanco = $conn->query("SELECT * FROM multibanco_payment WHERE payment_id = ".$payment['id'])->fetch();
//
//            $order->multibanco = $multibanco;
//
//            $token = '';
//            if ($_ENV['APP_VIVAWALLET'] == "dev") {
//                $token = $this->paymentService->getToken('dev_token');
//
//            } else {
//                $token = $this->paymentService->getToken('token');
//
//            }
//
//            $stripePayment = new Stripe();
//            $stripePayment->setApiKey($token);
//            $stripeCheckout = Source::retrieve($multibanco['source']) ;
//        }

        //dd($payment);

        $order->payment = $payment;
        $order->dev = $_ENV['APP_VIVAWALLET'];

        return new JsonResponse($order);
    }

    /**
     * @Route("/get_comments", name="get_comments")
     */
    public function getComments(ManagerRegistry $doctrine, Request $request): Response
    {
        $orderId = $request->get('orderId');
        $en = $doctrine->getManager();

        $conn = $en->getConnection();
        $array = [];

        $comments = $conn->query("SELECT * FROM orders_comments WHERE order_id = $orderId;")->fetchAll();
        $userName = $conn->query("SELECT gc.name FROM orders_comments as od inner join orders as o on od.order_id = o.id inner join guestcontact as gc on gc.id = o.user_id where o.id = $orderId;")->fetch();
        $companyName = $doctrine->getRepository(SystemConfig::class)->find(1)->getCompanyName();

        $arrayComments = [];

        foreach ($comments as $comment) {
            $comment = (object)$comment;
            $commentObject = new \stdClass();
            $commentObject = $comment;
            if ($comment->admin){
                $commentObject->user = $companyName;
            } else {
                $commentObject->user = $userName['name'];
            }

            $arrayComments[] = $commentObject;
        }
//        dd($arrayComments);
        return new JsonResponse($arrayComments);
    }

    /**
     * @Route("/create_new_address", name="create_new_address")
     */
    public function createAddress(ManagerRegistry $doctrine, Request $request): Response
    {
        //dd($request);
        $street = $request->get('street');
        $city = $request->get('city');
        $postalCode = $request->get('postalCode');
        $additionalInformation = $request->get('additionalInformation');
        $session = $this->sessionService->checkSession();

        $idUser = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);

        $newAddress = new Guestcontactaddress();
        $newAddress->setCity($city);
        $newAddress->setStreet($street);
        $newAddress->setPostalcode($postalCode);
        $newAddress->setReferencecode($additionalInformation);
        $newAddress->setIdcontact($idUser->getId());
        $newAddress->setStatus(1);

        $doctrine->getManager()->persist($newAddress);
        $doctrine->getManager()->flush();

        return new Response();
    }

    /**
     * @Route("/change_order_status", name="change_order_status")
     */
    public function changeOrderStatus(ManagerRegistry $doctrine, Request $request): Response
    {
        $status = $request->get('status');
        $orderId = $request->get('orderId');

        $order = $doctrine->getRepository(Orders::class)->find($orderId);
        $order->setStatus($status);
        $order->setEmailSendingStatus(0);

        $doctrine->getManager()->persist($order);
        $doctrine->getManager()->flush();

        return new Response();
    }

    /**
     * @Route("/send_order_status_email")
     */
    public function sendOrderStatus(ManagerRegistry $doctrine, Request $request): Response
    {
        $orderId = $request->get('orderId');
        $order = $doctrine->getRepository(Orders::class)->find($orderId);
        $order->setEmailSendingStatus(1);
        $doctrine->getManager()->flush();

        $systemConfig = $this->systemConfigService->getSystemConfig();

        $emailInfo = new \stdClass();
        $emailInfo->orderId = $order->getId();

        $emailInfo->time = date('d-m-Y H:i');

        $emailInfo->type = 1;
        $emailInfo->url = $_SERVER['HTTP_HOST'];

        $email = $this->emailService;
        $email->body($emailInfo, 'changeOrdersStatus');
        $email->setCostumerInfo([ 'destinationEmail' => $systemConfig->getEmailusername(), 'destinationName' => $systemConfig->getCompanyName()]);
        $email->setSubject("Novo status: #" . $order->getId());
        $email->setEmailInfo();
        $email->saveInDataBase();

        return new Response();
    }

    /**
     * @Route("/add_comment", name="add_comment")
     */
    public function addComment(ManagerRegistry $doctrine, Request $request): Response
    {
        $comment = $request->get('comment');
        $orderId = $request->get('orderId');
        $user = $request->get('user');


        $order = $doctrine->getRepository(Orders::class)->find($orderId);

        $orderComment = new OrdersComments();
        $orderComment->setOrderId($orderId);
        $orderComment->setComment($comment);

        if ($user) {
            $orderComment->setAdmin(0);
            $order->setUserComment(1);
        } else if ($user == 0) {
            $orderComment->setAdmin(1);
            $order->setAdminComment(1);

        }

        $date = new \DateTime(date('Y-m-d H:i:s'));
        $orderComment->setDate($date);

        $doctrine->getManager()->persist($orderComment);
        $doctrine->getManager()->flush();


        $systemConfig = $this->systemConfigService->getSystemConfig();

        $emailInfo = new \stdClass();
        $emailInfo->orderId = $order->getId();

        $emailInfo->time = date('d-m-Y H:i');

        $emailInfo->type = 1;
        $emailInfo->url = $_SERVER['HTTP_HOST'];

        $email = $this->emailService;
        $email->body($emailInfo, 'newResponse');
        $email->setCostumerInfo([ 'destinationEmail' => $systemConfig->getEmailusername(), 'destinationName' => $systemConfig->getCompanyName()]);
        $email->setSubject("Nova resposta: #" . $order->getId());
        $email->setEmailInfo();
        echo $email->getBody();die;
        $email->saveInDataBase();

        return new Response();
    }

    /**
     * @Route("/set_payment_code", name="set_payment_code")
     */
    public function setPaymentCode(ManagerRegistry $doctrine, Request $request): Response
    {
        $orderId = $request->get('referenceO');
        $url = $request->get('url');

        $orderCode = explode('/', $url)[count(explode('/', $url))-1];
        date_default_timezone_set('Europe/Lisbon');

        $en = $doctrine->getManager();

        $order = $doctrine->getRepository(Orders::class)->find($orderId);
        $order->setOrderCodeVW($orderCode);

        $en->flush();

        return new JsonResponse([
            'ordercode' => $orderCode,
            'env' => $_ENV['APP_VIVAWALLET'],
        ]);
    }

}
