<?php

namespace App\Controller;

use App\Entity\Measurement;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class WeatherApiController extends AbstractController
{
    private WeatherUtil $weatherUtil;

    public function __construct(WeatherUtil $weatherUtil)
    {
        $this->weatherUtil = $weatherUtil;
    }

    #[Route('/api/v1/weather', name: 'app_weather_api')]
    public function index(
        #[MapQueryParameter] string $city,
        #[MapQueryParameter] string $country,
        #[MapQueryParameter] string $format,
        #[MapQueryParameter('twig')] bool $twig = false
    ): Response {
        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($country, $city);

        $formattedMeasurements = array_map(fn(Measurement $m) => [
            'date' => $m->getDate()->format('Y-m-d'),
            'celsius' => $m->getCelsius(),
            'fahrenheit' => $m->getFahrenheit(),
        ], $measurements);

        if ($twig) {
            if ($format === 'csv') {
                return $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $measurements,
                ]);
            }

            elseif ($format === 'json') {
                return $this->render('weather_api/index.json.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $formattedMeasurements,
                ]);
            }
        }

        if ($format === 'csv') {
            $csvData = "city,country,date,celsius,fahrenheit\n";

            foreach ($measurements as $measurement) {
                $csvData .= sprintf(
                    "%s,%s,%s,%d,%d\n",
                    $city,
                    $country,
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getCelsius(),
                    $measurement->getFahrenheit()
                );
            }

            return new Response(
                $csvData,
                Response::HTTP_OK,
//                ['Content-Type' => 'text/csv']
            );
        }

        elseif ($format === 'json') {
            return $this->json([
                'city' => $city,
                'country' => $country,
                'measurements' => $formattedMeasurements,
            ]);
        }
        return new Response(
            'Unsupported format. Please use "json" or "csv".',
            Response::HTTP_BAD_REQUEST
        );
    }
}
