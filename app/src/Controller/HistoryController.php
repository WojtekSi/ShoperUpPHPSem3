<?php

namespace App\Controller;

use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HistoryController extends AbstractController
{

    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/mainpage', name: 'mainpage', methods: ['GET', 'POST'])]
    public function mainPage(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('GET')) {

            $lastResult = $entityManager->getRepository(History::class)->findOneBy([], ['id' => 'DESC']);

            if ($lastResult === null){
                return $this->render('search/mainPage.html.twig', ['isLastResult' => false , 'lastResultLocation' => '', 'lastResultDistance' => '', 'isErrorSearch' => false,
                    'Error' => '']);
            }

            $lastResultLocation = $lastResult->getLocation();
            $lastResultDistance = $lastResult->getDistance();

            return $this->render('search/mainPage.html.twig', ['isLastResult' => true ,'lastResultLocation' => $lastResultLocation, 'lastResultDistance' => $lastResultDistance, 'isErrorSearch' => false,
            'Error' => '']);
        }else{
            $locationName = $request->request->get('lokalizacja');

            $history = new History();
            $history->setLocation($locationName);
            


            $apiUrlForDecoding = $history->apiUrlForDecoding( $locationName );
            $requestTypeForDecoding = $history->getRequestTypeForDecoding();
            $responseForDecoding = $this->httpClient->request($requestTypeForDecoding, $apiUrlForDecoding);

            if($responseForDecoding->getStatusCode() !== 200){
                //tutaj można wyłapać wiele inncyh błędów lecz na potrzeby zadania nie wnikam co się jeszcze może wydarzyć, np request będzie mieć status 200 ale nie zwróci coś innengo niż współrzędne geograficzne których sie spodziewam 
                return $this->render('search/mainPage.html.twig', [
                    'isLastResult' => false ,
                    'lastResultLocation' => '',
                    'lastResultDistance' => '',
                    'isErrorSearch' => true,
                    'Error' => 'Błąd w rozkodowywaniu lokalizacji, prawdopodobnie podałeś nieprawidłową nazwę, spróbuj jeszcze raz'
                ]);
            }

            $responseForDecodingInArray = $responseForDecoding->toArray();
            $geographicalCoordinates = $responseForDecodingInArray['items'][0]['position']['lat'] . ',' . $responseForDecodingInArray['items'][0]['position']['lat'] ;
                        

            $apiUrlForSearchDistance = $history->apiUrlForSearchDistance( $geographicalCoordinates );
            $requestTypeForSearchDistance =  $history->getRequestTypeForSearchDistance();
            
            $responseForSearchDistance = $this->httpClient->request($requestTypeForSearchDistance, $apiUrlForSearchDistance);

            if($responseForSearchDistance->getStatusCode() !== 200){
                //tutaj również można wyłapać wiele inncyh błędów lecz na potrzeby zadania nie wnikam co się jeszcze może wydarzyć
                return $this->render('search/mainPage.html.twig', [
                    'isLastResult' => false ,
                    'lastResultLocation' => '',
                    'lastResultDistance' => '',
                    'isErrorSearch' => true,
                    'Error' => 'Błąd w obliczaniu odległości między siedzibą Shoper a podaną lokalizacją - spróbuj z inną lokalizacją'
                ]);
            }
            
            $responseForSearchDistanceInArray = $responseForSearchDistance->toArray();

            $distance = $responseForSearchDistanceInArray['routes'][0]['sections'][0]['summary']['length'];

            $distanceRounded = substr($distance, 0, 3);



            $history->setDistance($distanceRounded);
            



            $entityManager->persist($history);
            $entityManager->flush();

            return $this->redirectToRoute('mainpage');
        }


    }

    #[Route('/history', name: 'history', methods: 'GET')]
    public function history(EntityManagerInterface $entityManager): Response
    {
        $history = $entityManager->getRepository(History::class)->findAll();

        return $this->render('search/history.html.twig', ['history' => $history]);

    }
    
}
