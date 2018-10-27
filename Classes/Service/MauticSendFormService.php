<?php
declare(strict_types=1);
namespace Bitmotion\MarketingAutomationMautic\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\SingletonInterface;

class MauticSendFormService implements SingletonInterface
{

    /**
     * @param string $url
     * @param array $data
     * @return int
     */
    public function submitForm(string $url, array $data): int
    {
        $client = new Client();


        $multipart = [];

        $headers = $this->makeHeaders();
        $cookies = $this->makeCookies();
        $this->makeMultipart($multipart, 'mauticform', $data);

        $result = null;

        try {
            $result = $client->request('POST', $url, [
                'cookies' => $cookies,
                'headers' => $headers,
                'multipart' => $multipart
            ]);
        } catch(\Exception $e){
            /** @var ResponseInterface $result */
            $result = $e->getResponse();
            if($result !== null) {
                return (int) $result->getStatusCode();
            }
            return 500;
        }

        $statusCode = $result->getStatusCode();
        return (int) $statusCode;
    }

    private function makeCookies(): CookieJar
    {
        $cookies = new CookieJar();
        $this->addCookies($cookies,'mtc_id');
        $this->addCookies($cookies,'mtc_sid');
        $this->addCookies($cookies,'mautic_device_id');
        $this->addCookies($cookies,'mautic_session_id');
        return $cookies;
    }

    /**
     * @param CookieJar $cookies
     * @param string $cookieName
     */
    private function addCookies(CookieJar $cookies, string $cookieName)
    {
        if(array_key_exists($cookieName, $_COOKIE)) {
            $cookies->setCookie(new SetCookie([
                'Name' => $cookieName,
                'value' => $_COOKIE[$cookieName]
            ]));
        }
    }


    /**
     * @return array
     */
    private function makeHeaders(): array
    {
        $headers = [];
        if (isset($_SERVER['HTTP_REFERER'])) {
            $headers['Referer'] = $_SERVER["HTTP_REFERER"];
        }


        $ip = $this->getIpFromServer();
        if($ip !== '') {
            $headers['X-Forwarded-For'] = $ip;
            $headers['Client-Ip'] = $ip;
        }
        return $headers;
    }

    /**
     * Guesses IP address from $_SERVER
     *
     * @return string
     */
    public function getIpFromServer(): string
    {
        $ip = '';
        $ipHolders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHolders as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    // Multiple IPs are present so use the last IP which should be
                    // the most reliable IP that last connected to the proxy
                    $ips = explode(',', $ip);
                    $ips = array_map('trim', $ips);
                    $ip = end($ips);
                }
                $ip = trim($ip);
                break;
            }
        }

        return $ip;
    }

    /**
     * @param array $multipart
     * @param string $path
     * @param array $data
     */
    private function makeMultipart(array &$multipart, string $path, array $data)
    {
        foreach ($data as $key => $value) {
            $tempPath = $path. '[' . $key . ']';
            if(is_array($value)) {
                $this->makeMultipart($multipart, $tempPath, $value);
            } else {
                $multipart[] = [
                    'name'     => $tempPath,
                    'contents' => $value
                ];
            }
        }
    }
}
