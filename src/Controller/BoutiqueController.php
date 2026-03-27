<?php

namespace App\Controller;

use App\Service\BoutiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/boutique', requirements: ['_locale' => '%app.supported_locales%'], defaults: ['_locale' => 'fr'])]
final class BoutiqueController extends AbstractController
{
    #[Route('', name: 'app_boutique_index')]
    public function index(BoutiqueService $boutiqueService): Response
    {
        $categories = $boutiqueService->findAllCategories();
        return $this->render('boutique/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/rayon/{idCategorie}', name: 'app_boutique_rayon')]
    public function rayon(int $idCategorie, BoutiqueService $boutiqueService): Response
    {
        $categorie = $boutiqueService->findCategorieById($idCategorie);
        $produits = $boutiqueService->findProduitsByCategorie($idCategorie);

        if (!$categorie) {
            throw $this->createNotFoundException("Ce rayon n'existe pas.");
        }

        return $this->render('boutique/rayon.html.twig', [
            'categorie' => $categorie,
            'produits' => $produits,
        ]);
    }

    // AJOUT DE LA MÉTHODE CHERCHER (Étape 2.3 du TP)
    #[Route('/chercher/{recherche}', name: 'app_boutique_chercher', requirements: ['recherche' => '.+'], defaults: ['recherche' => ''])]
    public function chercher(BoutiqueService $boutique, string $recherche): Response
    {
        // On décode la recherche au cas où
        $recherche = urldecode($recherche);
        $produits = $boutique->findProduitsByLibelleOrTexte($recherche);

        return $this->render('boutique/chercher.html.twig', [
            'produits' => $produits,
            'recherche' => $recherche
        ]);
    }
}
