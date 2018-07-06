<?php

namespace App\Controller;

use App\Entity\Share;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShareController extends Controller
{

    /**
     * @Route("/share/home", name="app_share_home")
     */
    public function home(){
        return $this->render('share/home.html.twig', []);
    }

    /**
     * @Route("/share/show/main", name="app_share_show_main")
     */
    public function showMain()
    {
        $shares = $this->getDoctrine()
            ->getRepository(Share::class)
            ->findBy(array('space' => $this->get('security.token_storage')->getToken()->getUser()->getSpace()));

        return $this->render('share/main.html.twig', [
            'shares' => $shares
        ]);
    }


    /**
     * @Route("/share/show/second", name="app_share_show_second")
     */
    public function showSecond(){
        $shares = $this->getDoctrine()
            ->getRepository(Share::class)
            ->findBy(array('user' => $this->get('security.token_storage')->getToken()->getUser()));

        return $this->render('share/second.html.twig', [
            'shares' => $shares
        ]);
    }

    /**
     * @Route("/share/create", name="app_create_share")
     */
    public function create(Request $request){
        $formBuilder = $this->createFormBuilder();

        $usersBdd = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        $users = array();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        foreach ($usersBdd as $key => $value){
            if($usersBdd[$key]->getId() == $user->getId()){
                unset($usersBdd[$key]);
            }
            else {
                $users[$usersBdd[$key]->getPseudo()] = $usersBdd[$key]->getId();
            }
        }

        $formBuilder
            ->add('user', ChoiceType::class, array(
                'choices' => $users
            ))
            ->add('life', DateType::class, array(
            ))
            ->add('password', PasswordType::class, array(
                'required' => false
            ))
            ->add('create', SubmitType::class, ['label' => 'Ajouter la relation']);


        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $relation = $form->getData();
            $today = new \DateTime('NOW');
            if($relation['life'] < $today){
                $this->get('session')->getFlashBag()->clear();
                $this->addFlash(
                    'error',
                    "La date indiquée est inférieure à la date du jour"
                );
            }
            else {
                $share = new Share();
                $share->setLife($relation['life']);

                $space = $this->get('security.token_storage')->getToken()->getUser()->getSpace();

                $share->setLife($relation['life']);
                if($relation['password']){
                    $share->setPassword($relation['password']);
                }
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($relation['user']);
                $share->setSpace($space);
                $share->setUser($user);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($share);
                $entityManager->flush();

                return $this->redirectToRoute('app_share_show_main');
            }
        }

        return $this->render('share/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/share/update/{id}", name="app_update_share")
     */
    public function update($id, Request $request){
        $share = $this->getDoctrine()
            ->getRepository(Share::class)
            ->find($id);

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('life', DateType::class, array(
            ))
            ->add('password', PasswordType::class, array(
                'required' => false
            ));
        if($share->getPassword()){
            $formBuilder->add('deletepassword', CheckboxType::class, array(
                'required' => false
            ));
        }

        $formBuilder->add('update', SubmitType::class, ['label' => 'Modifier la relation']);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $relation = $form->getData();

            $today = new \DateTime('NOW');
            if($relation['life'] < $today){
                $this->get('session')->getFlashBag()->clear();
                $this->addFlash(
                    'error',
                    "La date indiquée est inférieure à la date du jour"
                );
            }
            else {
                $share->setLife($relation['life']);

                if($relation['password']){
                    $share->setPassword($relation['password']);
                }

                if($relation['deletepassword']){
                    $share->setPassword("");
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($share);
                $entityManager->flush();

                return $this->redirectToRoute('app_share_show_main');
            }
        }

        return $this->render('share/update.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/share/delete/{id}", name="app_delete_share")
     */
    public function delete($id){
        $share = $this->getDoctrine()
            ->getRepository(Share::class)
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($share);
        $entityManager->flush();
    }


}
