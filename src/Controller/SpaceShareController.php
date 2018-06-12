<?php

namespace App\Controller;

use App\Entity\SpaceShare;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                $spaces->setUserTwo();
            }
            else {
                $spaces->setUserOne();
            }
        }

        return $this->render('space_share/showspecific.html.twig', [
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
        foreach ($usersBdd as $key => $value){
            $users[$usersBdd[$key]->getPseudo()] = $usersBdd[$key]->getId();
        }

        $formBuilder
            ->add('user', ChoiceType::class, array(
                'choices' => $users
            ))
            ->add('lifetime', DateType::class, array(
            ))
            ->add('create', SubmitType::class, ['label' => 'Ajouter la relation']);


        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('space_share/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete($id){

    }

    public function update($id){

    }
}
