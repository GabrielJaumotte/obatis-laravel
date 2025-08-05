<?php

// app/Http/Controllers/TestGuzzleController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TestGuzzleController extends Controller
{
    public function testGuzzle()
    {
        $client = new Client();
        $response = $client->get('https://api.github.com');

        // Retourne le code HTTP (doit être 200 si tout marche)
        return response()->json([
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getBody()->getContents(), true)
        ]);
    }
}
