<?php

namespace App\Service;

use App\Repository\CategoriesRepository;
use App\Repository\GridRepository;
use App\Repository\ProductsRepository;
use App\Service\Invoice\Zonesoft\InvoiceZonesoftService;
use Doctrine\Persistence\ManagerRegistry;


class ProductsService
{
    private $gridRepository;
    private $productRepository;
    private $zoneSoftService;
    private $sessionService;
    public function __construct(GridRepository $gridRepository, ProductsRepository  $productsRepository, InvoiceZonesoftService $invoiceZonesoftService, SessionService $sessionService)
    {
        $this->gridRepository = $gridRepository;
        $this->productRepository = $productsRepository;
        $this->zoneSoftService = $invoiceZonesoftService;
        $this->sessionService = $sessionService;
    }

    public function getColorsGridProduct($productId)
    {
        return $this->gridRepository->getColorsProduct($productId);
    }
    public function productGridStockSum($productId)
    {
        return $this->gridRepository->productGridStockSum($productId);
    }

    public function changeProductGridStatus($productGridId)
    {
        $productGrid = $this->productRepository->getProductGridStatus($productGridId);

        $this->productRepository->changeProductGridStatus(!$productGrid->isStatus(), $productGridId);
    }

    public function syncProductsStock($productId = null){

        $idsProducts =  $this->productRepository->getProductsId($productId);
        $this->productRepository->updateStockStatus(0, 0, $productId);

        $productsStock = $this->zoneSoftService->getProductStockIds(explode(',',  $idsProducts));

        $productsStockArray = $productsStock['Response']['Content']['productstock'] ;

        if(!is_null($productsStockArray)){
            foreach ($productsStockArray as $product){
                $productId = $product['produto'];
                $productStock = $product['stock'];
                $this->productRepository->syncStock($productId, $productStock);

//                if(!is_null($productId)){
//                    $this->syncProductGridStockIds($productId);
//                }
            }

            return 1;
        } else {
            return 0;
        }
    }

    public function syncProductGridStockIds($productId = null)
    {
        $idsProducts =  $this->productRepository->getProductsGridId($productId);

        $this->productRepository->updateStockStatus(0, 1, $productId);

        $productsStock = $this->zoneSoftService->getProductStockIds(explode(',',  $idsProducts));
        $productsStockArray = $productsStock['Response']['Content']['productstock'] ;

        if(!is_null($productsStockArray)){
            foreach ($productsStockArray as $product){

                $productId = $product['produto'];
                $productStock = $product['stock'];
                $this->productRepository->syncStockGrid($productId, $productStock);
            }
            return 1;
        } else {
            return 0;
        }

    }

    public function checkGridStock($colorId, $sizeId, $productId)
    {
        $stock = $this->productRepository->getProductsGridStock($colorId, $sizeId, $productId) ? $this->productRepository->getProductsGridStock($colorId, $sizeId, $productId) : 0 ;
        return $stock;
    }

    public function getProductById($productId)
    {
        return $this->productRepository->find($productId);
    }

    public function getQuantitydCart($productId, ManagerRegistry $doctrine) : int
    {
        $sessionUser = $this->sessionService->checkSession();
        $conn = $doctrine->getConnection();

        $stmt = $conn->executeQuery("SELECT SUM(qtd) as qtd FROM `cart` WHERE product_id = :productId and session = :sessionUser", [
            'productId' => $productId,
            'sessionUser' => $sessionUser
        ]);

        $productCartQtdSum = $stmt->fetch();
        if($productCartQtdSum['qtd'] == null){
            $productCartQtdSum['qtd'] = 0;
        }
        return $productCartQtdSum['qtd'];
    }
    public function getQuantityStock($productId, ManagerRegistry $doctrine) : int
    {
        $conn = $doctrine->getConnection();
        $stmt = $conn->executeQuery("SELECT stock as quantityStock FROM `products` WHERE id = :productId", [
            'productId' => $productId,
        ]);

        $productCartQtdSum = $stmt->fetch();
        if($productCartQtdSum['quantityStock'] == null){
            $productCartQtdSum['quantityStock'] = 0;
        }
        return $productCartQtdSum['quantityStock'];
    }
 }