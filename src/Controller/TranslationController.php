<?php

namespace App\Controller;

use App\Entity\Transition;
use App\Entity\Translation;
use App\Workflow\TranslationWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Symfony\Component\Workflow\Dumper\StateMachineGraphvizDumper;
use Symfony\Component\Workflow\Exception\ExceptionInterface;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * @Route("/translation")
 */
class TranslationController extends AbstractController
{
    private $finalWorkflow;

    public function __construct(
        private EntityManagerInterface $em,
        private KernelInterface        $kernel
    )
    {
        $this->setUpWorkflow();
    }

    /**
     * @Route("", name="translation_index")
     */
    public function index()
    {
        return $this->render('translation/index.html.twig', [
            'translations' => $this->em->getRepository(Translation::class)->findAll(),
        ]);
    }

    /**
     * @Route("/create", methods={"POST"}, name="translation_create")
     */
    public function create(Request $request)
    {
        $translation = new Translation($request->request->get('title', 'title'));

        $this->em->persist($translation);
        $this->em->flush();

        return $this->redirect($this->generateUrl('translation_show', ['id' => $translation->getId()]));
    }

    /**
     * @Route("/show/{id}", name="translation_show")
     */
    public function show(Translation $translation)
    {
        return $this->render('translation/show.html.twig', [
            'translation' => $translation,
            'finalWorkflow' => $this->finalWorkflow
        ]);
    }

    /**
     * @Route("/apply-transition/{id}", methods={"POST"}, name="translation_apply_transition")
     */
    public function applyTransition(WorkflowInterface $translationWorkflow, Request $request, Translation $translation)
    {
        try {
            $translationWorkflow
                ->apply($translation, $request->request->get('transition'), [
                    'time' => date('y-m-d H:i:s'),
                ]);
            $this->em->flush();
        } catch (ExceptionInterface $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('translation_show', ['id' => $translation->getId()])
        );
    }

    /**
     * @Route("/reset-marking/{id}", methods={"POST"}, name="translation_reset_marking")
     */
    public function resetMarking(Translation $translation)
    {
        $translation->setMarking([]);
        $this->em->flush();

        return $this->redirect($this->generateUrl('translation_show', ['id' => $translation->getId()]));
    }

    protected function setUpWorkflow()
    {
        // TODO: business logic to load transitions dynamically
        $transitions = [];

        $transition1 = $this->em->getRepository(Transition::class)->findOneBy(['name' => 'to_proofreading']);
        $transition2 = $this->em->getRepository(Transition::class)->findOneBy(['name' => 'to_customer_approval']);
        $transition3 = $this->em->getRepository(Transition::class)->findOneBy(['name' => 'to_shippment']);
        $transition4 = $this->em->getRepository(Transition::class)->findOneBy(['name' => 'to_reject']);

        array_push($transitions, $transition1, $transition2);

        $this->finalWorkflow = TranslationWorkflow::getWorkflow($transitions);

        $this->updateSvg($this->finalWorkflow);
    }

    protected function updateSvg(Workflow $workflow)
    {
        $definition = $workflow->getDefinition();
        $name = 'workflow.translation';

        if ($workflow instanceof StateMachine) {
            $dumper = new StateMachineGraphvizDumper();
        } else {
            $dumper = new GraphvizDumper();
        }

        $dot = $dumper->dump($definition, null, ['node' => ['width' => 1.6]]);

        $process = new Process(['dot', '-Tsvg']);
        $process->setInput($dot);
        $process->mustRun();

        $svg = $process->getOutput();

        $svg = preg_replace('/.*<svg/ms', sprintf('<svg class="img-responsive" id="%s"', str_replace('.', '-', $name)), $svg);

        $shortName = explode('.', $name)[1];

        file_put_contents(sprintf('%s/templates/%s/doc.svg.twig', $this->kernel->getProjectDir(), $shortName), $svg);
    }
}
