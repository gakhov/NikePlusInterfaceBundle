<?php
/**
 *
 * Error Codes: 601 - 618
 */
namespace AG\NikePlusInterfaceBundle\Nike;

use Symfony\Component\Stopwatch\Stopwatch;
use AG\NikePlusInterfaceBundle\Nike\Exception as NikeException;

/**
 * Class ActivityGateway
 *
 * @package AG\NikePlusInterfaceBundle\Nike
 *
 * @since 0.0.1
 */
class ActivityGateway extends EndpointGateway
{
    /**
     * Get a summary data for a list of Activities
     *
     * @access public
     * @version 0.0.1
     *
     * @param  int $count
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     * @throws NikeException
     * @return object The result as an object
     */
    public function getActivities($count = null, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Activities', 'Nike+ API');

        $body = array();
        if ($count !== null)
        {
            $body['count'] = (int)$count;
        }
        if ($startDate !== null)
        {
            $body['startDate'] = $startDate->format("Y-m-d");
        }
        if ($endDate !== null)
        {
            $body['endDate'] = $endDate->format("Y-m-d");
        }

        try
        {
            /** @var object $activities */
            $activities = $this->makeApiRequest('me/sport/activities', 'GET', $body);
            $timer->stop('Get Activities');
            return $activities;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Activities');
            throw new NikeException('Unable to get a list of activities.', 611, $e);
        }
    }

    /**
     * Get user's Sport Activities for the specified Experience Type
     *
     * @access public
     * @version 0.0.1
     *
     * @param  string $experienceType
     * @param  int $count
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     * @throws NikeException
     * @return object The result as an object
     */
    public function getActivitiesByExperience($experienceType, $count = null, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Activities by Experience', 'Nike+ API');

        $body = array();
        if ($count !== null)
        {
            $body['count'] = (int)$count;
        }
        if ($startDate !== null)
        {
            $body['startDate'] = $startDate->format("Y-m-d");
        }
        if ($endDate !== null)
        {
            $body['endDate'] = $endDate->format("Y-m-d");;
        }

        try
        {
            /** @var object $activities */
            $activities = $this->makeApiRequest('me/sport/activities/' . $experienceType, 'GET', $body);
            $timer->stop('Get Activities by Experience');
            return $activities;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Activities by Experience');
            throw new NikeException('Get Activities by Experience failed.', 602, $e);
        }
    }

    /**
     * Get details of the user's activities, specified by its Activity ID
     *
     * @access public
     * @version 0.0.1
     *
     * @param  string $activityId
     * @throws NikeException
     * @return object The result as an object
     */
    public function getActivity($activityId)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Activity', 'Nike+ API');

        try
        {
            /** @var object $activity */
            $activity = $this->makeApiRequest('me/sport/activities/' . $activityId);
            $timer->stop('Get Activity');
            return $activity;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Activity');
            throw new NikeException('Get Activity by ID failed.', 602, $e);
        }
    }

    /**
     * Get GPS data for the user's activities, specified by its Activity ID
     *
     * @access public
     * @version 0.0.1
     *
     * @param  $activityId
     * @throws NikeException
     * @return object The result as an object
     */
    public function getActivityGPS($activityId)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Get Activity GPS', 'Nike+ API');

        try
        {
            /** @var object $activity */
            $activity = $this->makeApiRequest('me/sport/activities/gps' . $activityId);
            $timer->stop('Get Activity GPS');
            return $activity;
        }
        catch (\Exception $e)
        {
            $timer->stop('Get Activity GPS');
            throw new NikeException('Get Activity GPS failed.', 602, $e);
        }
    }

    /**
     * Log user activity to an user's history
     * (Read more: https://developer.nike.com/documentation/api-docs/activity-services/add-activities.html)
     *
     * @access public
     * @version 0.0.1
     *
     * @param string $activityType The type of Activity performed.
     * @param string $deviceType Type of device the activity was recorded on.
     * @param int $startTime The Activity's start time in seconds (Unix time).
     * @param string $timeZoneName Timezone the Activity was captured in.
     * @param string $metrics Contains the metrics data for the Activity.
     * @param string $deviceName The model of the device (or the application name if using an app).
     * @throws NikeException
     * @return object The result as an object
     */
    public function addActivity($activityType, $deviceType, $startTime, $timeZoneName, $metrics, $deviceName = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Add Activity', 'Nike+ API');

        $body = array(
            'activityType' => $activityType,
            'deviceType'   => $deviceType,
            'startTime'    => $startTime,
            'timeZoneName' => $timeZoneName,
            'metrics'      => $metrics
        );

        if($deviceName !== null)
        {
            $body['deviceName'] = $deviceName;
        }

        try
        {
            /** @var object $addedActivity */
            $addedActivity = $this->makeApiRequest('me/sport/activities', 'POST', $body);
            $timer->stop('Add Activity');
            return $addedActivity;
        }
        catch (\Exception $e)
        {
            $timer->stop('Add Activity');
            throw new NikeException('Failed adding activity.', 606, $e);
        }
    }
