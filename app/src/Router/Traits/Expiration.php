<?php

namespace App\Router\Traits;

use App\Configuration\Configuration;
use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;

trait Expiration
{
    /**
     *  Get expiration date
     */
    private function getExpiry(array $file): DateTimeInterface
    {
        $interval = $this->container->get(Configuration::class)->get('files.ttl');
        $tz = new DateTimeZone('GMT');

        if (empty($interval)) {
            return (new DateTime('+1 year'))->setTimezone($tz);
        }

        if ($interval instanceof DateInterval === false) {
            if (substr($interval, 0, 1) === 'P') {
                $interval = new DateInterval($interval);
            } else {
                $interval = DateInterval::createFromDateString($interval);
            }
        }

        return DateTime::createFromFormat('U', $file['timestamp'])->add($interval)->setTimezone($tz);
    }
}
