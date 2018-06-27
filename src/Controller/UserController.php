<?php

namespace App\Controller;

use App\Service\Email;
use App\Service\Token;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\User;
use App\Entity\Space;

class UserController extends Controller
{
    /**
     * @Route("/login", name="app_login")
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
                ->findOneBy(array('email' => $user['email'], 'password' => $user['password']));

            if (!$userDb) {
                return $this->render('user/login.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => "Les identifiants sont incorrects"
                ));
            }
            else {
                $token = new UsernamePasswordToken($userDb, null, 'main', $userDb->getRoles());
                $this->get('security.token_storage')->setToken($token);

                $this->get('session')->set('_security_main', serialize($token));

                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                return $this->redirectToRoute('app_space_show', array('id' => $userDb->getSpace()->getId()));
            }
        }

        return $this->render('user/login.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/signup", name="app_signup")
     */
    public function inscription(Request $request, Email $email, Token $token)
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
                $fileSystem = new Filesystem();

                $user->setDateInscription(new \DateTime());

                $space = new Space();
                $space->setName($user->getDateInscription()->format('Ymd').str_replace(array(" ","."), "", $user->getPseudo()));
                $space->setPath("../public/".$space->getName()."/");
                $space->setSize(1000000);

                $user->setSpace($space);

                $fileSystem->mkdir("../public/".$space->getName(), 0777);
                $fileSystem->mkdir("../public/".$space->getName()."/profil", 0777);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                try {

                    $token->getToken()->setUser($user);
                    $token->generate("TOKEN_VALID_ACCOUNT");
                    $parameters = array(
                        'url' => $this->generateUrl('app_validate_account', array('value' => $token->getToken()->getValue())),
                        'user' => $user
                    );
                    $email->send($user->getEmail(),"[Clouedo] Validation inscription", 'email/validate_account.html.twig', $parameters);

                }
                catch (\Exception $e){

                }

                return $this->redirectToRoute('app_login', array('accountCreated' => "Votre compte a bien été créé, un mail a été envoyé pour le valider"));
            }
        }

        return $this->render('user/inscription.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/profil", name="app_profil")
     */
    public function profil(){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('user/profil.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * @Route("/update", name="app_update_user")
     */
    public function update(Request $request){

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('email',TextType::class, array('data' => $user->getEmail()))
            ->add('password', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe doivent être identiques',
                    'required' => false
                )
            )
            ->add('fileprofil', FileType::class)
            ->add('update', SubmitType::class, array('label' => 'Valider'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userFields = $form->getData();

            $fileUploaded = $userFields['fileprofil'];

            if(!in_array($fileUploaded->guessExtension(),array("jpg","jpeg","png"))){
                return $this->render('user/update.html.twig', array(
                    'form' => $form->createView(),
                    'errorImg' => "La photo de profil transmise ne respecte pas le bon format de fichier (.jpg, .jpeg, .png)"
                ));
            }
            else {
                if($user->getPathImg()){
                    unlink($user->getPathImg());
                }
                $user->setPathImg('profil/'.$fileUploaded->getClientOriginalName());
                $fileUploaded->move($user->getSpace()->getPath()."profil/", $fileUploaded->getClientOriginalName());

                if($userFields['password']){
                    $user->setPassword($userFields['password']);
                }
                $user->setEmail($userFields['email']);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_profil');

            }
        }
        return $this->render('user/update.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete", name="app_delete_user")
     */
    public function delete(){
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $space = $user->getSpace();
        $files = $space->getFiles();

        $sharesSpace = $space->getShares();
        $userShares = $user->getShare();

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($files as $key => $value){
            if(unlink($space->getPath().$files[$key]->getPath())) {
                $entityManager->remove($files[$key]);
            }
        }
        foreach ($sharesSpace as $key => $value){
            $entityManager->remove($sharesSpace[$key]);
        }

        foreach ($userShares as $key => $value){
            $entityManager->remove($userShares[$key]);
        }

        if($user->getPathImg()){
            unlink($space->getPath().$user->getPathImg());
        }

        if(rmdir($space->getPath()."profil") && rmdir($space->getPath())){
            $entityManager->remove($user);
        }

        $entityManager->flush();

        $this->get('security.token_storage')->setToken(null);

        return $this->redirectToRoute('app_login');

    }

    /**
     * @Route("/password/forgot", name="app_forgot_password")
     */
    public function forgotPassword(Request $request, Email $email, Token $token){

        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('email',TextType::class)
            ->add('submit', SubmitType::class, array('label' => 'Soumettre'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $userDb = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array('email' => $user['email']));

            if(!$userDb){
                return $this->render('user/forgot_password.html.twig', array(
                    'form' => $form->createView(),
                    'error' => "Cette email n'est associé à aucun utilisateur"
                ));
            }
            else {
                try {
                    $token->getToken()->setUser($userDb);
                    $token->generate("TOKEN_FORGOT_PASSWORD");
                    $parameters = array(
                        'url' => $this->generateUrl('app_reset_password', array('value' => $token->getToken()->getValue()))
                    );
                    $email->send($userDb->getEmail(),"[Clouedo] Reinitialisation du mot de passe", 'email/forgot_password.html.twig', $parameters);

                    return $this->render('user/forgot_password.html.twig', array(
                        'form' => $form->createView(),
                        'success' => "Un email vous a été envoyé pour reinitialiser votre mot de passe"
                    ));
                }
                catch (\Exception $e){
                    return $this->render('user/forgot_password.html.twig', array(
                        'form' => $form->createView(),
                        'error' => $e->getMessage()
                    ));
                }
            }
        }

        return $this->render('user/forgot_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/password/reset/{value}", name="app_reset_password")
     */
    public function resetPassword($value){
        return $this->render('user/reset_password.html.twig', array(
        ));
    }

    /**
     * @Route("/validation/notsend", name="app_validation_not_send")
     */
    public function validationNotSend(Request $request, Email $email, Token $token){
        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('email',TextType::class)
            ->add('submit', SubmitType::class, array('label' => 'Soumettre'));

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $userDb = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array('email' => $user['email']));

            if(!$userDb){
                return $this->render('user/validate_account.html.twig', array(
                    'form' => $form->createView(),
                    'error' => "Cette email n'est associé à aucun utilisateur"
                ));
            }
            else {
                try {
                    $token->getToken()->setUser($userDb);
                    $token->generate("TOKEN_VALID_ACCOUNT");
                    $parameters = array(
                        'url' => $this->generateUrl('app_validate_account', array('value' => $token->getToken()->getValue()))
                    );
                    $email->send($userDb->getEmail(),"[Clouedo] Validation inscription", 'email/validate_account.html.twig', $parameters);

                    return $this->render('user/validate_account.html.twig', array(
                        'form' => $form->createView(),
                        'success' => "Un email vous a été envoyé pour valider votre compte"
                    ));
                }
                catch (\Exception $e){
                    return $this->render('user/validate_account.html.twig', array(
                        'form' => $form->createView(),
                        'error' => $e->getMessage()
                    ));
                }
            }
        }

        return $this->render('user/validate_account.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        $this->get('security.token_storage')->setToken(null);
    }

}
