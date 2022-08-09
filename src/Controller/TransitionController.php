<?php

namespace App\Controller;

use App\Entity\Transition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transition")
 */
class TransitionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @Route("", name="transition_index")
     */
    public function index()
    {
        return $this->render('transition/index.html.twig', [
            'transitions' => $this->em->getRepository(Transition::class)->findAll(),
        ]);
    }

    /**
     * @Route("/create", methods={"POST"}, name="transition_create")
     */
    public function create(Request $request)
    {
        $transition = new Transition(
            $request->request->get('title', 'title'),
            explode(',', $request->request->get('from')),
            explode(',', $request->request->get('to')));

        $this->em->persist($transition);
        $this->em->flush();

        return $this->redirect($this->generateUrl('transition_show', ['id' => $transition->getId()]));
    }

    /**
     * @Route("/show/{id}", name="transition_show")
     */
    public function show(Transition $transition)
    {
        return $this->render('transition/show.html.twig', [
            'transition' => $transition,
        ]);
    }
}
