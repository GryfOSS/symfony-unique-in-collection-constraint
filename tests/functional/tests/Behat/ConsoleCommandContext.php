<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ConsoleCommandContext implements Context
{
    private Application $application;
    private CommandTester $commandTester;
    private int $exitCode;

    public function __construct(KernelInterface $kernel)
    {
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }

    /**
     * @When I run the console command :commandName
     */
    public function iRunTheConsoleCommand(string $commandName): void
    {
        echo "Launching console command: {$commandName}\n";

        $command = $this->application->find($commandName);
        $this->commandTester = new CommandTester($command);
        $this->exitCode = $this->commandTester->execute([]);
    }

    /**
     * @Then the command should succeed
     */
    public function theCommandShouldSucceed(): void
    {
        if ($this->exitCode !== Command::SUCCESS) {
            $output = $this->commandTester->getDisplay();
            throw new \RuntimeException(sprintf(
                "Command failed with exit code %d. Output:\n%s",
                $this->exitCode,
                $output
            ));
        }
    }

    /**
     * @Then the command should fail
     */
    public function theCommandShouldFail(): void
    {
        if ($this->exitCode === Command::SUCCESS) {
            $output = $this->commandTester->getDisplay();
            throw new \RuntimeException(sprintf(
                "Command unexpectedly succeeded. Output:\n%s",
                $output
            ));
        }
    }

    /**
     * @Then I should see :text in the output
     */
    public function iShouldSeeInTheOutput(string $text): void
    {
        $output = $this->commandTester->getDisplay();
        if (strpos($output, $text) === false) {
            throw new \RuntimeException(sprintf(
                "Expected to see '%s' in output, but got:\n%s",
                $text,
                $output
            ));
        }
    }
}