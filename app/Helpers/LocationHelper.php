<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

class LocationHelper
{
    private const string HAVERSINE = '(6371000
                    * acos(cos(radians(%1$s))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(%2$s))
                    + sin(radians(%1$s)) * sin(radians(latitude)))
                )';

    public static function nearbyQueryFilter(string|Builder $model, $latitude, $longitude, $radius = 10)
    {
        if (!$model instanceof Builder) {
            $query = $model::select('*');
        } else {
            $query = $model->select('*');
        }
        $query->selectRaw(sprintf(self::HAVERSINE, $latitude, $longitude) . ' AS distance');
        $query->whereRaw(sprintf(self::HAVERSINE, $latitude, $longitude). ' < ' . $radius);

        return $query;
    }

    public static function distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo): float
    {
        $theta = $longitudeFrom - $longitudeTo;
        $distance = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo))
            + cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        return round($distance * 1609.344, 2);
    }
}
