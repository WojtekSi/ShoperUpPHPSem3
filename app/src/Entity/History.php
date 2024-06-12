<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class History
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    public string $location;

    #[ORM\Column]
    public string $distance;

    public string $apiKey = 'iqujun3FG-wTJyq2Y38cLNlYkczp80VC3Ue1b913FNg';
    public string $appID = 'slKtKkVQ9bUgTKXkYyX5';
    public string $geographicalCoordinatesShoper = '50.06045,19.93242';
    public string $requestTypeForDecoding = 'GET';
    public string $requestTypeForSearchDistance = 'GET';



    public function apiUrlForDecoding( $locationName ){
        return "https://geocode.search.hereapi.com/v1/geocode?q={$locationName}&apiKey={$this->apiKey}" ;
    } 

    public function getRequestTypeForDecoding(){
        return $this->requestTypeForDecoding;
    }


    public function apiUrlForSearchDistance( $geographicalCoordinatesLocation ){
        return "https://router.hereapi.com/v8/routes?transportMode=car&origin={$this->geographicalCoordinatesShoper}&destination={$geographicalCoordinatesLocation}&return=summary&apiKey={$this->apiKey}" ;
    } 


    public function getRequestTypeForSearchDistance(){
        return $this->requestTypeForSearchDistance;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setDistance(string $distance): void
    {
        $this->distance = $distance;
    }

    public function getDistance(): string
    {
        return $this->distance;
    }
}

