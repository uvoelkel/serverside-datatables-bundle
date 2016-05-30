<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class ActionsColumn extends UnboundColumn
{
    public function __construct($name, array $actions, array $options = [])
    {
        $callback = function ($data) use ($actions, $options) {

            $result = '';

            $isDropdown = isset($options['dropdown']) && (true === $options['dropdown']);
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
            } else {
                $result .= '<div class="btn-group btn-group-xs">';
            }

            /** @var \Symfony\Component\Routing\RouterInterface $router */
            $router = $this->getTable()->get('router');

            foreach ($actions as $action => $settings) {
                $url = null;

                if (isset($settings['callback'])) {
                    $url = call_user_func($settings['callback'], $data, $router);
                } elseif (isset($settings['route']) && method_exists($data, 'getId')) {
                    $url = $router->generate($settings['route'], ['id' => $data->getId()]);
                }

                if (!is_string($url)) {
                    continue;
                }

                $label = isset($settings['label']) ? $settings['label'] : $action;
                $title = isset($settings['title']) ? $settings['title'] : $label;

                if ($isDropdown) {
                    $link = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a> ';
                    $result .= '<li>' . $link . '</li>';
                } else {
                    $link = '<a class="btn btn-default" href="' . $url . '" title="' . $title . '">' . $label . '</a> ';
                    $result .= $link;
                }
            }

            if ($isDropdown) {
                $result .= '</ul></div>';
            } else {
                $result .= '</div>';
            }

            return $result;
        };

        parent::__construct($name, $callback, $options);
    }
}
