<?php

namespace Voelkel\DataTablesBundle\DataTables;

use Voelkel\DataTablesBundle\Table\Localization;

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
                return $data->format(Localization::get('datetime'));
            }

            if (method_exists($data, '__toString')) {
                return $data->__toString();
            }

            return get_class($data);
        } elseif (is_bool($data)) {
            return $data ? Localization::get('true') : Localization::get('false');
        }

        return $data;
    }
}
