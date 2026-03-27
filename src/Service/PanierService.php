<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\BoutiqueService;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Usager;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;

class PanierService
{
    private $session;
    private $produitRepository;
    private $em;
    private $panier;
    const PANIER_SESSION = 'panier';

    // On remplace BoutiqueService par ProduitRepository et EntityManagerInterface
    public function __construct(
        RequestStack           $requestStack,
        ProduitRepository      $produitRepository,
        EntityManagerInterface $em
    )
    {
        $this->produitRepository = $produitRepository;
        $this->em = $em;
        $this->session = $requestStack->getSession();
        $this->panier = $this->session->get(self::PANIER_SESSION, []);
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->produitRepository->find($idProduit); // Utilise le Repo
            if ($produit) {
                $total += $produit->getPrix() * $quantite;
            }
        }
        return $total;
    }

    public function getContenu(): array
    {
        $contenu = [];
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->produitRepository->find($idProduit);
            if ($produit) {
                $contenu[] = [
                    "produit" => $produit,
                    "quantite" => $quantite
                ];
            }
        }
        return $contenu;
    }

    public function panierToCommande(Usager $usager): ?Commande
    {
        if (empty($this->panier)) {
            return null;
        }

        // 1. Créer la commande
        $commande = new Commande();
        $commande->setUsger($usager);

        $commande->setDateCreation(new \DateTimeImmutable());
        $commande->setValidation(true);

        // 2. Créer les lignes de commande
        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->produitRepository->find($idProduit);

            if ($produit) {
                $ligne = new LigneCommande();
                $ligne->setProduit($produit);
                $ligne->setQuantite($quantite);
                $ligne->setPrix($produit->getPrix());
                $ligne->setCommande($commande);
                $this->em->persist($ligne);
            }
        }

        // Sauvegarder en base
        $this->em->persist($commande);
        $this->em->flush();

        // Vider le panier
        $this->vider();

        return $commande;
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
    public function getNombreProduits() : int
    {
        // Renvoie le nombre de produits (somme des quantités)
        return array_sum($this->panier);
    }




}

/*
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

public function panierToCommande(Usager $usager): ?Commande
{
    if (empty($this->panier)) return null;

    $commande = new Commande();
    $commande->setUsager($usager);
    $commande->setDateCreation(new \DateTimeImmutable());
    $commande->setValidation(true);

    foreach ($this->panier as $id => $quantite) {
        $produit = $this->produitRepo->find($id);

        $ligne = new LigneCommande();
        $ligne->setProduit($produit);
        $ligne->setQuantite($quantite);
        $ligne->setPrix($produit->getPrix());
        $ligne->setCommande($commande);

        $this->entityManager->persist($ligne);
    }

    $this->entityManager->persist($commande);
    $this->entityManager->flush();

    $this->vider(); // Méthode pour vider la session
    return $commande;
}
}*/
/*
 use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Usager;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

// ... dans la classe PanierService

private $em; // Ajoute l'EntityManager au constructeur plus tard

// Modifie ton constructeur pour inclure l'EntityManagerInterface
public function __construct(RequestStack $requestStack, BoutiqueService $boutique, EntityManagerInterface $em) {
    $this->boutique = $boutique;
    $this->em = $em;
    $this->session = $requestStack->getSession();
    $this->panier = $this->session->get(self::PANIER_SESSION, []);
}

public function panierToCommande(Usager $usager): ?Commande
{
    if (empty($this->panier)) {
        return null;
    }

    // 1. Créer la commande
    $commande = new Commande();
    $commande->setUsager($usager);
    $commande->setDateCreation(new \DateTime());
    $commande->setValidation(false);

    // 2. Créer les lignes de commande
    foreach ($this->panier as $idProduit => $quantite) {
        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        if ($produit) {
            $ligne = new LigneCommande();
            $ligne->setProduit($produit);
            $ligne->setQuantite($quantite);
            $ligne->setPrix($produit->getPrix());
            $ligne->setCommande($commande);

            $this->em->persist($ligne);
        }
    }

    $this->em->persist($commande);
    $this->em->flush();

    // 3. Vider le panier
    $this->vider();

    return $commande;
}
 */
