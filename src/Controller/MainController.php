<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Predis\Client;
use Exception;

class MainController extends AbstractController
{
    private $serviceName;
    private $version;
    private $description;
    private $methods;
    private $host;
    private $logger;
    private $referer;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->host = $_SERVER['HTTP_HOST'];
        $this->referer = $_SERVER['REMOTE_ADDR'];
        $this->serviceName = $_ENV['APP_NAME'];
        $this->version = $_ENV['APP_VERSION'];
        $this->description = $_ENV['APP_DESCRIPTION'];
        $this->logger = $logger;
        $this->methods = [
            "generateUrl"=> [
                "URL"=> "/generate",
                "HTTP-Method" => "POST",
                "Request body" => [
                    "url" => "your long url",
                    "short_uri" => "your desired short uri to be set(optional)"
                ],
                "Response"=> "http://$this->host/{short_uri}"
            ],
            "getUrl"=> [
                "URL"=> "/get/{generated_url}",
                "HTTP-Method"=> "GET",
                "Response"=> "{your_url}"
            ]
        ];
    }

    public function generate(Request $request)
    {
        try {
            // checking request method, expecting only POST
            $method = $request->getMethod();
            if ($method !== 'POST') {
                throw new Exception("Wrong method, you should use POST to generate URLs");
            }

            // checking request body json format
            $body = json_decode($request->getContent(), true);
            if ($body === null || $request->headers->get('content-type') !== 'application/json') {
                throw new Exception("Content-Type of request body should be an application/json");
            }

            // splice protocol definition
            $regexp = '/https?:\/\//iu';
            $urlToStore = key_exists('url', $body) ? preg_replace($regexp, '', $body['url']) : '';
            $uriToStore = key_exists('short_uri', $body) ? $body['short_uri'] : '';
            // validating url evailability
            $ch = curl_init($urlToStore);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            if ($responseCode < 200 || $responseCode >= 400) {
                throw new Exception("Whoops! Your URL is unreachable, or you don't specify 'url' key in request body");
            }
            curl_close($ch);
            $shortUri = $this->storeUrlToRedis($urlToStore, $uriToStore);
            return new JsonResponse(
                [
                "GeneratedUrl" => "http://$this->host/$shortUri",
                "uri" => "$shortUri"
                ]
            );
        } catch (Exception $e) {
            $this->logging($e->getMessage());
            return new JsonResponse(
                [
                "Error" => $e->getMessage()
                ]
            );
        }
    }
    
    private function storeUrlToRedis(string $urlToStore, string $uriToStore = '')
    {
        $redisClient = new Client();
        if (empty($uriToStore)) {
            $uriToStore = substr(md5($urlToStore), 0, 8);
        } else {
            $url = $redisClient->hget($uriToStore, "url");
            if (!empty($url)) {
                throw new Exception("Sorry, your short_uri is already exists, try another");
            }
        }
        
        $redisClient->hmset($uriToStore, "url", $urlToStore, "short", $uriToStore);
        $redisClient->expire($uriToStore, strtotime("+15 days"));
        $shortUri = $redisClient->hget($uriToStore, "short");
        $redisClient->bgsave();
        $redisClient->quit();
        return $shortUri;
    }

    public function redirectTo($previouslyStoredUri)
    {
        try {
            $redisClient = new Client();
            $where = $redisClient->hget($previouslyStoredUri, "url");
            if (empty($where)) {
                throw new Exception("Wrong URL! Generate Short URL first");
            }
            $redisClient->hincrby($previouslyStoredUri, "count", 1);
            $redisClient->bgsave();
            $redisClient->quit();
        } catch (Exception $e) {
            $this->logging($e->getMessage());
            return new JsonResponse(
                [
                "Error" => $e->getMessage()
                ]
            );
        }
        
        return $this->redirect("http://$where");
    }


    private function logging(string $message)
    {
        return $this->logger->error("RequestFrom:[$this->referer]:$message");
    }

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
