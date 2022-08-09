programmatic-symfony-workflow
=============================

Installation
------------

    docker compose exec php bin/console doctrine:database:create
    docker compose exec php bin/console doctrine:schema:update --force

If you update the workflow configuration, you will need to regenerate the SVG by running the following command:

    # For the task
    docker compose exec php bin/console  workflow:build:svg state_machine.task
    # For the article
    docker compose exec php bin/console  workflow:build:svg workflow.article
