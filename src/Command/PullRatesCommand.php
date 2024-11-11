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
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsCommand(
    name: 'app:pull-rates',
    description: 'Pull rates from specified source or from default one',
)]
#[AsPeriodicTask('1 day', schedule: 'default')]
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
        $rateSourceName = $input->getArgument('rateSourceName');

        if($rateSourceName) {
            $rateSources = $this->entityManager->getRepository(RateSource::class)->findBy(['name' => $rateSourceName]);
        } else {
            $rateSources = $this->entityManager->getRepository(RateSource::class)->findAll();
        }

        foreach ($rateSources as $rateSource) {
            $service = $this->pullRatesFactory->getService($rateSource->getName());
            $rates = $service->fetchRates();
            $service->saveRates($rates);
        }

        return Command::SUCCESS;
    }
}
