<?php

namespace App\Controller;

use App\Entity\Usager;
use App\Form\UsagerType;
use App\Repository\UsagerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/{_locale}/usager', requirements: ['_locale' => 'fr|en'])]
final class UsagerController extends AbstractController
{
    #[Route('', name: 'app_usager_index', methods: ['GET'])]
    public function index(): Response
    {
        // On ne fait plus $usagerRepository->find(1)
        // On récupère l'objet Usager directement depuis la session de connexion
        $user = $this->getUser();

        if (!$user) {
            // Au cas où, si pas connecté, on renvoie vers le login
            return $this->redirectToRoute('app_login');
        }

        return $this->render('usager/index.html.twig', [
            'usager' => $user,
        ]);
    }

    #[Route('/new', name: 'app_usager_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usager = new Usager();
        $form = $this->createForm(UsagerType::class, $usager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($usager, $plainPassword);
            $usager->setPassword($hashedPassword);

            $usager->setRoles(["ROLE_CLIENT"]);

            $entityManager->persist($usager);
            $entityManager->flush();

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                //ça marche
                $this->addFlash('success', 'Votre compte a été créé avec succès !');

                return $this->redirectToRoute('app_usager_index');
            }

            return $this->redirectToRoute('app_usager_index');
        }

        return $this->render('usager/new.html.twig', [
            'usager' => $usager,
            'form' => $form,
        ]);
    }
}
