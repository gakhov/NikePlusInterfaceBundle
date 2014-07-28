<?php
/**
 *
 * Error Codes: 401 - 407
 */
namespace AG\NikePlusInterfaceBundle\Nike;

use SimpleXMLElement;
use OAuth\OAuth2\Service\Nike as ServiceInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use AG\NikePlusInterfaceBundle\Nike\Exception as NikeException;

/**
 * Class EndpointGateway
 *
 * @package AG\NikePlusInterfaceBundle\Nike
 *
 * @since 0.0.1
 */
class EndpointGateway
{
    /**
     * @var ServiceInterface
     */
    protected $service;
    /**
     * @var array $configuration
     */
    protected $configuration;

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Set Nike+ service
     *
     * @access public
     *
     * @param ServiceInterface $service
     * @return self
     */
    public function setService(ServiceInterface $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Make an API request
     *
     * @access protected
     * @version 0.0.1
     *
     * @param string $resource Endpoint after '.../v1/'
     * @param string $method ('GET', 'POST', 'PUT', 'DELETE')
     * @param array $body Request parameters
     * @param array $extraHeaders Additional custom headers
     * @throws NikeException
     * @return object The result as an object
     */
    protected function makeApiRequest($resource, $method = 'GET', $body = array(), $extraHeaders = array())
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('API Request', 'Nike+ API');

        $path = $resource;

        if ($method == 'GET' && !empty($body)) {
            $path .= '?' . http_build_query($body);
            $body = array();
        }

        try
        {
            $response = $this->service->request($path, $method, $body, $extraHeaders);
        }
        catch (\Exception $e)
        {
            throw new NikeException('The service request failed.', 401, $e);
        }

        try
        {
            $response = $this->parseResponse($response);
        }
        catch (\Exception $e)
        {
            throw new NikeException('The response from Nike+ could not be interpreted.', 402, $e);
        }
        $timer->stop('API Request');
        return $response;
    }

    /**
     * Parse json response.
     *
     * @access private
     *
     * @param string $response
     * @throws NikeException
     * @return mixed stdClass.
     */
    private function parseResponse($response)
    {
        try
        {
            $response = json_decode($response);
        }
        catch (\Exception $e)
        {
            throw new NikeException('Could not decode JSON response.', 403);
        }
        return $response;
    }
}
