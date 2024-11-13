<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:country-city',
    description: 'Weather for the location chosen with country code and city name',
)]
class WeatherCountryCityCommand extends Command
{
    public WeatherUtil $weatherUtil;

    public function __construct(WeatherUtil $weatherUtil)
    {
        parent::__construct();
        $this->weatherUtil = $weatherUtil;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('country', InputArgument::REQUIRED, 'Country code (e.g., "US", "PL")')
            ->addArgument('city', InputArgument::REQUIRED, 'City name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $countryCode = $input->getArgument('country');
        $city = $input->getArgument('city');

        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $city);
        $io->writeln(sprintf('Weather data for %s, %s:', $city, $countryCode));
            foreach ($measurements as $measurement) {
                $io->writeln(sprintf("\t%s: %s°C",
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getCelsius()
                ));
            }

            return Command::SUCCESS;

    }
}
