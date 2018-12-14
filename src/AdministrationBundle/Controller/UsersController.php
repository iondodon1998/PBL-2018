<?php

namespace AdministrationBundle\Controller;

use AdministrationBundle\Entity\User;
use AdministrationBundle\Form\ChangePassword;
use AdministrationBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use \AdministrationBundle\Resources\views\base;


class UsersController extends Controller
{
    /**
     * @Route("/admin/users", name="users")
     *
     */
    public function listUsers(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $users =  $entityManager->getRepository(User::class)->findAll();

        if(!$users){
            throw new \RuntimeException('error while fetching users');
        }

        return $this->render(
            '@Administration/Users/users.html.twig',
            array('users' => $users)
        );
    }

    /**
     * @Route("/admin/users/new", name="users_new", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse|Response
     */
    public function newUser(Request $request, EntityManagerInterface $em) : Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('users');
        }

        return $this->render('@Administration/Users/new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/admin/users/edit/{id}", name="user_edit", methods={"GET", "POST"})
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     */
    public function editUser($id, Request $request) : Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
//            $entityManager->persist($location);
            $entityManager->flush();
            return $this->redirectToRoute('users');
        }

        return $this->render('@Administration/Users/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Route("/admin/users/deletion/{id}", name="user_delete")
     * @param $id
     * @return RedirectResponse
     */
    public function deleteUser($id): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/admin/users/change_password/", name="change_password")
     * @return Response
     */
    public function ChangePasswordAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePassword::class);
        $form->setData($user);

        $form->handleRequest($request);


        return $this->render('@Administration/Users/change_password.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
