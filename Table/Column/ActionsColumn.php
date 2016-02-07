<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class ActionsColumn extends UnboundColumn
{
    public function __construct($name, array $actions, array $options = [])
    {
        $callback = function ($data) use ($actions) {

            //$result = '<ul>';
            $result = '';
            foreach ($actions as $action => $settings) {
                $url = call_user_func($settings['callback'], $data);
                if (!is_string($url)) {
                    continue;
                }

                $title = isset($settings['title']) ? $settings['title'] : $action;

                //$result .= '<li>';
                $result .= '<a href="' . $url . '" title="' . $title . '">' . $settings['label'] . '</a> ';
                //$result .= '</li>';
            }
            //$result .= '</ul>';

            return $result;
        };

        parent::__construct($name, $callback, $options);
    }
}
