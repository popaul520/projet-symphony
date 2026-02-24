<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{

    public function __construct(PanierService $panierService, BoutiqueService $boutiqueService)
    {
        $this->panierService = $panierService;
        $this->boutiqueService = $boutiqueService;
    }
    /*
    #[Route('/panier', name: 'app_panier')]
    public function index(): Response
    {

        $product =
            if(!$product){
                throw  $this->createNotFoundExeception('le profuit n\'existe pas');
            }
        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
        ]);
    }*/
    #[Route(
    path: '/{_locale}/panier', // L'URL auquel répondra cette action sera donc /test
    name: 'app_panier_index',
    requirements: ['_locale' => '%app.supported_locales%'],
    defaults: ['_locale' => 'fr']
    )]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            'contenu' => $this->panierService->getContenu(),
            'total' => $this->panierService->getTotal(),
            'nbProduits' => $this->panierService->getNombreProduits(),
        ]);
    }


    #[Route(
        path: '/{_locale}/panier/ajouter{idProduit}/{quantite}', // L'URL auquel répondra cette action sera donc /test
        name: 'app_panier_ajouter',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    #[Route('/ajouter/{id}', name: 'app_panier_ajouter')]
    public function ajouter(int $id): Response
    {
        $produit = $this->boutiqueService->findProduitById($id);
        if (!$produit) {
            throw $this->createNotFoundException("Le produit d'id $id n'existe pas.");
        }
        $this->panierService->ajouterProduit($id);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route(
        path: '/{_locale}/panier/enlever/{idProduit}/{quantite}', // L'URL auquel répondra cette action sera donc /test
        name: 'app_panier_enlever',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    public function enlever(int $id): Response
    {
        $produit = $this->boutiqueService->findProduitById($id);

        if (!$produit) {
            throw $this->createNotFoundException("Le produit d'id $id n'existe pas.");
        }
        $this->panierService->enleverProduit($id);
        return $this->redirectToRoute('app_panier_index');
    }


    #[Route(
        path: '/{_locale}/panier/supprimer/{idProduit}', // L'URL auquel répondra cette action sera donc /test
        name: 'app_panier_supprimer',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    #[Route('/supprimer/{id}', name: 'app_panier_supprimer')]
    public function supprimer(int $id): Response
    {
        $produit = $this->boutiqueService->findProduitById($id);

        if (!$produit) {
            throw $this->createNotFoundException("Le produit d'id $id n'existe pas.");
        }

        $this->panierService->supprimerProduit($id);

        return $this->redirectToRoute('app_panier_index');
    }


    #[Route(
        path: '/{_locale}/panier/vider', // L'URL auquel répondra cette action sera donc /test
        name: 'app_panier_vider',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    public function vider(): Response
    {
        $this->panierService->vider();

        return $this->redirectToRoute('app_panier_index');
    }

    #[Route(
        path: '/{_locale}/panier/vider', // L'URL auquel répondra cette action sera donc /test
        name: 'app_panier_vider',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    public function nombreProduits(PanierService $panier): Response {
        $this->panierService->nombreProduits();
        return $this->redirectToRoute('app_panier_index');
    }
}
