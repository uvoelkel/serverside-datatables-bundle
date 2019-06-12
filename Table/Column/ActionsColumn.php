<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class ActionsColumn extends UnboundColumn
{
    protected $actions = [];

    public function __construct($name, array $actions, array $options = [])
    {
        $this->actions = $actions;
        parent::__construct($name, [$this, 'callback'], $options);
    }

    public function callback($data)
    {
        $options = $this->getOptions();
        $actions = [];
        foreach ($this->actions as $name => $settings) {
            if (!isset($settings['url'])) {
                $settings['url'] = $this->getActionUrl($settings, $data);
            }

            if (!is_string($settings['url'])) {
                continue;
            }

            if (!isset($settings['method'])) {
                $settings['method'] = 'get';
            }

            $actions[$name] = $settings;
        }

        $result = '';

        $isDropdown = isset($options['dropdown']) && (true === $options['dropdown']) && 1 < sizeof($actions);
        if ($isDropdown) {
            $default = null;
            foreach ($actions as $action => $settings) {
                if (isset($settings['default']) && true === $settings['default']) {
                    $default = $action;
                    break;
                }
            }

            $label = isset($options['dropdown_label']) ? $options['dropdown_label'] : 'Action';

            $result .= '<div class="btn-group">';

            if (null === $default) {
                $result .= '<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
                           $label .' <span class="caret"></span>' .
                           '</button>';
            } else {
                $result .= '<a class="btn btn-primary btn-xs" href="' . $actions[$default]['url'] . '">' . $actions[$default]['label'] . '</a>' .
                           '<button type="button" class="btn btn-primary btn-xs dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
                           '<span class="caret"></span>' .
                           '</button>';
            }

            $result .= '<ul class="dropdown-menu dropdown-menu-right">';
        } else {
            $result .= '<div class="btn-group btn-group-xs">';
        }

        foreach ($actions as $action => $settings) {
            $url    = $settings['url'];
            $label  = isset($settings['label']) ? $settings['label'] : $action;
            $title  = isset($settings['title']) ? $settings['title'] : $label;
            $click  = isset($settings['onclick']) ? 'onclick="' . htmlspecialchars($settings['onclick']) . '"' : '';
            $target = isset($settings['target']) ? 'target="' . $settings['target'] . '"' : '';

            if ($isDropdown) {
                if (!isset($settings['default']) || false === $settings['default']) {
                    $link = '<a href="' . $url . '" class="dropdown-item" title="' . $title . '" ' . $click . ' ' . $target . '>' . $label . '</a> ';
                    $result .= '<li>' . $link . '</li>';
                }
            } else {
                $link = '<a class="btn btn-primary" href="' . $url . '" title="' . $title . '" ' . $click . ' ' . $target . '>' . $label . '</a> ';
                $result .= $link;
            }
        }

        if ($isDropdown) {
            $result .= '</ul></div>';
        } else {
            $result .= '</div>';
        }

        return $result;
    }

    private function getActionUrl($settings, $data)
    {
        $url = null;

        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $router = $this->getTable()->get('router');

        if (isset($settings['callback'])) {
            $url = call_user_func($settings['callback'], $data, $router);
        } elseif (isset($settings['route']) && method_exists($data, 'getId')) {
            $url = $router->generate($settings['route'], ['id' => $data->getId()]);
        } elseif (isset($settings['url'])) {
            $url = $settings['url'];
        } elseif (isset($settings['onclick'])) {
            $url = '#';
        }

        return $url;
    }
}
