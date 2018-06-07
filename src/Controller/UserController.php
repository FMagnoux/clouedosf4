<?php

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\User;


class UserController extends Controller
{

    /**
     * @Route("/user/login", name="app_login")
     */
    public function login(Request $request)
    {
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('email',TextType::class)
            ->add('password',PasswordType::class)
            ->add('login', SubmitType::class, array('label' => 'Connexion'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $userDb = $this->getDoctrine()
                ->getRepository(User::class)
                ->findBy(array('email' => $user['email'], 'password' => $user['password']));
            $userDb = $userDb[0];

            if (!$userDb) {
                return $this->render('user/login.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => "Les identifiants sont incorrect"
                ));
            }
            else {
                $token = new UsernamePasswordToken($userDb, null, 'main', $userDb->getRoles());
                $this->get('security.token_storage')->setToken($token);

                $this->get('session')->set('_security_main', serialize($token));

                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                return $this->redirectToRoute('app_show');
            }
        }

        return $this->render('user/login.html.twig', array(
            'form' => $form->createView(),
            'errors' => false
        ));
    }

    public function check()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    /**
     * @Route("/user/logout", name="/user/logout")
     */
    public function logout()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }


    /**
     * @Route("/user/inscription", name="app_inscription")
     */
    public function inscription(Request $request)
    {
        $user = new User();

        $formBuilder = $this->createFormBuilder($user);

        $formBuilder
            ->add('email',TextType::class)
            ->add('password',PasswordType::class)
            ->add('pseudo',TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Créer mon compte'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $validator = $this->get('validator');

            $liste_erreurs = $validator->validate($user);

            if(!$form->isValid()) {
                return $this->render('user/inscription.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $liste_erreurs
                ));
            } else {
                $user->setDateInscription(new \DateTime());
                $user->setFolder($user->getDateInscription()->format('Ymd').$user->getPseudo());
                mkdir("../public/".$user->getFolder(), 777);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('user/inscription.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/user/signout", name="app_signout")
     */
    public function signout()
    {
        session_destroy();
    }
}
