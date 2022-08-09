<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Exception\ExceptionInterface;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @Route("", name="article_index")
     */
    public function index()
    {
        return $this->render('article/index.html.twig', [
            'articles' => $this->em->getRepository(Article::class)->findAll(),
        ]);
    }

    /**
     * @Route("/create", methods={"POST"}, name="article_create")
     */
    public function create(Request $request)
    {
        $article = new Article($request->request->get('title', 'title'));

        $this->em->persist($article);
        $this->em->flush();

        return $this->redirect($this->generateUrl('article_show', ['id' => $article->getId()]));
    }

    /**
     * @Route("/show/{id}", name="article_show")
     */
    public function show(Article $article)
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/apply-transition/{id}", methods={"POST"}, name="article_apply_transition")
     */
    public function applyTransition(WorkflowInterface $articleWorkflow, Request $request, Article $article)
    {
        try {
            $articleWorkflow
                ->apply($article, $request->request->get('transition'), [
                    'time' => date('y-m-d H:i:s'),
                ]);
            $this->em->flush();
        } catch (ExceptionInterface $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('article_show', ['id' => $article->getId()])
        );
    }

    /**
     * @Route("/reset-marking/{id}", methods={"POST"}, name="article_reset_marking")
     */
    public function resetMarking(Article $article)
    {
        $article->setMarking([]);
        $this->em->flush();

        return $this->redirect($this->generateUrl('article_show', ['id' => $article->getId()]));
    }
}
