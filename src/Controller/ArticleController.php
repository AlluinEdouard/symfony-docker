<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/creer', name: 'app_article_creer')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();

        // Crée le formulaire à partir de ArticleType
        $form = $this->createForm(ArticleType::class, $article);

        // Gère la requête (remplit le formulaire avec les données de l'utilisateur)
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setDate(new DateTimeImmutable());

            $entityManager->persist($article);
            $entityManager->flush();

            // Ajoute un message flash pour confirmer la création de l'article
            $this->addFlash('success', 'Article créé avec succès !');

        }

        // Rend le template avec le formulaire
        return $this->render('article/creer.html.twig', [
            'controller_name' => 'ArticleController',
            'titre' => 'Créer un article',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/supprimer/{id?}', name: 'app_article_supprimer')]
public function delete(EntityManagerInterface $entityManager, ?int $id): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'ID est absent
        if (!$id) {
            $this->addFlash('error', 'Veuillez spécifier un ID pour supprimer un article.');
            return $this->render('article/supprimer.html.twig', [
                'article' => null,
            ]);
        }

        // Chercher l'article par son ID
        $article = $entityManager->getRepository(Article::class)->find($id);

        // Vérifier si l'article existe
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé!');
            return $this->redirectToRoute('app_article');
        }

        // Supprimer l'article de la base de données
        $entityManager->remove($article);
        $entityManager->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'Article supprimé avec succès!');

        // Rediriger vers la page des articles (index)
        return $this->redirectToRoute('app_article_liste');
    }

    #[Route('/article/modifier/{id?}', name: 'app_article_modifier')]
public function edit(EntityManagerInterface $entityManager, Request $request, ?int $id): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // Si aucun ID n'est fourni
        if (!$id) {
            $this->addFlash('error', 'Veuillez spécifier un ID pour modifier un article.');
            return $this->render('article/modifier.html.twig', [
                'article' => null,
            ]);
        }

        // Récupérer l'article par son ID
        $article = $entityManager->getRepository(Article::class)->find($id);

        // Vérifier si l'article existe
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé!');
            return $this->redirectToRoute('app_article');
        }

        // Si la requête est en POST, traiter les données du formulaire
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $texte = $request->request->get('texte');
            $publie = $request->request->get('publie') === 'on'; // Case à cocher (vérifie si elle est cochée)

            // Mettre à jour les champs de l'article
            $article->setTitre($titre);
            $article->setTexte($texte);
            $article->setPublie($publie);

            // Sauvegarder dans la base de données
            $entityManager->flush();

            $this->addFlash('success', 'Article modifié avec succès!');
            return $this->redirectToRoute('app_article_liste');
        }

        // Afficher le formulaire dans la vue
        return $this->render('article/modifier.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/liste', name: 'app_article_liste')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/liste.html.twig', [
            'articles' => $articles,
        ]);
    }




}
