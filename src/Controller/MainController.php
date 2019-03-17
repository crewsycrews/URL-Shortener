<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Predis\Client;

class MainController
{
    private $serviceName;
    private $version;
    private $description;
    private $methods;
    private $host;
    
    public function __construct()
    {
        $this->host = $_SERVER['HTTP_HOST'];
        $this->serviceName = $_ENV['APP_NAME'];
        $this->version = $_ENV['APP_VERSION'];
        $this->description = $_ENV['APP_DESCRIPTION'];
        $this->methods = [
            "generateUrl"=> [
                "URL"=> "/generate/{your_url}",
                "HTTP-Method"=> "POST",
                "Response"=> "http://$this->host/{short_uri}"
            ],
            "generateCustomUrl"=> [
                "URL"=> "/generateCustom/{your_long_url}/{your_desired_uri}",
                "HTTP-Method"=> "POST",
                "Response"=> "http://$this->host/{your_desired_uri}"
            ],
            "getUrl"=> [
                "URL"=> "/get/{generated_url}",
                "HTTP-Method"=> "GET",
                "Response"=> "{your_url}"
            ]
        ];
    }

    public function generateUrl(Request $request)
    {
        try {
            $body = json_decode($request->getContent(), true);
            if ($body === null) {
                throw new \Exception("Content type of body should be an application/json");
            }
            $urlToStore = key_exists('url', $body) ? $body['url'] : '';
            $ch = curl_init($urlToStore);
            curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            if ($responseCode < 200 || $responseCode >= 400) {
                throw new \Exception("Whoops! Your URL is unreachable, or you don't specify url key in request body");
            }
            $redisClient = new Client();
            $redisClient->set($urlToStore, substr(md5($urlToStore), 0, 8));
            $redisClient->expire($urlToStore, strtotime("+15 days"));
            $value = $redisClient->get($urlToStore);
            $redisClient->bgsave();
            $redisClient->quit();
            return new JsonResponse(
                [
                "GeneratedUrl" => "http://$this->host/$value",
                ]
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                "Error" => $e->getMessage()
                ]
            );
        }
    }

    // public function generateCustomUrl(string $urlToStore, string $desiredUrl)
    // {
    //     $redisClient = new Client();
    //     $redisClient->set($urlToStore, $desiredUrl);
    //     $redisClient->expire($urlToStore, strtotime("+15 days"));
    //     $value = $redisClient->get($urlToStore);
    //     $redisClient->bgsave();
    //     $redisClient->quit();
    //     return new JsonResponse(
    //         [
    //         "GeneratedUrl" => "http://$this->host/$value"
    //         ]
    //     );
    // }
    
    public function mainResponse()
    {
        return new JsonResponse(
            [
            "Service" => $this->serviceName,
            "Version" => $this->version,
            "Description" => $this->description,
            "Methods" => $this->methods
            ]
        );
    }
}
