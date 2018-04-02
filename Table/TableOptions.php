<?php

namespace Voelkel\DataTablesBundle\Table;

class TableOptions implements \ArrayAccess
{
    const PAGING_TYPE_NUMBERS           = 'numbers';        //  Page number buttons only
    const PAGING_TYPE_SIMPLE            = 'simple';         // 'Previous' and 'Next' buttons only
    const PAGING_TYPE_SIMPLE_NUMBERS    = 'simple_numbers'; // 'Previous' and 'Next' buttons, plus page numbers
    const PAGING_TYPE_FULL              = 'full';           // 'First', 'Previous', 'Next' and 'Last' buttons
    const PAGING_TYPE_FULL_NUMBERS      = 'full_numbers';   // 'First', 'Previous', 'Next' and 'Last' buttons, plus page numbers

    const RESPONSIVE_DETAILS_FALSE                  = false;
    const RESPONSIVE_DETAILS_CHILD_ROW              = '$.fn.dataTable.Responsive.display.childRow';
    const RESPONSIVE_DETAILS_CHILD_ROW_IMMEDIATE    = '$.fn.dataTable.Responsive.display.childRowImmediate';

    const RESPONSIVE_DETAILS_TYPE_NONE              = '';
    const RESPONSIVE_DETAILS_TYPE_INLINE            = 'inline';
    const RESPONSIVE_DETAILS_TYPE_COLUMN            = 'column';

    static function getDefaultOptions($locale)
    {
        $options = new TableOptions();
        $options->data = [
            'processing' => true,
            'pageLength' => 25,
            'autoWidth' => false,
            'pagingType' => TableOptions::PAGING_TYPE_NUMBERS,
            'deferLoading' => null,
            'responsive' => false,
            //'lengthMenu' => '[ [ 10, 25, 50, 100 ], [ 10, 25, 50, 100 ] ]',
            //'dom' => "<'row'<'col-sm-12'pl>>" . "<'row'<'col-sm-12'<'table-responsive'tr>>>" . "<'row'<'col-xs-12'<'hr'>><'col-sm-5'i><'col-sm-7'p>>",
        ];

        if ('de' === $locale) {
            $options->data['language'] = [
                'processing' => 'Bitte warten...',
                'search' => 'Suchen',
                'lengthMenu' => '_MENU_', // _MENU_ Einträge anzeigen",
                'info' => '_START_ bis _END_ von _TOTAL_ Einträgen',
                'infoEmpty' => '0 bis 0 von 0 Einträgen',
                'infoFiltered' => '(gefiltert aus _MAX_ Einträgen)',
                'infoPostFix' => '',
                'loadingRecords' => 'Wird geladen...',
                'zeroRecords' => 'Keine Einträge vorhanden',
                'emptyTable' => 'Keine Daten in der Tabelle vorhanden',
                'paginate' => [
                    'first' => 'Erste',
                    'previous' => 'Zurück',
                    'next' => 'Weiter',
                    'last' => 'Letzte',
                ],
                'aria' => [
                    'sortAscending' => ': aktivieren, um Spalte aufsteigend zu sortieren',
                    'sortDescending' => ': aktivieren, um Spalte absteigend zu sortieren',
                ],
            ];
        }

        return $options;
    }

    private $data = [];

    public function all()
    {
        return $this->data;
    }

    public function merge($options = [])
    {
        $this->data = array_merge_recursive($this->data, $options);
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

    /**
     * @param bool $responsive
     * @param false|string $details
     * @param string $type
     */
    public function setResponsive($responsive, $details, $type)
    {
        if (false === $responsive) {
            $this->data['responsive'] = false;
            return;
        }

        $this->data['responsive'] = [
            'details' => null,
        ];

        if (false === $details) {
            $this->data['responsive']['details'] = false;
            return;
        }

        $this->data['responsive']['details'] = [
            'display' => $details,
            'type' => $type,
        ];
    }
}
