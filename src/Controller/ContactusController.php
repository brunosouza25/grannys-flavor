<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\MessagesContact;
use App\Service\GuestContactService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactusController extends AbstractController
{
    private $guestContactService;
    public function __construct(GuestContactService $guestContactService)
    {
        $this->guestContactService = $guestContactService;
    }

    /**
     * @Route("/contact", name="app_contactus")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $banner = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);

        return $this->render('contactus/index.html.twig', [
            'titlePage' => 'Contactos',
            'banner' => $banner,
        ]);
    }

    /**
     * @Route("/page_contact", name="page_contact")
     */
    public function contact(): Response
    {
        return $this->render('contactus/index.html.twig');
    }

    /**
     * @Route("/save_message", name="save_message")
     */
    public function saveMessage(ManagerRegistry $doctrine, Request $request ): Response
    {

        $name = $request->get('name');
        $email = $request->get('email');
        $comments = $request->get('comments');

        $message = new MessagesContact();
        $message->setName($name);
        $message->setEmail($email);
        $message->setComment($comments);

        $en = $doctrine->getManager();
        $en->persist($message);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/get_guest_contact", name="get_guest_contact")
     */
    public function guetGuestContact(ManagerRegistry $doctrine): JsonResponse
    {
        $en = $doctrine->getManager();
        $conn = $en->getConnection();

        $user = $this->guestContactService->getUserSession();

        $query = "select gc.name, gc.contact, gc.email, gca.id, gca.street from guestcontact gc, guestcontactaddress gca WHERE gc.id = gca.idcontact AND gca.status = 1 AND idcontact = " . $user->getId();

        $address = $conn->query($query)->fetchAll();

        return new JsonResponse($address);
    }
}
