<?php

namespace App\Controller\APP;

use App\Entity\Categories;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

/** @Route("/app", name="app") */
class AppDashboardController extends AbstractController
{


    /**
     * @Route("/app", name="/app_area")
     */
    public function adminArea(): Response
    {

        return $this->redirectToRoute("admin/app_login");
    }

    /**
     * @Route("/dashboard", name="/app_dashboard")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        if ($this->getUser()) {
            $checkAddress = $doctrine->getRepository(UserAddress::class)->findOneBy(array('userid' => $this->getUser()->getId()));
            if($checkAddress == null){
                $address = 0;
            }else{
                $address = 1;
            }

            $userphonestate = $this->getUser()->getPhoneState();

            if($userphonestate == null){
                $phonestate = 0;
            }else{
                $phonestate = 1;
            }


            $getFoodCategoires = $doctrine->getRepository(Categories::class)->findAll();





            return $this->render('APP/app_dashboard/index.html.twig', [
                'titlePage' => 'App Dashboard',
                'address' => $address,
                'phonestate' => $phonestate,
                'categories' => $getFoodCategoires
            ]);

        }

    }


    /**
     * @Route("/login", name="/app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {



        if ($this->getUser()) {
            return $this->redirectToRoute('app/app_dashboard');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('APP/app_dashboard/login/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);


    }

    /**
     * @Route("/register_admin", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {

        return $this->render('APP/app_dashboard/login/register.html.twig', [
        ]);
    }

    /**
     * @Route("/register/add-user", name="app_register_new_user")
     */
    public function registerAddUser(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {

        $name = $request->get('full-name');
        $email = $request->get('user-email');
        $phone = $request->get('phonenumbe');
        $password = $request->get('password');

        $checkuser = $doctrine->getRepository(User::class)->findOneBy(array('email' => $email));

        if($checkuser == null){
            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $user->setRoles(array('ROLE_CUSTUMER'));
            $entityManager->persist($user);
            $entityManager->flush();
            $state = '0';
        }else{
            $state = '1';
        }

      return new JsonResponse([
          'state' => $state
      ]);
    }






}
