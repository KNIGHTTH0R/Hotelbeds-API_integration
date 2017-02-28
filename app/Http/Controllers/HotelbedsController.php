<?php

namespace App\Http\Controllers;

use DateTime;
use hotelbeds\hotel_api_sdk\helpers\Availability;
use hotelbeds\hotel_api_sdk\HotelApiClient;
use hotelbeds\hotel_api_sdk\model\Geolocation;
use hotelbeds\hotel_api_sdk\model\Occupancy;
use hotelbeds\hotel_api_sdk\model\Stay;
use hotelbeds\hotel_api_sdk\model\Pax;
use hotelbeds\hotel_api_sdk\types\ApiVersion;
use hotelbeds\hotel_api_sdk\types\ApiVersions;
use hotelbeds\hotel_api_sdk\types\HotelSDKException;

class HotelbedsController extends Controller
{

    public function getLocation($address)
    {
        // Google HQ
        $prepAddr = str_replace(' ', '+', $address);
        $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
        return json_decode($geocode);
    }


    public function index()
    {
        $location = $this->getLocation("Dublim, Ireland");

        $latitude = $location->results[0]->geometry->location->lat;
        $longitude = $location->results[0]->geometry->location->lng;

        $apiClient = new HotelApiClient("https://api.test.hotelbeds.com/hotel-content-api/1.0/hotels",
            "uwqg5du6varc5tztdhjuzsw9",
            "6AvrhP5bqG",
            new ApiVersion(ApiVersions::V1_0),
            "120");

        $rqData = new Availability();
        $rqData->stay = new Stay(DateTime::createFromFormat("Y-m-d", "2017-03-01"),
            DateTime::createFromFormat("Y-m-d", "2017-03-10"));


        //$rqData->hotels = [ "hotel" => [ 48771 ] ];
        //$rqData->destination = new Destination("PMI");

        $geolocation = new Geolocation();

        $geolocation->latitude = $latitude;
        $geolocation->longitude = $longitude;
        $geolocation->radius = 5.0;
        $geolocation->unit = Geolocation::KM;

        $rqData->geolocation = $geolocation;

        $occupancy = new Occupancy();
        $occupancy->adults = 2;
        $occupancy->children = 0;
        $occupancy->rooms = 1;

        $rqData->occupancies = [$occupancy];

        try {
            $availRS = $apiClient->Availability($rqData);
        } catch (HotelSDKException $e) {
            $auditData = $e->getAuditData();
            print_r($e->getMessage());
            print_r("Audit remote data = " . json_encode($auditData->toArray()));
            exit();
        } catch (Exception $e) {
            print_r($e->getMessage());
            exit();
        }


        // Check availability is empty or not!
        if (!$availRS->isEmpty()) {

            $arrhoteis = $availRS->hotels->toArray();


            //print_r($arrhoteis);

            for ($i = 0; $i < count($arrhoteis["hotels"]); $i++) {
                //HOTEL DETAILS
                print_r($arrhoteis["hotels"][$i]["name"] . "<br>"); // hotel name
                print_r($arrhoteis["hotels"][$i]["categoryName"] . "<br>"); // hotel name
                print_r($arrhoteis["hotels"][$i]["zoneName"] . "<br>"); // hotel name

                for ($y = 0; $y < count($arrhoteis["hotels"][$i]["rooms"]); $y++) {
                    echo "-- <br>";
                    // ROOMS DETAILS
                    print_r($arrhoteis["hotels"][$i]["rooms"][$y]["name"] . "<br>"); // rooms name
                    print_r($arrhoteis["hotels"][$i]["rooms"][$y]["rates"]["0"]["net"] . "<br>"); // total price
                    echo "--";
                }
                echo "<hr><br>";
            }


        } else {
            echo "There are no results!";
        }

    }
}
