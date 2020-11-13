<?php

namespace App\Coordinates;

class Coordinates
{
    const DEGREE_IN_METERS = 111153;

    private float $latitude;
    private float $longitude;

    function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    static function parseCoordinates(String $value):?self
    {
        if (preg_match_all('/\d{2}.\d{6}/', $value, $arr) == 2)
            return new Coordinates($arr[0][0], $arr[0][1]);
        else
            return null;
    }

    function inArray():array
    {
        return ['latitude' => $this->latitude, 'longitude' => $this->longitude];
    }

    function getSquareCoordinates(int $radius):array
    {
        $meridianLengthInDegrees = $radius / self::DEGREE_IN_METERS;
        $parallelLengthInDegrees = $radius / (self::DEGREE_IN_METERS * cos(deg2rad($this->latitude)));
        $toY = $this->latitude + $meridianLengthInDegrees;
        $fromY = $this->latitude - $meridianLengthInDegrees;
        $fromX = $this->longitude - $parallelLengthInDegrees;
        $toX = $this->longitude + $parallelLengthInDegrees;

        return ['from_X' => $fromX, 'to_X' => $toX, 'from_Y' => $fromY, 'to_Y' => $toY];
    }

    function getByAccuracy(int $accuracy):array
    {
        return ['latitude' => round($this->latitude, $accuracy, PHP_ROUND_HALF_DOWN),
                'longitude' => round($this->longitude, $accuracy, PHP_ROUND_HALF_DOWN)];
    }

}