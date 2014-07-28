<?php
/**
 *
 * Error Codes: 101 - 112
 */
namespace AG\NikePlusInterfaceBundle\Nike;

use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\Nike as ServiceInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Client\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Stopwatch\Stopwatch;
use AG\NikePlusInterfaceBundle\Nike\Exception as NikeException;

/**
 * Class ApiGatewayFactory
 *
 * @package AG\NikePlusInterfaceBundle\Nike
 *
 * @version 0.0.1
 *
 * @method AuthenticationGateway getAuthenticationGateway()
 * @method ActivityGateway getActivityGateway()
 * @method AggregationGateway getAggregationGateway()
 */
class ApiGatewayFactory
{
    /**
     * @var string
     */
    protected $clientId;
    /**
     * @var string
     */
    protected $clientSecret;
    /**
     * @var ServiceInterface
     */
    protected $service;
    /**
     * @var TokenStorageInterface
     */
    protected $storageAdapter;
    /**
     * @var string
     */
    protected $callbackURL;
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * @var array
     */
    protected $configuration;
    /**
     * @var Router
     */
    protected $router;

    /**
     * Set the client credentials when this class is instantiated
     *
     * @access public
     *
     * @param string $clientId Client credentials provided by Nike+ for the application
     * @param string $clientSecret The application's client_secret issued by Nike+
     * @param string $callbackURL Callback URL to provide to Nike+
     * @param array  $configuration Configurable items
     * @param Router $router
     */
    public function __construct($clientId, $clientSecret, $callbackURL, $configuration, Router $router)
    {
        $this->clientId       = $clientId;
        $this->clientSecret   = $clientSecret;
        $this->callbackURL    = $callbackURL;
        $this->configuration  = $configuration;
        $this->router         = $router;
    }

    /**
     * Set client credentials
     *
     * @access public
     *
     * @param string $clientId Client credentials provided by Nike+ for the application
     * @param string $clientSecret The application's client_secret issued by Nike+
     * @return self
     */
    public function setCredentials($clientId, $clientSecret)
    {
        $this->clientId    = $clientId;
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * Set storage adapter.
     *
     * @access public
     *
     * @param TokenStorageInterface $adapter
     * @return self
     */
    public function setStorageAdapter(TokenStorageInterface $adapter)
    {
        $this->storageAdapter = $adapter;
        return $this;
    }

    /**
     * Get storage adapter.
     *
     * @access public
     *
     * @return TokenStorageInterface
     */
    public function getStorageAdapter()
    {
        return $this->storageAdapter;
    }

    /**
     * Set callback URL.
     *
     * @access public
     * @version 0.5.0
     *
     * @param string $callbackURL
     * @throws NikeException
     * @return self
     */
    public function setCallbackURL($callbackURL)
    {
        if(substr($callbackURL, 0, 1) == '/' && substr($callbackURL, 0, 2) != '//') $callbackURL = $this->router->getContext()->getBaseUrl().$callbackURL;
        if (!filter_var($callbackURL, FILTER_VALIDATE_URL)) throw new NikeException('The provided callback URL ('.$callbackURL.') is not a valid URL.', 102);
        $this->callbackURL = $callbackURL;
        return $this;
    }

    /**
     * Set HTTP Client library for Fitbit service.
     *
     * @access public
     *
     * @param  ClientInterface $client
     * @return self
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * Open a Gateway
     *
     * @access public
     * @version 0.0.1
     *
     * @param $method
     * @param $parameters
     * @throws NikeException
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Establishing Gateway', 'Nike+ API');
        if (!preg_match('/^get.*Gateway$/', $method))
        {
            throw new NikeException('Invalid API Gateway interface ('.$method.') requested.', 103);
        }
        if (count($parameters))
        {
            throw new NikeException('API Gateway interfaces do not accept parameters.', 104);
        }
        $gatewayName = '\\'.__NAMESPACE__.'\\'.substr($method, 3);
        try
        {
            $gateway = new $gatewayName($this->configuration);
        }
        catch (\Exception $e)
        {
            $timer->stop('Establishing Gateway');
            throw new NikeException('API Gateway could not open a gateway named '.$gatewayName.'.', 105);
        }
        $this->injectGatewayDependencies($gateway);
        $timer->stop('Establishing Gateway');
        return $gateway;
    }

    /**
     * Inject Dependencies into a Gateway Interface
     *
     * @access protected
     * @version 0.5.0
     *
     * @param EndpointGateway $gateway
     * @throws NikeException
     * @return bool
     */
    protected function injectGatewayDependencies(EndpointGateway $gateway)
    {
        try
        {
            $gateway->setService($this->getService());
        }
        catch (\Exception $e)
        {
            throw new NikeException('Could not inject gateway dependencies', 112, $e);
        }
        return true;
    }

    /**
     * Get Nike+ service
     *
     * @access protected
     * @version 0.0.1
     *
     * @throws NikeException
     * @return ServiceInterface
     */
    protected function getService()
    {
        if (!$this->clientId)    throw new NikeException('Cannot get service as the client id is empty.', 106);
        if (!$this->clientSecret) throw new NikeException('Cannot get service as the client secret is empty.', 107);
        if (!$this->callbackURL)    throw new NikeException('Cannot get service as the callback URL is empty.', 108);
        if (!$this->storageAdapter) throw new NikeException('Cannot get service as it is missing a storage adapter.', 109);

        if (!$this->service)
        {
            try
            {
                $credentials = new Credentials(
                    $this->clientId,
                    $this->clientSecret,
                    $this->callbackURL
                );
            }
            catch (\Exception $e)
            {
                throw new NikeException('Could not initialise the credentials.', 110, $e);
            }

            try
            {
                $factory = new ServiceFactory();
                if ($this->httpClient) $factory->setHttpClient($this->httpClient);
                $this->service = $factory->createService('Nike', $credentials, $this->storageAdapter);
            }
            catch (\Exception $e)
            {
                throw new NikeException('Could not initialise service factory.', 111, $e);
            }
        }
        return $this->service;
    }
}
