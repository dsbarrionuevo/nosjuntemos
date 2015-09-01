<?php

class Util {

    public static function date_to_big_endian($date, $lower_year = 1900, $top_year = 2100, $initial_separator = '/', $end_separator = '-') {
        if (!is_null($date) && strlen($date) == 10) {
            $parts = explode($initial_separator, $date);
            if (count($parts) != 3) {
                throw new InvalidArgumentException('Error: No están todos los datos (dia, mes o año)', 1);
            }
            $day = $parts[0];
            $month = $parts[1];
            $year = $parts[2];
            if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year)) {
                throw new InvalidArgumentException('Error: Algún dato no es entero', 2);
            }
            if (!checkdate($month, $day, $year)) {
                throw new InvalidArgumentException('Error: Algún dato no está en el intervalo correcto', 3);
            }
            $intYear = (int) $year;
            if ($intYear < $lower_year || $intYear > $top_year) {
                throw new InvalidArgumentException('Error: El anio esta fuera de los limites', 4);
            }
            $bigEndian = $year . $end_separator . $month . $end_separator . $day;
            return $bigEndian;
        } else {
            throw new InvalidArgumentException('Error: La fecha no tiene la cantidad de datos apropiados', 5);
        }
    }

    public static function date_to_little_endian($date, $lower_year = 1900, $top_year = 2100, $initial_separator = '-', $end_separator = '/') {
        if (!is_null($date) && strlen($date) == 10) {
            $parts = explode($initial_separator, $date);
            if (count($parts) != 3) {
                throw new InvalidArgumentException('Error: No están todos los datos (dia, mes o año)', 1);
            }
            $year = $parts[0];
            $month = $parts[1];
            $day = $parts[2];
            if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
                throw new InvalidArgumentException('Error: Algún dato no es entero', 2);
            }
            if (!checkdate($month, $day, $year)) {
                throw new InvalidArgumentException('Error: Algún dato no está en el intervalo correcto', 3);
            }
            $intYear = (int) $year;
            if ($intYear < $lower_year || $intYear > $top_year) {
                throw new InvalidArgumentException('Error: El anio esta fuera de los limites', 4);
            }
            $littleEndian = $day . $end_separator . $month . $end_separator . $year;
            return $littleEndian;
        } else {
            throw new InvalidArgumentException('Error: La fecha no tiene la cantidad de datos apropiados', 5);
        }
    }

}
