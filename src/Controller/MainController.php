<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class MainController
{
    public function storeUrl()
    {
        $var = "test";
        return new JsonResponse(
            ['data' => $var]
        );
    }
}
