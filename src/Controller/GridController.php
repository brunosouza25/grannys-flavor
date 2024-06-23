<?php

namespace App\Controller;

use App\Entity\Grid;
use App\Entity\Products;
use App\Entity\ProductsGrid;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GridController extends AbstractController
{
    /**
     * @Route("/admin/grids", name="admin/grids")
     */
    public function index(): Response
    {
        return $this->render('grid/index.html.twig', [
            'controller_name' => 'GridController',
        ]);
    }


    /**
     * @Route("/admin/get_grids", name="admin/get_grids")
     */
    public function getGrids(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Grid', 'a')
            ->where('a.status = 1')
            ->orderBy('a.position_sort', 'DESC')
            ->getQuery();

        $grids = $query->getArrayResult();
        return new JsonResponse($grids);

    }

    /**
     * @Route("/admin/get_colors", name="admin/get_colors")
     */
    public function getColors(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Grid', 'a')
            ->where('a.status = 1')
            ->andWhere('a.type = 1')
            ->orderBy('a.position_sort', 'DESC')
            ->getQuery();

        $grids = $query->getArrayResult();
        return new JsonResponse($grids);

    }

    /**
     * @Route("/admin/get_sizes", name="admin/get_sizes")
     */
    public function getSizes(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Grid', 'a')
            ->where('a.status = 1')
            ->andWhere('a.type = 0')
            ->orderBy('a.position_sort', 'DESC')
            ->getQuery();

        $grids = $query->getArrayResult();
        return new JsonResponse($grids);

    }

    /**
     * @Route("/admin/new_grid", name="admin/new_grid")
     */
    public function newGrid(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $grid = new Grid();
        $grid->setName($request->get('itemName'));
        $grid->setType($request->get('itemType'));
        $grid->setPositionSort($request->get('itemPosition'));
        $grid->setStatus(true);

        $en->persist($grid);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/new_product_grid_code", name="admin/new_product_grid_code")
     */
    public function newGridCode(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $grid = new Grid();
        $grid->setName($request->get('itemName'));
        $grid->setType($request->get('itemType'));
        $grid->setStatus(true);

        $en->persist($grid);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/grid_codes", name="admin/grid_codes")
     */
    public function gridCodes(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');

        return $this->render('grid/gridCodes.html.twig', [
            'controller_name' => 'GridController',
            'productId' => $productId
        ]);
    }

    /**
     * @Route("/admin/get_grid_codes", name="admin/get_grid_codes")
     */
    public function getGridCodes(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $conn = $en->getConnection();
        $productId = $request->get('productId');

        $grid = $conn->query("SELECT pg.status as status, pg.stock as stock,g.name AS name_color, g2.name AS name_size, g.id AS color_id, g2.id AS size_id, pg.id AS product_grid_id, pg.code 
                              FROM products_grid AS pg left JOIN grid AS g ON pg.grid_color_id = g.id LEFT JOIN grid AS g2 on pg.grid_size_id = g2.id 
                              WHERE pg.product_id = $productId ORDER by g.type, g.name;")->fetchAll();

        return new JsonResponse($grid);

    }

    /**
     * @Route("/admin/save_grid_codes", name="admin/save_grid_codes")
     */
    public function saveGridCodes(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        foreach ($request->get('codes2') as $item) {
            $gridCode = $doctrine->getRepository(ProductsGrid::class)->find($item['id']);
            $gridCode->setCode($item['code']);
            $gridCode->setStock((int)$item['stock']);

            $en->flush();

        }


        return new Response();

    }

    /**
     * @Route("/admin/new_product_grid", name="admin/new_product_grid")
     */
    public function newProductGrid(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $productId = $request->get('productId');
        $gridId = $request->get('gridId');

        $conn = $en->getConnection();

        $gridExiste = $conn->query("select * from products_grid where product_id = $productId and (grid_color_id = $gridId or grid_size_id = $gridId);")->fetchAll();


        if (!empty($gridExiste)) {
            return new JsonResponse(['code' => 0]);
        }

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Grid', 'a')
            ->where("a.id = $gridId")
            ->andWhere('a.status = 1')
            ->getQuery();

        $gridInformation = (object)$query->getArrayResult()[0];


        $productGridsColorsExists = $conn->query("SELECT g.id AS grid_id, g.name AS name, pd.id as product_grid_id FROM products_grid as pd
                               INNER JOIN grid AS g
                               ON pd.grid_color_id = g.id
                               WHERE pd.product_id = $productId and g.status = 1 and g.type = 1 group by g.id; ")->fetchAll();

        $productGridsSizeExists = $conn->query("SELECT g.id AS grid_id, g.name AS name, pd.id as product_grid_id FROM products_grid as pd
                               INNER JOIN grid AS g
                               ON pd.grid_size_id = g.id
                               WHERE pd.product_id = $productId and g.status = 1 and g.type = 0 group by g.id")->fetchAll();


//        $productGridsSizeExists = $doctrine->getRepository(ProductsGrid::class)->findBy(['product_id' => $productId, 'grid_size_id']);
//        $productGridsColorExists = $doctrine->getRepository(ProductsGrid::class)->findBy(['product_id' => $productId]);


        //type 1 = inserindo cor
        /**
         * esse bloco abaixo verifica se existe grids já vinculados para pegar as mesmas e replicar nos demais,
         * por exemplo: se o produto tiver 5 cores, ele pega cada uma e adiciona o tamanho que tiver sendo adicionado
         */

        /**
         * Verificação se o tipo é cor ou tamanho que está sendo adicionado "if ($gridInformation->type ==  1)"
         */
        if ($gridInformation->type == 1) {
            /**
             * Verificação se o array de ProductsGrid "if (!empty($productGridsSizeExists))" não está vazio para
             * verificar se aquele produto não tem grid
             */
            if (!empty($productGridsSizeExists)) {
                /**
                 * foreach para pegar todos os tamanhos adicionados e vincular a nova a cor a todos eles
                 */
                foreach ($productGridsSizeExists as $productGridSizeExists) {

                    $grid = $doctrine->getRepository(ProductsGrid::class)->find(((object)$productGridSizeExists)->product_grid_id);
                    /**
                     * se for nulo, quer dizer que já foi adicionado um tamanho e nenhuma cor antes, logo ele só seta a cor nos tamanhos que já existem mas sem cores vinculadas
                     */
                    if (is_null($grid->getGridColorId())) {
                        $grid->setGridColorId($gridId);
                        $en->persist($grid);
                        $en->flush();
                    } else {
                        /**
                         * senão, ele apenas cria mais um produto grid com a vinculação de ambos
                         */
                        $newGrid = new ProductsGrid();
                        $newGrid->setProductId($productId);
                        $newGrid->setGridColorId($gridId);
                        $newGrid->setGridSizeId(((object)$productGridSizeExists)->grid_id);
                        $newGrid->setStatus(1);
                        $newGrid->setStock(0);
                        $newGrid->setCode('');

                        $en->persist($newGrid);
                        $en->flush();
                    }


                }
            } else {
                /**
                 * senão, não existe tamanhos já vinculados no produto e ele somente cria um sem um tamanho
                 */
                $grid = new ProductsGrid();
                $grid->setProductId($productId);
                $grid->setGridColorId($gridId);
                $grid->setStatus(1);
                $grid->setStock(0);
                $grid->setCode('');

                $en->persist($grid);
                $en->flush();
            }
        } else {
            /**
             * mesma funcionalidade mas para quando se adiciona tamanhos ao invés de cores
             */
            if (!empty($productGridsColorsExists)) {
                foreach ($productGridsColorsExists as $productGridColorsExists) {

                    $grid = $doctrine->getRepository(ProductsGrid::class)->find(((object)$productGridColorsExists)->product_grid_id);
                    if (is_null($grid->getGridSizeId())) {
                        $grid->setGridSizeId($gridId);
                        $en->persist($grid);
                        $en->flush();
                    } else {
                        $newGrid = new ProductsGrid();
                        $newGrid->setProductId($productId);
                        $newGrid->setGridSizeId($gridId);
                        $newGrid->setGridColorId(((object)$productGridColorsExists)->grid_id);
                        $newGrid->setStatus(1);
                        $newGrid->setStock(0);
                        $newGrid->setCode('');

                        $en->persist($newGrid);
                        $en->flush();
                    }


                }
            } else {
                $grid = new ProductsGrid();
                $grid->setProductId($productId);
                $grid->setGridSizeId($gridId);
                $grid->setStatus(1);
                $grid->setStock(0);
                $grid->setCode('');

                $en->persist($grid);
                $en->flush();
            }
        }


        $product = $doctrine->getRepository(Products::class)->find($productId);


        $product->setGrid(true);

        $en->flush();


        return new JsonResponse(['code' => 1]);
    }


    /**
     * @Route("/admin/delete_grid_product", name="admin/delete_grid_product")
     */
    public function deleteGridProduct(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $productId = $request->get('productId');
        $gridId = $request->get('gridId');
        $gridType = $request->get('gridType');


        $grid = $doctrine->getRepository(Grid::class)->find($gridId);

        if (!$grid->isType()) {
            $productGrids = $en->getRepository(ProductsGrid::class)->findBy(['product_id' => $productId, 'grid_size_id' => $gridId]);

        } else {
            $productGrids = $en->getRepository(ProductsGrid::class)->findBy(['product_id' => $productId, 'grid_color_id' => $gridId]);
        }


        foreach ($productGrids as $productGrid) {

            $en->remove($productGrid);
        }

        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/get_edit_grid", name="admin/get_edit_grid")
     */
    public function getEditGrid(ManagerRegistry $doctrine, Request $request): Response
    {
        $gridId = $request->get('itemId');
        $gridName = $request->get('itemName');
        $gridType = $request->get('itemType');
        $type = $request->get('type');

        $grid = $doctrine->getRepository(Grid::class)->find($gridId);

        return $this->render('grid/gridEdit.twig', [
            'controller_name' => 'GridController',
            'itemId' => $gridId,
            'itemName' => $gridName,
            'itemType' => $gridType,
            'type' => $type,
            'grid' => $grid
        ]);
    }

    /**
     * @Route("/admin/edit_grid", name="admin/edit_grid")
     */
    public function editGrid(ManagerRegistry $doctrine, Request $request): Response
    {
        $gridId = $request->get('itemId');
        $gridName = $request->get('itemName');
        $gridType = $request->get('itemType');
        $positionSort = $request->get('itemPosition');

        $images = $request->files->get('image');

        $em = $doctrine->getManager();

        $grid = $doctrine->getRepository(Grid::class)->find($gridId);
        $grid->setName($gridName);
        $grid->setType($gridType);
        $grid->setPositionSort($positionSort);

        if($gridType) {
            if (isset($images)) {


                $filename = $images->getClientOriginalName();
                $location = "uploads/" . $filename;

                $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
                $imageFileType = strtolower($imageFileType);

                $valid_extensions = array("jpg", "jpeg", "png", "webp", "jfif");
//                dd($images);
                if (in_array(strtolower($imageFileType), $valid_extensions)) {
                    $src = $images->getPathname();
                    $dest = "uploads/" . explode('.', $filename)[0] . "-" . time() . '.webp';
                    $destjpeg = "uploads/" . time() . "-" . $filename;

                    $quality = 40;

                    if (strtolower($imageFileType) != "webp") {
                        $info = getimagesize($src);

                        if ($info['mime'] == 'image/jpeg') {
                            $image = imagecreatefromjpeg($src);
                        } elseif ($info['mime'] == 'image/gif') {
                            $image = imagecreatefromgif($src);
                        } elseif ($info['mime'] == 'image/png') {
                            $image = imagecreatefrompng($src);
                        } else {
                            die('Unknown image file format');
                        }

                        // Compress and save file to jpg
                        imagejpeg($image, $destjpeg, $quality);

                        // Create and save
                        $img = imagecreatefromjpeg($destjpeg);
                        imagepalettetotruecolor($img);
                        imagealphablending($img, true);
                        imagesavealpha($img, true);
                        imagewebp($img, $dest, 10);
                        imagedestroy($img);

                        unlink($destjpeg);
                    } else {
                        $img = imagecreatefromwebp($src);
                        imagewebp($img, $dest, 10);
                    }

                    $images->move('uploads/', $dest);

                    $grid->setImage($dest);
                }

            }
        }
        $em->persist($grid);
        $em->flush();

        return new Response();
    }


    /**
     * @Route("/admin/delete_grid", name="admin/delete_grid")
     */
    public function deleteGrid(ManagerRegistry $doctrine, Request $request): Response
    {
        $gridId = $request->get('itemId');

        $en = $doctrine->getManager();
        $grid = $doctrine->getRepository(Grid::class)->find($gridId);

        $productsGrid = $doctrine->getRepository(ProductsGrid::class)->findBy(['grid_id' => $gridId]);

        foreach ($productsGrid as $productGrid) {
            $en->remove($productGrid);
        }

        $grid->setStatus(0);
        $en->persist($grid);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_grid", name="admin/get_grid")
     */
    public function getGrid(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Grid', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('gridId'))
            ->getQuery();

        $grid = $query->getArrayResult();

        return new JsonResponse($grid[0]);

    }
}
