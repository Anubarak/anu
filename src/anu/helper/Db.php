<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 11:49
 */

namespace anu\helper;
/**
 * Db is a helper to transform values for the database, it serializes arrays or formats DateTime objects
 *
 * @author Robin Schambach
 */
class Db{

    /**
     * Prepares a value to be sent to the database.
     *
     * @param mixed $value The value to be prepared
     *
     * @return mixed The prepped value
     */
    public static function prepareValueForDb($value)
    {
        // If the object explicitly defines its savable value, use that
        //if ($value instanceof Serializable) {
        //    return $value->serialize();
        //}

        // Only DateTime objects and ISO-8601 strings should automatically be detected as dates
        if ($value instanceof \DateTime){
            return static::prepareDateForDb($value);
        }

        // If it's an object or array, just JSON-encode it
        if (is_object($value) || is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }

    /**
     * Prepares a date to be sent to the database.
     *
     * @param mixed $date The date to be prepared
     *
     * @return string|null The prepped date, or `null` if it could not be prepared
     */
    public static function prepareDateForDb(\DateTime $date)
    {

        if ($date !== false) {
            $timezone = $date->getTimezone();
            $date->setTimezone(new \DateTimeZone('UTC'));
            $formattedDate = $date->format('Y-m-d H:i:s');
            $date->setTimezone($timezone);

            return $formattedDate;
        }

        return null;
    }
}