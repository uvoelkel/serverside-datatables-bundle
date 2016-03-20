<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class ActionsColumn extends UnboundColumn
{
    public function __construct($name, array $actions, array $options = [])
    {
        //$options['dropdown'] = false;

        $callback = function ($data, $object, ActionsColumn $column) use ($actions) {

            $result = '';

            $options = $column->getOptions();
            $isDropdown = (true === $options['dropdown']);
            if ($isDropdown) {
                $default = null;
                foreach ($actions as $action => $settings) {
                    if (isset($settings['default']) && true === $settings['default']) {
                        $default = $action;
                        break;
                    }
                }

                $result .= '<div class="btn-group">';

                if (null === $default) {
                    $result .= '<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
                        'Action <span class="caret"></span>' .
                        '</button>';
                } else {
                    $result .= '<button type="button" class="btn btn-default btn-xs">Action</button>' .
                        '<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
                        '<span class="caret"></span>' .
                        '</button>';
                }

                $result .= '<ul class="dropdown-menu dropdown-menu-right">';
            }

            foreach ($actions as $action => $settings) {
                $url = call_user_func($settings['callback'], $data);
                if (!is_string($url)) {
                    continue;
                }

                $title = isset($settings['title']) ? $settings['title'] : $action;

                if ($isDropdown) {
                    $link = '<a href="' . $url . '" title="' . $title . '">' . $settings['label'] . ' ' . $title . '</a> ';
                    $result .= '<li>' . $link . '</li>';
                } else {
                    $link = '<a href="' . $url . '" title="' . $title . '">' . $settings['label'] . '</a> ';
                    $result .= $link;
                }
            }

            if ($isDropdown) {
                $result .= '</ul></div>';
            }

            return $result;
        };

        parent::__construct($name, $callback, $options);
    }
}
