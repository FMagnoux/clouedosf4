<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Space;
use App\Entity\Share;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SpaceController extends Controller
{
    /**
     * @Route("/space/{id}", name="app_space_show")
     */
    public function show($id, \Symfony\Component\HttpFoundation\Request $request)
    {
        $space = $this->getDoctrine()
            ->getRepository(Space::class)
            ->find($id);

        $parameters = array(
            'files' => $space->getFiles(),
        );

        if($space->getId() != $this->get('security.token_storage')->getToken()->getUser()->getSpace()->getId()){
            $share = $this->getDoctrine()
                ->getRepository(Share::class)
                ->findBy(array('space' => $space->getId(), 'user'=> $this->get('security.token_storage')->getToken()->getUser()));
            if(!$share){
                return $this->render('space/access.html.twig', array(
                    "notAvailable" => true
                ));
            }
            else {
                if($share[0]->getPassword()){
                    $formBuilder = $this->createFormBuilder();

                    $formBuilder->add('password', PasswordType::class, array(
                        'required' => false
                    ));

                    $formBuilder->add('send', SubmitType::class, ['label' => 'Soumettre']);

                    $form = $formBuilder->getForm();

                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $password = $form->getData();

                        if($password['password'] != $share[0]->getPassword()){
                            $this->get('session')->getFlashBag()->clear();
                            $this->addFlash(
                                'error',
                                "La date indiquée est inférieure à la date du jour"
                            );
                            return $this->render('space/access.html.twig', array(
                                'form' => $form->createView()
                            ));
                        }
                        else {
                            $parameters['notOwner'] = true;
                            return $this->render('space/show.html.twig', $parameters);
                        }
                    }

                    return $this->render('space/access.html.twig', array(
                        'form' => $form->createView(),
                    ));
                }
                else {
                    $parameters['notOwner'] = true;
                    return $this->render('space/show.html.twig', $parameters);
                }
            }
        }
        else {
            return $this->render('space/show.html.twig', $parameters);
        }
    }
}
