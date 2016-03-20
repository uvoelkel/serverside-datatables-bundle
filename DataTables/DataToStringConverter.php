<?php

namespace Voelkel\DataTablesBundle\DataTables;

class DataToStringConverter
{
    private $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function convertDataToString($data)
    {
        if (is_object($data)) {
            if ($data instanceof \DateTimeInterface) {
                //setlocale(LC_TIME, "de_DE");
                // strftime('%c', $data->getTimestamp());
                return $data->format('d.m.Y H:i:s');
            }

            if (method_exists($data, '__toString')) {
                return $data->__toString();
            }

            return get_class($data);
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        }

        return $data;
    }
}
