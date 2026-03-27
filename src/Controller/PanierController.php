<?php

namespace App\Controller;

use App\Service\BoutiqueService;
use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/panier', requirements: ['_locale' => '%app.supported_locales%'], defaults: ['_locale' => 'fr'])]
final class PanierController extends AbstractController
{
    private PanierService $panierService;
    private BoutiqueService $boutiqueService;

    public function __construct(PanierService $panierService, BoutiqueService $boutiqueService)
    {
        $this->panierService = $panierService;
        $this->boutiqueService = $boutiqueService;
    }

    #[Route('', name: 'app_panier_index')]
    public function index(): Response
    {
        return $this->render('panier/index.html.twig', [
            // On utilise 'items' pour correspondre à ton template Twig
            'items' => $this->panierService->getContenu(),
            'total' => $this->panierService->getTotal(),
        ]);
    }

    #[Route('/ajouter/{idProduit}/{quantite}', name: 'app_panier_ajouter', defaults: ['quantite' => 1])]
    public function ajouter(int $idProduit, int $quantite): Response
    {
        $produit = $this->boutiqueService->findProduitById($idProduit);
        if (!$produit) {
            throw $this->createNotFoundException("Le produit n'existe pas.");
        }
        $this->panierService->ajouterProduit($idProduit, $quantite);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/enlever/{idProduit}/{quantite}', name: 'app_panier_enlever', defaults: ['quantite' => 1])]
    public function enlever(int $idProduit, int $quantite): Response
    {
        $this->panierService->enleverProduit($idProduit, $quantite);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/supprimer/{idProduit}', name: 'app_panier_supprimer')]
    public function supprimer(int $idProduit): Response
    {
        $this->panierService->supprimerProduit($idProduit);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/vider', name: 'app_panier_vider')]
    public function vider(): Response
    {
        $this->panierService->vider();
        return $this->redirectToRoute('app_panier_index');
    }

    public function nombreProduits(PanierService $panier): Response
    {
        $nb = $panier->getNombreProduits();
        // On renvoie juste le chiffre brut dans la réponse
        return new Response((string)$nb);
    }
}
