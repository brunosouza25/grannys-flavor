<?php

namespace App\Controller\ADMIN;

use App\Entity\Housedata;
use App\Entity\Zonemap;
use App\Entity\Zonemapdrawing;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class OrderingZonesController extends AbstractController
{
    /**
     * @Route("/ordering-zones", name="app_ordering_zones")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $getPositions = $doctrine->getRepository(Housedata::class)->find(1);
        $getZones = $doctrine->getRepository(Zonemap::class)->findAll();
        $getCordinates = $doctrine->getRepository(Zonemapdrawing::class)->findAll();



        $ltd = $getPositions->getLat();
        $lng = $getPositions->getLng();
        return $this->render('ADMIN/ordering_zones/index.html.twig', [
            'titlePage' => 'Zona de Entrega',
            'ltd' => $ltd,
            'lng' => $lng,
            'zones' => $getZones,
            'cordinates' => $getCordinates
        ]);
    }

    /**
     * @Route("/house-position", name="app_house-position")
     */
    public function housePosition(ManagerRegistry $doctrine): Response
    {

        $getPositions = $doctrine->getRepository(Housedata::class)->find(1);

        $ltd = $getPositions->getLat();
        $lng = $getPositions->getLng();


        return $this->render('ADMIN/ordering_zones/index2.html.twig', [
            'titlePage' => 'Localização',
            'ltd' => $ltd,
            'lng' => $lng
            ]);
    }

    /**
     * @Route("/poly-position", name="app_poly-position")
     */
    public function polyPosition(ManagerRegistry $doctrine): Response
    {

        $getZones = $doctrine->getRepository(Zonemap::class)->findAll();
        $getCordinates = $doctrine->getRepository(Zonemapdrawing::class)->findAll();


        return $this->render('ADMIN/ordering_zones/polygon.html.twig', [
            'titlePage' => 'Localização',
            'zones' => $getZones,
            'cordinates' => $getCordinates
        ]);
    }



    /**
     * @Route("/house-position/save", name="app_house-position_save")
     */
    public function housePositionSave(Request $request, ManagerRegistry $doctrine): Response
    {
        $ltd = $request->get('ltd');
        $lng = $request->get('lng');
        $position = $doctrine->getRepository(Housedata::class)->find(1);
        $position->setLat($ltd);
        $position->setLng($lng);
        $doctrine->getManager()->persist($position);
        $doctrine->getManager()->flush();
        return new Response();
    }


    /**
     * @Route("/new-zones", name="app_new_zones")
     */
    public function newZonesSave(Request $request, ManagerRegistry $doctrine): Response
    {
        $zonename = $request->get('zonename');
        $pricezone = $request->get('pricezone');
        $minPrice = $request->get('pricezone');
        $cordinates = $request->get('vertices');
        $colorzone = $request->get('colorZone');

        $en = $doctrine->getManager();

        $str  = ['(',')',];
        $rplc = ['','',];
        $filterString = str_replace($str,$rplc,$cordinates);
        $arrayCordinate = (explode(',',$filterString));
        $arrayLat = array();
        $arraylong = array();
        foreach ($arrayCordinate as $key => $cordinate){
            if($key % 2 != 0){
                $arraylong[]= array('lng' => $cordinate);
            }else{
                $arrayLat[]= array('lat' => $cordinate);
            }
        }
        $lastarray = array();
        foreach ($arraylong as $key => $longitude){
            foreach ($arrayLat as $keyl => $latitude){
                if($key == $keyl){
                    $lastarray [] = array_merge($latitude, $longitude);
                }
            }
        }
        $getexistedZone = $doctrine->getRepository(Zonemap::class)->findOneBy(array('positions' => 999999));

        if($getexistedZone == null){
            $count = 999999;
        }else{
            $getallcount = $doctrine->getRepository(Zonemap::class)->findAll();
            $counts = count($getallcount);
            $count = (999999-$counts);
        }

        $random = time() . rand(10*45, 100*98);

        $addZone = new Zonemap();
        $addZone->setTitle($zonename);
        $addZone->setPrice($pricezone);
        $addZone->setMinprice($minPrice);
        $addZone->setColor($colorzone);
        $addZone->setReference($random);
        $addZone->setPositions($count);

        $en->persist($addZone);
        $en->flush();

        $lastid = $addZone->getId();

        foreach ($lastarray as $latLang){
            $addCordinate = new Zonemapdrawing();
            $addCordinate->setIdzone($lastid);
            $addCordinate->setCordinate($addZone->getReference());
            $addCordinate->setLat($latLang['lat']);
            $addCordinate->setLng($latLang['lng']);
            $en->persist($addCordinate);
        }

        $en->flush();

        return new Response();
    }

    /**
     * @Route("/edit-exister-zone", name="app_edit_existed_zone")
     */
    public function editExistedZone(Request $request, ManagerRegistry $doctrine): Response
    {

        $zonename = $request->get('zonename');
        $pricezone = $request->get('pricezone');
        $cordinates = $request->get('vertices');
        $zoneID = $request->get('zoneID');
        $minprice = $request->get('minpricetotal');

        $en = $doctrine->getManager();


        if($cordinates != null) {


            $getZone = $doctrine->getRepository(Zonemap::class)->find($zoneID);
            $getZone->setTitle($zonename);
            $getZone->setPrice($pricezone);
            $getZone->setMinprice($minprice);
            $en->persist($getZone);
            $en->flush();

            $getCordinates = $doctrine->getRepository(Zonemapdrawing::class)->findBy(array('idzone' => $zoneID));

            foreach ($getCordinates as $values) {
                $en->remove($values);
            }
            $en->flush();


            $str = ['(', ')',];
            $rplc = ['', '',];
            $filterString = str_replace($str, $rplc, $cordinates);
            $arrayCordinate = (explode(',', $filterString));
            $arrayLat = array();
            $arraylong = array();
            foreach ($arrayCordinate as $key => $cordinate) {
                if ($key % 2 != 0) {
                    $arraylong[] = array('lng' => $cordinate);
                } else {
                    $arrayLat[] = array('lat' => $cordinate);
                }
            }
            $lastarray = array();
            foreach ($arraylong as $key => $longitude) {
                foreach ($arrayLat as $keyl => $latitude) {
                    if ($key == $keyl) {
                        $lastarray [] = array_merge($latitude, $longitude);
                    }
                }
            }


            foreach ($lastarray as $latLang) {
                $addCordinate = new Zonemapdrawing();
                $addCordinate->setIdzone($zoneID);
                $addCordinate->setCordinate($getZone->getReference());
                $addCordinate->setLat($latLang['lat']);
                $addCordinate->setLng($latLang['lng']);
                $en->persist($addCordinate);
            }

            $en->flush();

        }else{

            $getZone = $doctrine->getRepository(Zonemap::class)->find($zoneID);
            $getZone->setTitle($zonename);
            $getZone->setPrice($pricezone);
            $getZone->setMinprice($minprice);
            $en->persist($getZone);
            $en->flush();

        }
        return new Response();
    }


    /**
     * @Route("/delete_zone", name="delete_zone")
     */
    public function deleteZone(ManagerRegistry $doctrine, Request $request)
    {
        $zoneId = $request->get('zoneId');
        $zone = $doctrine->getRepository(Zonemap::class)->find($zoneId);
        $zoneCords = $doctrine->getRepository(Zonemapdrawing::class)->findBy(['idzone' => $zoneId]);
        $en = $doctrine->getManager();

        foreach ($zoneCords as $zoneCord) {
            $en->remove($zoneCord);
            $en->flush();
        }

        $en->remove($zone);
        $en->flush();

        return new Response();
    }

}
