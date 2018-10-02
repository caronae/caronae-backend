<?php

namespace Caronae\Services;


use Carbon\Carbon;
use Caronae\Models\User;

class ValidateDuplicateService
{

    /**
     * @var User
     */
    private $user;

    /**
     * @var \DateTime[]
     */
    private $searchRange;

    /**
     * @var boolean
     */
    private $going;
    /**
     * @var Carbon
     */
    private $dateTime;

    /**
     * ValidateDuplicateService constructor.
     * @param User $user
     * @param Carbon $dateTime
     * @param $going
     */
    public function __construct(User $user, Carbon $dateTime, $going)
    {
        $this->user = $user;
        $this->searchRange = $this->getSearchRange($dateTime);
        $this->going = $going;
        $this->dateTime = $dateTime;
    }

    public function validate()
    {
        $searchDate = $this->dateTime;

        $ridesFound = $this->user->offeredRides()
            ->whereBetween('date', $this->searchRange)
            ->where('going', $this->going)
            ->get();

        if (count($ridesFound) > 0) {
            $valid = false;

            $duplicated = $ridesFound->reduce(function ($duplicated, $ride) use ($searchDate) {
                return $duplicated || $ride->isAroundDate($searchDate);
            }, false);

            if ($duplicated) {
                $status = 'duplicate';
                $message = 'The user has already offered a ride on the specified date.';
            } else {
                $status = 'possible_duplicate';
                $message = 'The user has already offered a ride too close to the specified date.';
            }
        } else {
            $valid = true;
            $status = 'valid';
            $message = 'No conflicting rides were found close to the specified date.';
        }

        return [
            'valid' => $valid,
            'status' => $status,
            'message' => $message
        ];
    }

    public function getSearchRange(Carbon $dateTime)
    {
        $dateMin = $dateTime->copy()->setTime(0,0,0)->max(Carbon::now());
        $dateMax = $dateTime->copy()->setTime(23,59,59);
        return [$dateMin, $dateMax];
    }
}
