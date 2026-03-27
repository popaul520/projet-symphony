<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

# On peut définir ici un préfixe pour les URL de toutes les routes des actions de la classe DefaultController
#[Route(
    path: '/',

)]
class DefaultController extends AbstractController
{
    #[Route(
        path: '/{_locale}',
        name: 'app_default_index',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    public function index(): Response
    {

        $now = new \DateTime("now");
        return $this->render('default/index.html.twig', [
            "dateActuelle" => $now,
        ]);
    }

    #[Route(
        path: '/{_locale}/test', // L'URL auquel répondra cette action sera donc /test
        name: 'app_default_test',
        requirements: ['_locale' => '%app.supported_locales%'],
        defaults: ['_locale' => 'fr']
    )]
    public function test(): Response
    {
        // On renvoie une réponse
        return new Response("Hello World !");
    }

    // TODO : route et contrôleur de la page de contact
    #[Route(
        path: '/{_locale}/contact',
        name: 'contact',
        requirements: ['_locale' => '%app.supported_locales%']
    )]
     public function contact(): Response
     {
         return $this->render('default/contact.html.twig');
     }

}
