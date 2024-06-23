<?php

namespace App\Controller\APP;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/app", name="app") */
class AppmyaccountController extends AbstractController
{
    /**
     * @Route("/appmyaccount", name="app_appmyaccount")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $getuserdata = $doctrine->getRepository(User::class)->find($this->getUser()->getID());

        return $this->render('APP/appmyaccount/index.html.twig', [
            'titlePage' => 'Minha Conta',
            'userdata' => $getuserdata
        ]);
    }
    /**
     * @Route("/appmyaccount/update", name="app_appmyaccount_update")
     */
    public function accountUpdate(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $name = $request->get('username');
        $email = $request->get('useremail');
        $phone = $request->get('userphone');
        $password = $request->get('password');

        $getuserdata = $doctrine->getRepository(User::class)->find($this->getUser()->getID());

        $getuserdata->setName($name);
        $getuserdata->setEmail($email);
        $getuserdata->setPhone($phone);

        if($password != '') {
            $getuserdata->setPassword(
                $userPasswordHasher->hashPassword(
                    $getuserdata,
                    $password
                )
            );
        }

        $doctrine->getManager()->persist($getuserdata);
        $doctrine->getManager()->flush();

        return new Response();
    }
}
