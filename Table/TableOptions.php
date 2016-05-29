<?php

namespace Voelkel\DataTablesBundle\Table;

class TableOptions implements \ArrayAccess
{
    const PAGING_TYPE_NUMBERS           = 'numbers';        //  Page number buttons only
    const PAGING_TYPE_SIMPLE            = 'simple';         // 'Previous' and 'Next' buttons only
    const PAGING_TYPE_SIMPLE_NUMBERS    = 'simple_numbers'; // 'Previous' and 'Next' buttons, plus page numbers
    const PAGING_TYPE_FULL              = 'full';           // 'First', 'Previous', 'Next' and 'Last' buttons
    const PAGING_TYPE_FULL_NUMBERS      = 'full_numbers';   // 'First', 'Previous', 'Next' and 'Last' buttons, plus page numbers

    static function getDefaultOptions()
    {
        $options = new TableOptions();
        $options->data = [
            'processing' => true,
            'pageLength' => 25,
            'autoWidth' => false,
            'pagingType' => TableOptions::PAGING_TYPE_NUMBERS,
            //'lengthMenu' => [ [ 10, 25, 50, 100 ], [ 10, 25, 50, 100 ] ],
        ];

        return $options;
    }




    private $data = [];

    public function all()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
