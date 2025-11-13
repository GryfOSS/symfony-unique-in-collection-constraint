<?php

namespace App\Command;

use App\Model\Collection;
use App\Model\Single;
use App\Model\TwoFieldsContraintsCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:test-group-and-name',
    description: 'Test command for two fields validation issue.',
)]
class TestGroupAndNameCommand extends Command
{
    public function __construct(protected ValidatorInterface $validator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $model1 = new Single();
        $model1->id = 1;
        $model1->name = 'First';
        $model1->group = 1;

        $model2 = new Single();
        $model2->id = 2;
        $model2->name = 'Second';
        $model2->group = 1;

        $collection = new TwoFieldsContraintsCollection();
        $collection->name = '2nd Collection';
        $collection->singles = [$model1, $model2];

        $issues = $this->validator->validate($collection);
        //there should be no issue
        if ($issues->count() !== 0) {
            return Command::FAILURE;
        }

        $model1 = new Single();
        $model1->id = 1;
        $model1->name = 'First';
        $model1->group = 1;

        $model2 = new Single();
        $model2->id = 2;
        $model2->name = 'First';
        $model2->group = 1;

        $collection = new TwoFieldsContraintsCollection();
        $collection->name = '3nd Collection';
        $collection->singles = [$model1, $model2];

        $issues = $this->validator->validate($collection);
        //there should be no issue
        if ($issues->count() !== 1) {
            return Command::FAILURE;
        }

        $model1 = new Single();
        $model1->id = 1;
        $model1->name = 'First';
        $model1->group = 1;

        $model2 = new Single();
        $model2->id = 2;
        $model2->name = 'First';
        $model2->group = 1;

        $model3 = new Single();
        $model3->id = 3;
        $model3->name = 'First';
        $model3->group = 2;

        $collection = new TwoFieldsContraintsCollection();
        $collection->name = '4th Collection';
        $collection->singles = [$model1, $model2, $model3];

        $issues = $this->validator->validate($collection);
        //there should be no issue (same name but different group)
        if ($issues->count() !== 1) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
