<?php

namespace App\Controller;

use App\Service\BoutiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoutiqueController extends AbstractController
{
    #[Route(
        path:'/boutique/',
        name: 'app_boutique_index')]
    public function index(): Response
    {

        $categories = BoutiqueService::LES_CATEGORIES;
        return $this->render('boutique/index.html.twig', [
            'boutique' => $categories,
        ]);
    }

    #[Route(
        path:'/boutique/',
        name: 'app_boutique_rayon'
    )]
    public function rayon(int $idCategorie): Response{
        $produit = findProduitsByCategorie($idCategorie);
        return $this->render('boutique/index.html.twig', ["Produitsid" => $produit]);
    }
}
