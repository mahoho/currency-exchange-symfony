<?php

namespace App\Command;

use App\Entity\RateSource;
use App\Service\PullRates\PullRatesFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:pull-rates',
    description: 'Pull rates from specified source or from default one',
)]
class PullRatesCommand extends Command {
    private PullRatesFactory $pullRatesFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(PullRatesFactory $pullRatesFactory, EntityManagerInterface $entityManager) {
        $this->pullRatesFactory = $pullRatesFactory;

        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void {
        $this->addArgument('rateSourceName', InputArgument::OPTIONAL, 'Rate Source Name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $rateSourceName = $input->getArgument('rateSourceName');

        if(!$rateSourceName) {
            $rateSource = $this->entityManager->getRepository(RateSource::class)->findOneBy(['isDefault' => 1]);

            if(!$rateSource) {
                $io->error('No default rate source is configured');
                return Command::FAILURE;
            }

            $rateSourceName = $rateSource->getName();
        }

        $service = $this->pullRatesFactory->getService($rateSourceName);
        $rates = $service->fetchRates();
        $service->saveRates($rates);

        return Command::SUCCESS;
    }
}
