<?php
namespace App\Tests\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OfferControllerTest2 extends WebTestCase
{
    public function testGetOffersWithoutToken()
    {
        $client = new Client(['base_uri' => 'http://localhost:8000/api/']);

        $response = $client->request("GET", $client->getConfig("base_uri") . 'offers', ['http_errors' => false]);
        $this->assertEquals(403, $response->getStatusCode());

    }

    public function testGetOffersWithToken()
    {
        $client = new Client(['base_uri' => 'http://localhost:8000/api/']);

        $credential = array('email'=>'Joséphine.Deschamps@gmail.com', 'password' => 'glH?t9^)PaN?s');
        $auth = $client->request('POST', $client->getConfig("base_uri") . "login",
            [ 'headers' => ['Content-Type' => 'application/json'],
                'body'=> json_encode($credential)]);

        $token = json_decode($auth->getBody()->read(1024), true)["token"];

        $response = $client->request("GET", $client->getConfig("base_uri") . 'offers', ['http_errors' => false, 'Authorization' => "Bearer $token"]);
        $this->assertEquals(403, $response->getStatusCode());

    }

    public function testGetOneOfferWithoutToken(){
        $client = new Client(['base_uri'=> 'http://localhost:8000/api/']);

        $response = $client->request("GET", $client->getConfig("base_uri") . 'offers/183', ['http_errors'=>false]);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testGetOneOfferWithToken(){
        $client = new Client(['base_uri'=> 'http://localhost:8000/api/']);

        $credential = array('email'=>'Joséphine.Deschamps@gmail.com', 'password' => 'glH?t9^)PaN?s');
        $auth = $client->request('POST', $client->getConfig("base_uri") . "login",
            [ 'headers' => ['Content-Type' => 'application/json'],
                'body'=> json_encode($credential)]);

        $token = json_decode($auth->getBody()->read(1024), true)["token"];

        $response = $client->request("GET", $client->getConfig("base_uri").'offers/FNAC120',
            ['http_errors'=>false, "headers" => ["Authorization" => "Bearer $token"]]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetNotExistingOfferWithToken(){
        $client = new Client(['base_uri'=> 'http://localhost:8000/api/']);

        $credential = array('email'=>'Joséphine.Deschamps@gmail.com', 'password' => 'glH?t9^)PaN?s');
        $auth = $client->request('POST', $client->getConfig("base_uri") . "login",
            [ 'headers' => ['Content-Type' => 'application/json'],
                'body'=> json_encode($credential)]);

        $token = json_decode($auth->getBody()->read(1024), true)["token"];

        $response = $client->request("GET", $client->getConfig("base_uri").'offers/FNAC121',
            ['http_errors'=>false, "headers" => ["Authorization" => "Bearer $token"]]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    protected static function getKernelClass()
    {
        return \App\Kernel::class;
    }


}
