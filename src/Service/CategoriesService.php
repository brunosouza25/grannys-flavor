<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Repository\CategoriesRepository;
use App\Repository\GuestcontactRepository;
use App\Repository\PayByrdConfigRepository;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class CategoriesService
{
    private $categoryRepository;
    public function __construct(CategoriesRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getCategoryAndChildrenProducts($categoryId, $products)
    {
        $products = array_merge($this->categoryRepository->getProducts($categoryId), $products);

        $children = $this->categoryRepository->getChildrenCategories($categoryId);
        foreach ($children as $child) {
            $products = $this->getCategoryAndChildrenProducts($child['id'], $products);
        }
        return $products;
    }

    public function getCategoryAndChildrenIds($categoryId, $ids)
    {
        $ids[] = $categoryId;

        $children = $this->categoryRepository->getChildrenCategories($categoryId);
        foreach ($children as $child) {
            $ids = $this->getCategoryAndChildrenIds($child['id'], $ids);
        }
        return $ids;
    }

    public function getChildCategory($categoryId)
    {
        return $this->categoryRepository->getChildCategory($categoryId);
    }
    public function getCategoriesName($categoryIds)
    {
        $categories = $this->categoryRepository->getCategoriesName($categoryIds);
        $categoriesName = [];
        foreach ($categories as $category){
            $categoriesName[] = $category['name'];
        }
        return implode(', ', $categoriesName);
    }

}