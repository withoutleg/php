<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Coordinates\Coordinates;
use App\Repository\EstablishmentRepository;
use App\DataManager\DataManager;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class PublicController extends AbstractController
{
    /**
     * @Route("/get_establishment_by", name="get_establishment_by")
     */
    public function get_establishment_by(Request $request, EstablishmentRepository $establishmentRepository, AdapterInterface $cachePool): Response
    {
        if ($request->query->has('coordinates')){
            $coordinates_raw = $request->query->get('coordinates');
            $radius = $request->query->get('radius', 10000);

            $coordinates = Coordinates::parseCoordinates($coordinates_raw);
            if ($coordinates === null)
                return new Response("<html><body>Wrong coordinates</body></html>");

            $dataManager = new DataManager($establishmentRepository, $cachePool);

            $establishments_raw = $dataManager->findByCoordinates($coordinates, $radius);

            $establishments_response = '';
            foreach ($establishments_raw as $establishment_raw)
                    $establishments_response .= $establishment_raw->getId().
                        ':'.$establishment_raw->getName().
                        ':'.$establishment_raw->getDistance().
                        '</br>';

            return new Response($establishments_response);
        }else{
            return new Response("<html><body>Coordinates didn't find in your query</body></html>");
        }

    }

    /**
     * @Route("/push_test_data", name="push_test_data")
     */
    public function push_test_data(Request $request):Response
    {
        $amount = $request->query->get('amount', 10000);

        $entityManager = $this->getDoctrine()->getManager();

        $names = ['Shop Addidas', 'A1', 'Cafe', 'Dima', 'Denis', 'Feltching house', 'Sex shop', 'Mazda shop'];
        $latitude_from = 53.8000;
        $latitude_to = 54.0000;
        $longitude_from = 27.4000;
        $longitude_to = 27.7000;

        for($i = 0; $i < $amount; ++$i) {

            if (($i % 2) === 0){
                $latitude = (float)rand() / (float)getrandmax() + $latitude_from;
                $longitude = (float)rand() / (float)getrandmax() + $longitude_from;
            }else{
                $latitude = (float)rand() / (float)getrandmax() + (float)rand($latitude_from, $latitude_to);
                $longitude = (float)rand() / (float)getrandmax() + (float)rand($longitude_from, $longitude_to);
            }

            $name = $names[rand(0, count($names) - 1)];

            $establishment = new Establishment();
            $establishment->setName($name);
            $establishment->setLatitude($latitude);
            $establishment->setLongitude($longitude);

            $entityManager->persist($establishment);

            if (($i % 100) === 0){
                $entityManager->flush();
                $entityManager->clear();
            }
            unset($establishment);
        }
        $entityManager->flush();
        $entityManager->clear();

        return new Response("<html><body>$amount</body></html>");
    }

}