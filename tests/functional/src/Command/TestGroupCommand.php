<?php

namespace App\Command;

use App\Model\Collection;
use App\Model\Single;
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
    name: 'app:test-group',
    description: 'Test command for a single field validation issue.',
)]
class TestGroupCommand extends Command
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

        $collection = new Collection();
        $collection->name = '1st Collection';
        $collection->singles = [$model1, $model2];

        $issues = $this->validator->validate($collection);

        //we expect here one issue
        foreach ($issues as $issue) {
            $output->writeln($issue->getMessage());
        }

        if ($issues->count() !== 1) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
