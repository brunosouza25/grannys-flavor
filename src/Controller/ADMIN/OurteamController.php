<?php

namespace App\Controller\ADMIN;

use App\Entity\Ourteam;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class OurteamController extends AbstractController
{
    /**
     * @Route("/ourteam", name="app_ourteam")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $members = $doctrine->getRepository(Ourteam::class)->findAll();

        return $this->render('ADMIN/ourteam/index.html.twig', [
            'titlePage' => 'A Equipa',
            'members' => $members
        ]);
    }

    /**
     * @Route("/ourteam/add-new-member", name="app_ourteam_add_new_member")
     */
    public function addNewMember(Request $request, ManagerRegistry $doctrine): Response
    {

        $name = $request->get('name-member');
        $function = $request->get('function-member');
        $text = $request->get('desc-member');

        if(isset($_FILES['image-member']['name'])) {
            $filename = $_FILES['image-member']['name'];

            $location = "uploads/team/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $path = "uploads/team/".time(). "-" .$_FILES['image-member']['name'];
                copy($_FILES['image-member']['tmp_name'], $path);

                $en = $doctrine->getManager();

                $member = new Ourteam();
                $member->setName($name);
                $member->setPosition($function);
                $member->setDescription($text);
                $member->setImage($path);
                $en->persist($member);
                $en->flush();
            }
        }

        return new Response();
    }


    /**
     * @Route("/ourteam/edit-current-member", name="app_ourteam_edit_current_member")
     */
    public function EditCurrentMember(ManagerRegistry $doctrine, Request $request): Response
    {

        $memberId = $request->get('memberId');

        $member = $doctrine->getRepository(Ourteam::class)->find($memberId);

       return new JsonResponse([
           'name' => $member->getName(),
           'position' => $member->getPosition(),
           'text' => $member->getDescription(),
           'image' => $member->getImage()
       ]);
    }

    /**
     * @Route("/ourteam/delete-current-member", name="app_ourteam_delete_current_member")
     */
    public function deleteCurrentMember(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $memberId = $request->get('memberId');
        $member = $doctrine->getRepository(Ourteam::class)->find($memberId);
        $en->remove($member);
        $en->flush();
       return new Response();
    }

    /**
     * @Route("/ourteam/edit-current-member-save", name="app_ourteam_edit_current_member_save")
     */
    public function EditCurrentMemberSave(ManagerRegistry $doctrine, Request $request): Response
    {

        $memberId = $request->get('teamID');
        $fotoExiste = $request->get('teamID');
        $name = $request->get('name-member');
        $function = $request->get('function-member');
        $text = $request->get('desc-member');

        $en = $doctrine->getManager();
        $getTeam = $doctrine->getRepository(Ourteam::class)->find($memberId);

        $getTeam->setName($name);
        $getTeam->setPosition($function);
        $getTeam->setDescription($text);

        if(isset($_FILES['image-member']['name'])) {
            $filename = $_FILES['image-member']['name'];

            $location = "uploads/team/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $path = "uploads/team/".time(). "-" .$_FILES['image-member']['name'];
                copy($_FILES['image-member']['tmp_name'], $path);

                $en = $doctrine->getManager();

                $getTeam->setImage($path);
            }
        }


        $en->persist($getTeam);
        $en->flush();

        return new Response();
    }
}
