<?php

namespace App\Command\Api;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DocsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('api:docs')
            ->setDescription('Generates OpenAPI docs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $swagger = 'vendor/bin/openapi';
        $source = 'src/Controller';
        $target = 'public/docs/openapi.json';

        $process = new Process([PHP_BINARY, $swagger, $source, '--output', $target]);
        $process->run(static function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        $output->writeln('<info>Done!</info>');
    }
}
