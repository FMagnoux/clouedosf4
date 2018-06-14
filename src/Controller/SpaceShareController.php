<?php

namespace App\Controller;

use App\Entity\SpaceShare;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SpaceShareController extends Controller
{
    /**
     * @Route("/space/share/specific/{main}", name="app_show_specific_space")
     */
    public function showSpace($main)
    {
        if($main ==='true'){
            $main = true;
        }
        else {
            $main = false;
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $select = 'id_user_two';
        if($main){
            $select = 'id_user_one';
        }

        $spaces = $this->getDoctrine()
            ->getRepository(SpaceShare::class)
            ->findBy(array($select => $user->getId()));

        foreach ($spaces as $key => $value){
            if($main){
                $spaces[$key]->setUserTwo($this->getDoctrine());
            }
            else {
                $spaces[$key]->setUserOne($this->getDoctrine());
            }
        }

        return $this->render('space_share/showall.html.twig', [
            "spaces" => $spaces,
            "main" => $main
        ]);
    }

    /**
     * @Route("/space/share/spaces", name="app_show_share_spaces")
     */
    public function show(){
        return $this->render('space_share/show.html.twig', []);
    }

    /**
     * @Route("/space/share/spaces/details/{id}", name="app_space_show_specific")
     */
    public function details($id){

    }

    /**
     * @Route("/space/share/create", name="app_create_share_spaces")
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
            ->add('lifetime', DateType::class, array(
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
            if($relation['lifetime'] < $today){
                return $this->render('space_share/create.html.twig', [
                    'errorTime' => "La date indiquée est inférieure à la date du jour",
                    'form' => $form->createView(),
                ]);
            }
            else {
                $spaceShare = new SpaceShare();
                $spaceShare->setIdUserOne($this->get('security.token_storage')->getToken()->getUser()->getId());
                $spaceShare->setIdUserTwo($relation['user']);
                $spaceShare->setLife($relation['lifetime']);
                if($relation['password']){
                    $spaceShare->setPassword($relation['password']);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($spaceShare);
                $entityManager->flush();

                return $this->redirectToRoute('app_show_specific_space', array('main' => "true"));

            }
        }

        return $this->render('space_share/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/space/share/delete/{id}", name="app_space_delete_specific")
     */
    public function delete($id){
        $link = $this->getDoctrine()
            ->getRepository(SpaceShare::class)
            ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($link);
        $entityManager->flush();

        return $this->redirectToRoute('app_show_specific_space', array('main' => "true"));

    }

    /**
     * @Route("/space/share/update/{id}", name="app_space_update_specific")
     */
    public function update($id, Request $request){
        $link = $this->getDoctrine()
            ->getRepository(SpaceShare::class)
            ->find($id);

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('lifetime', DateType::class, array(
                'data' => $link->getLife()
            ))
            ->add('password', PasswordType::class, array(
                'required' => false
            ));

            $formBuilder->add('update', SubmitType::class, ['label' => 'Modifier la relation']);

            if($link->getPassword()){
                $formBuilder->add('deletepassword', CheckboxType::class, array(
                    'required' => false,
                    'disabled' => false,
                    'label' => "Supprimer le mot de passe"
                ));
            }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $relation = $form->getData();

            $today = new \DateTime('NOW');
            if($relation['lifetime'] < $today){
                return $this->render('space_share/update.html.twig', [
                    'errorTime' => "La date indiquée est inférieure à la date du jour",
                    'form' => $form->createView(),
                ]);
            }
            else {
                $link->setLife($relation['lifetime']);
                if($relation['password']){
                    $link->setPassword($relation['password']);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($link);
                $entityManager->flush();

                return $this->redirectToRoute('app_show_specific_space', array('main' => "true"));
            }
        }

        return $this->render('space_share/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
