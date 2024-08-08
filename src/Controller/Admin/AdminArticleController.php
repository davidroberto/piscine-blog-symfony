<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\UniqueFilenameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function insertArticle(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ParameterBagInterface $params, UniqueFilenameGenerator $uniqueFilenameGenerator)
    {
        $article = new Article();

        $articleCreateForm = $this->createForm(ArticleType::class, $article);

        $articleCreateForm->handleRequest($request);

        if($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {

            $imageFile = $articleCreateForm->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);

                $extension = $imageFile->guessExtension();

                // j'ai créé une classe "de service"
                // qui genere un nom unique pour un fichier
                $newFilename = $uniqueFilenameGenerator->generateUniqueFilename($safeFilename, $extension);

                try {
                    // je récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // je déplace le fichier dans le dossier /public/upload en partant de la racine
                    // du projet, et je renomme le fichier avec le nouveau nom (slugifié et identifiant unique)
                    $imageFile->move( $rootPath.'/public/uploads', $newFilename);
                } catch (FileException $e){
                    dd($e->getMessage());
                }

                // je stocke dans la propriété image
                // de l'entité article le nom du fichier
                $article->setImage($newFilename);
            }


            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'article enregistré');

            return $this->redirectToRoute('admin_list_articles');
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