<?php

namespace App\Controller\ADMIN;

use App\Entity\Categories;
use App\Entity\Families;
use App\Entity\Grupoopcoes;
use App\Entity\Products;
use App\Entity\ProductsZoneSoft;
use App\Entity\Subfamilie;
use App\Entity\Zonesoftapi;
use App\Entity\Zonesoftdata;
use App\Service\Invoice\Zonesoft\InvoiceZonesoftService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin", name="admin/") */
class ZonesoftSyncController extends AbstractController
{

    private $zonesoft;
    private $httpClient;

    public function __construct(InvoiceZonesoftService $zonesoft)
    {
        $this->zonesoft = $zonesoft;
        $this->httpClient = HttpClient::create();
    }

    /**
     * @Route("/zonesoft", name="zonesoft_sync")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $doctrine->getManager();
        $zonesoftData = $doctrine->getRepository(Zonesoftdata::class)->find(1);
        $zonesoftApi = $doctrine->getRepository(Zonesoftapi::class)->find(1);

        return $this->render('ADMIN/zonesoft_sync/index.html.twig', [
            'titlePage' => 'Sincronização Zonesoft',
            'zonesoftdata' => $zonesoftData,
            'zonesoftapi' => $zonesoftApi
        ]);
    }


    /**
     * @Route("/zonesoft/sync-data", name="zonesoft_sync_data")
     */
    public function zonesyncData(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();


        $familiesList = $this->zonesoft->getFamilies();


        $filteredfamily = array();
        $subfamilies = array();
        $familiesList = $familiesList->Response->Content->family;
        foreach ($familiesList as $index => $columns) {
            foreach ($columns as $key => $value) {
                if ($key == 'frontoffice' && $value == '1') {
                    $filteredfamily[] = $columns;
                }

            }
        }

        foreach ($filteredfamily as $idexs => $subcoluns) {
            $listsubfalimy = $subcoluns->subfamilies;
            $familycode = strval($subcoluns->codigo);
            foreach ($listsubfalimy as $ida => $vala) {
                foreach ($vala as $keya => $valuea) {
                    if ($keya == 'familia' && $valuea == $familycode) {
                        $subfamilies[] = $vala;
                    }
                }
            }
        }


        $existingCodes = [];
        $families = $en->getRepository(Categories::class)->findBy(['parent_id' => null]);

        // Busca todas as subcategorias existentes
        foreach ($families as $family) {
            $existingCodes[] = $family->getZoneSoftId();
        }

        // Deleta as subcategorias que não existem mais
        foreach ($existingCodes as $familyCode) {
            $found = false;
            foreach ($filteredfamily as $itemExterno) {
                if ($familyCode == $itemExterno->codigo) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $deleteFamily = $en->getRepository(Categories::class)->findOneBy(['zone_soft_id' => $familyCode]);
                $en->remove($deleteFamily);
            }
        }

        // Insere subcategorias que não estão no sistema (categorias raiz)
        foreach ($filteredfamily as $itemExterno) {
            if (!in_array($itemExterno->codigo, $existingCodes)) {
                $addFamily = new Categories();
                $addFamily->setZoneSoftId($itemExterno->codigo);
                $addFamily->setName($itemExterno->descricao);
                $addFamily->setState(1);
                $en->persist($addFamily);
            }
        }
        $en->flush();

        // subfamilies
        $existingCodes = [];
        $repository = $en->getRepository(Categories::class);
        $queryBuilder = $repository->createQueryBuilder('c');

// Adicione a condição para encontrar entidades com parent_id não nulo
        $queryBuilder->where($queryBuilder->expr()->isNotNull('c.parent_id'));

// Execute a consulta
        $families = $queryBuilder->getQuery()->getResult();


        // Busca todas as subcategorias existentes
        foreach ($families as $family) {
            $existingCodes[] = $family->getZoneSoftId();
        }
        // Deleta as subcategorias que não existem mais
//        dump($filteredfamily);
//        dd($existingCodes);
        foreach ($existingCodes as $familyCode) {
            $found = false;
            foreach ($subfamilies as $itemExterno) {
                if ($familyCode == $itemExterno->codigo) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {

                $deleteFamily = $en->getRepository(Categories::class)->findOneBy(['zone_soft_id' => $familyCode]);
                $en->remove($deleteFamily);
            }
        }

        // Insere subcategorias que não estão no sistema (categorias raiz)
        foreach ($subfamilies as $itemExterno) {
            if (!in_array($itemExterno->codigo, $existingCodes)) {
                $addFamily = new Categories();
                $addFamily->setZoneSoftId($itemExterno->codigo);
                $addFamily->setName($itemExterno->descricao);
                $addFamily->setState(1);
                $parentCategoryId = $doctrine->getRepository(Categories::class)->findOneBy(['zone_soft_id' => $itemExterno->familia]);
//                dd
                $addFamily->setParentId($parentCategoryId->getId() );
                $en->persist($addFamily);
            }
        }

        $en->flush();


        $activedProducts = array();
        $productlists = $this->zonesoft->getProducts(3000);
        $productlists = $productlists->Response->Content->product;
        foreach ($productlists as $indexProduct => $columnsProduct) {
            foreach ($columnsProduct as $keysProduct => $valueProduct) {
                if ($keysProduct == 'retalho' && $valueProduct == '1') {
                    $activedProducts[] = $columnsProduct;
                }

            }
        }


        $productlists = $this->zonesoft->getProducts(3000, 250);

        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }

                }
            }

        }

        $productlists = $this->zonesoft->getProducts(3000, 500);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }

                }
            }

        }


        $productlists = $this->zonesoft->getProducts(3000, 750);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }

                }
            }

        }


        $productlists = $this->zonesoft->getProducts(3000, 1000);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }

                }
            }

        }


        $productlists = $this->zonesoft->getProducts(3000, 1250);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }

                }
            }

        }

        $productlists = $this->zonesoft->getProducts(3000, 1500);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }
                }
            }
        }

        $productlists = $this->zonesoft->getProducts(3000, 1750);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }
                }
            }
        }

        $productlists = $this->zonesoft->getProducts(3000, 2000);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }
                }
            }
        }

        $productlists = $this->zonesoft->getProducts(3000, 2250);
        if ($productlists != 'No results found!') {
            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }
                }
            }
        }

        $productlists = $this->zonesoft->getProducts(3000, 2500);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }
                }
            }
        }

        $productlists = $this->zonesoft->getProducts(3000, 2750);
        if ($productlists != 'No results found!') {

            $productlists = $productlists->Response->Content->product;
            foreach ($productlists as $indexProduct => $columnsProduct) {
                foreach ($columnsProduct as $keysProduct => $valueProduct) {
                    if ($keysProduct == 'retalho' && $valueProduct == '1') {
                        $activedProducts[] = $columnsProduct;
                    }

                }
            }

        }

        $updateAll = $request->get('update_all_products');
        $update_prices = $request->get('update_prices');
        //dd($activedProducts);
        if ($updateAll) {
            foreach ($activedProducts as $keysProduct) {
                $product = $doctrine->getRepository(Products::class)->findOneBy(['code' => $keysProduct->codigo]);
                if (is_null($product)) {
                    $addProduct = new Products();
                    $addProduct->setCode($keysProduct->codigo);
                    $addProduct->setName($keysProduct->descricao);
                    $addProduct->setDescription($keysProduct->descricaocurta);
                    $addProduct->setPrice($keysProduct->precovenda);
//            $addProduct->setPrice2($keysProduct->pvp3);
//            $addProduct->setImage($keysProduct->foto);
                    $addProduct->setCategoryId($keysProduct->familia);
                    $addProduct->setSubFamilyId($keysProduct->subfam);
//            $addProduct->setOptionId($keysProduct->grupo);
                    $addProduct->setState(1);
                    $addProduct->setType(0);
                    $addProduct->setStock(999999);
                    $addProduct->setDeleted(0);
                    $en->persist($addProduct);
                    $en->flush();
                } else {
                    $product->setCode($keysProduct->codigo);
                    $product->setName($keysProduct->descricao);
                    $product->setDescription($keysProduct->descricaocurta);
                    $product->setPrice($keysProduct->precovenda);
//            $addProduct->setPrice2($keysProduct->pvp3);
//            $addProduct->setImage($keysProduct->foto);
                    $product->setCategoryId($keysProduct->familia);
                    $product->setSubFamilyId($keysProduct->subfam);
//            $addProduct->setOptionId($keysProduct->grupo);
                    $product->setStock(999999);
                    $en->persist($product);
                    $en->flush();
                }

            }

        }

        if ($update_prices) {
            foreach ($activedProducts as $keysProduct) {
                $product = $doctrine->getRepository(Products::class)->findOneBy(['code' => $keysProduct->codigo]);
                $category = $doctrine->getRepository(Categories::class)->findOneBy(['zone_soft_id' => $keysProduct->familia]);

                if (is_null($product)) {
                    //dd($product);

                    $addProduct = new Products();
                    $addProduct->setCode($keysProduct->codigo);
                    $addProduct->setName($keysProduct->descricao);
                    $addProduct->setDescription($keysProduct->descricaocurta);
                    $addProduct->setPrice($keysProduct->precovenda);
//            $addProduct->setPrice2($keysProduct->pvp3);
//            $addProduct->setImage($keysProduct->foto);
                    $addProduct->setCategoryId($category->getId());
                    $addProduct->setSubFamilyId($keysProduct->subfam);
//            $addProduct->setOptionId($keysProduct->grupo);
                    $addProduct->setState(1);
                    $addProduct->setType(0);
                    $addProduct->setStock(999999);
                    $addProduct->setDeleted(0);
                    $en->persist($addProduct);
                    $en->flush();
                } else {
                    //dd($product);
                    $product->setCode($keysProduct->codigo);
                    $product->setName($keysProduct->descricao);
                    $product->setPrice($keysProduct->precovenda);
//            $addProduct->setPrice2($keysProduct->pvp3);
//            $addProduct->setImage($keysProduct->foto);
                    $product->setCategoryId($category->getId());
                    $product->setSubFamilyId($keysProduct->subfam);
//            $addProduct->setOptionId($keysProduct->grupo);
                    $product->setStock(999999);
                    $en->persist($product);
                    $en->flush();
                }

            }
        }


        $deleteoptionsGroup = $doctrine->getRepository(Grupoopcoes::class)->findAll();

        foreach ($deleteoptionsGroup as $entitygroupOptions) {
            $en->remove($entitygroupOptions);
        }

        $en->flush();

        $optionsGroups = $this->zonesoft->getOptions();
        //dd($optionsGroups);

        if ($optionsGroups == 'No results found!'){
            return new Response();
        }
        $optionsGroups = $optionsGroups->Response->Content->option;


        foreach ($optionsGroups as $keysGroup) {
            $addGroup = new Grupoopcoes();
            $addGroup->setCodigo($keysGroup->codigo);
            $addGroup->setDescricao($keysGroup->descricao);
            $addGroup->setGrupo($keysGroup->grupo);
            $en->persist($addGroup);
            $en->flush();
        }

        return new Response();
    }


    /**
     * @Route("/zonesoft/data-save", name="zonesoft_data_save")
     */
    public function zonesoftdataSave(Request $request, ManagerRegistry $doctrine): Response
    {


        $nif = $request->get('nif');
        $username = $request->get('username');
        $password = $request->get('password');
        $loja = $request->get('loja');


        $en = $doctrine->getManager();

        $zonesoftData = $doctrine->getRepository(Zonesoftdata::class)->find(1);


        $zonesoftData->setNif($nif);
        $zonesoftData->setUsername($username);
        $zonesoftData->setPassword($password);
        $zonesoftData->setStore($loja);

        $en->persist($zonesoftData);
        $en->flush();

//        $zonesoftApi = $doctrine->getRepository(Zonesoftapi::class)->find(1);

        return new Response();
    }

    /**
     * @Route("/zonesoft/data-save-api", name="zonesoft_data_save_api")
     */
    public function zonesoftdataSaveApi(Request $request, ManagerRegistry $doctrine): Response
    {


        $app_secret = $request->get('app_secret');
        $app_key = $request->get('app_key');
        $store_id = $request->get('store_id');


        $en = $doctrine->getManager();

        $zonesoftApi = $doctrine->getRepository(Zonesoftapi::class)->find(1);


        $zonesoftApi->setAppsecret($app_secret);
        $zonesoftApi->setAppkey($app_key);
        $zonesoftApi->setStoreid($store_id);

        $en->persist($zonesoftApi);
        $en->flush();


        return new Response();
    }
}


