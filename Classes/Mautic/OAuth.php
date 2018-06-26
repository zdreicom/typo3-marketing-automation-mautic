<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Mautic;

use Mautic\Auth\AuthInterface;

class OAuth implements AuthInterface
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct(AuthInterface $authorization, string $baseUrl)
    {
        $this->authorization = $authorization;
        $this->baseUrl = $baseUrl;
    }

    public function __call($method, $arguments)
    {
        if (!is_callable([$this->authorization, $method])) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist!', $method), 1530044605);
        }

        return call_user_func_array([$this->authorization, $method], $arguments);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check if current authorization is still valid
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorization->isAuthorized();
    }

    /**
     * Make a request to server using the supported auth method
     *
     * @param string $url
     * @param array $parameters
     * @param string $method
     * @param array $settings
     *
     * @return array
     */
    public function makeRequest($url, array $parameters = [], $method = 'GET', array $settings = [])
    {
        return $this->authorization->makeRequest($url, $parameters, $method, $settings);
    }
}
