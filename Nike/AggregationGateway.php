<?php
/**
 *
 * Error Codes: 701
 */
namespace AG\NikePlusInterfaceBundle\Nike;

use AG\NikePlusInterfaceBundle\Nike\Exception as NikeException;

/**
 * Class ActivityStatsGateway
 *
 * @package Nibynool\FitbitInterfaceBundle\Fitbit
 *
 * @since 0.0.1
 */
class AggregationGateway extends EndpointGateway
{
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Provides summary data for a user's achievements
     *
     * @access public
     * @version 0.0.1
     *
     * @throws NikeException
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getAggregation()
    {
        try
        {
            return $this->makeApiRequest('me/sport');
        }
        catch (\Exception $e)
        {
            throw new NikeException('Unable to get aggregation data.', 701, $e);
        }
    }
}
