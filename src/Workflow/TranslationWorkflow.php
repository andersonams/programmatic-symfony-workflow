<?php

namespace App\Workflow;


use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class TranslationWorkflow
{
    public function __construct()
    {
    }


    public static function getWorkflow($transitions = []): Workflow
    {
        $definition = new DefinitionBuilder();

        /*
        $places = [];

        foreach ($states as $state) {
            foreach ($state->getFroms() as $from) {
                array_push($places, $from);
            }

            foreach ($state->getTos() as $to) {
                array_push($places, $to);
            }
        }

        $definition->addPlaces(array_unique($places));
        */

        /** @var \App\Entity\Transition $transition */
        foreach ($transitions as $transition) {
            $definition->addPlaces($transition->getFroms());
            $definition->addPlaces($transition->getTos());

            // Transitions
            $definition->addTransition(new Transition($transition->getName(), $transition->getFroms(), $transition->getTos()));
        }

        $marking = new MethodMarkingStore(false, 'marking');

        return new Workflow($definition->build(), $marking, null, 'translation');
    }
}
