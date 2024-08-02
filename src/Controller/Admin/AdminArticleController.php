<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminArticleController extends AbstractController
{

    #[Route('/admin/articles', 'admin_list_articles')]
    public function adminListArticles(ArticleRepository $articleRepository): Response
    {


        $articles = $articleRepository->findAll();

        return $this->render('admin/page/article/list_articles.html.twig', [
           'articles' => $articles
        ]);
    }

    #[Route('/admin/articles/delete/{id}', 'admin_delete_article')]
    public function deleteArticle(int $id, ArticleRepository $articleRepository, EntityManagerInterface $entityManager)
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            $html404 = $this->renderView('admin/page/404.html.twig');
            return new Response($html404, 404);
        }

        try {
            $entityManager->remove($article);
            $entityManager->flush();

            // permet d'enregistrer un message dans la session de PHP
            // ce message sera affiché grâce à twig sur la prochaine page
            $this->addFlash('success', 'Article bien supprimé !');

        } catch(\Exception $exception){
            return $this->renderView('admin/page/error.html.twig', [
                'errorMessage' => $exception->getMessage()
            ]);
        }

        return $this->redirectToRoute('admin_list_articles');
    }


    #[Route('/admin/articles/insert', 'admin_insert_article')]
    public function insertArticle(Request $request, EntityManagerInterface $entityManager)
    {
        $article = new Article();

        $articleCreateForm = $this->createForm(ArticleType::class, $article);

        $articleCreateForm->handleRequest($request);

        if($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'article enregistré');
        }

        $articleCreateFormView = $articleCreateForm->createView();

        return $this->render('admin/page/article/insert_article.html.twig', [
            'articleForm' => $articleCreateFormView
        ]);

    }

    #[Route('/admin/articles/update/{id}', 'admin_update_article')]
    public function updateArticle(int $id, Request $request, EntityManagerInterface $entityManager, ArticleRepository $articleRepository)
    {
        $article = $articleRepository->find($id);

        $articleCreateForm = $this->createForm(ArticleType::class, $article);

        $articleCreateForm->handleRequest($request);

        if ($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'article enregistré');
        }

        $articleCreateFormView = $articleCreateForm->createView();

        return $this->render('admin/page/article/update_article.html.twig', [
            'articleForm' => $articleCreateFormView
        ]);

    }



}