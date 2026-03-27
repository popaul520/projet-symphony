<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\BoutiqueService;

class PanierService
{
    private $session;
    private $boutique;
    private $panier;
    const PANIER_SESSION = 'panier';

    public function __construct(RequestStack $requestStack, BoutiqueService $boutique)
    {
        $this->boutique = $boutique;
        $this->session = $requestStack->getSession();

        // Récupération du panier en session s'il existe, initialisation à vide sinon
        $this->panier = $this->session->get(self::PANIER_SESSION, []);
    }

    public function getTotal() : float
    {
        $total = 0;
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->boutique->findProduitById($idProduit);
            if ($produit) {
                $total += $produit->prix * $quantite;
            }
        }
        return $total;
    }

    public function getNombreProduits() : int
    {
        // Renvoie le nombre de produits (somme des quantités)
        return array_sum($this->panier);
    }

    public function ajouterProduit(int $idProduit, int $quantite = 1) : void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] += $quantite;
        } else {
            $this->panier[$idProduit] = $quantite;
        }
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    public function enleverProduit(int $idProduit, int $quantite = 1) : void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] -= $quantite;
            if ($this->panier[$idProduit] <= 0) {
                unset($this->panier[$idProduit]);
            }
            $this->session->set(self::PANIER_SESSION, $this->panier);
        }
    }

    public function supprimerProduit(int $idProduit) : void
    {
        if (isset($this->panier[$idProduit])) {
            unset($this->panier[$idProduit]);
            $this->session->set(self::PANIER_SESSION, $this->panier);
        }
    }

    public function vider() : void
    {
        $this->panier = [];
        $this->session->remove(self::PANIER_SESSION);
    }

    public function getContenu() : array
    {
        $contenu = [];
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->boutique->findProduitById($idProduit);
            if ($produit) {
                $contenu[] = [
                    "produit" => $produit,
                    "quantite" => $quantite
                ];
            }
        }
        return $contenu;
    }
}
