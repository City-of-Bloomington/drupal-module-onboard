<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Routing;

use Symfony\Component\Routing\Route;

class Routes
{
    public function routes()
    {
        $routes  = [];
        $config  = \Drupal::config('onboard.settings');
        $types   = $config->get('onboard_types');
        if ($types) {
            $aliasManager = \Drupal::service('path.alias_manager');

            $nids = \Drupal::entityQuery('node')
                    ->condition('status', 1)
                    ->condition('type', $types)
                    ->execute();

            foreach ($nids as $nid) {
                $alias = $aliasManager->getAliasByPath("/node/$nid");
                $routes["onboard.meetings.node-$nid"] = new Route(
                    "$alias/meetings/{year}",
                    [
                        '_controller' => '\Drupal\onboard\Controller\OnBoardController::meetings',
                        '_title'      => 'Meetings',
                        'node'        => $nid,
                        'year'        => 0
                    ],
                    [
                        '_permission' => 'access content',
                        'year'        => '\d+'
                    ],
                    [
                        'parameters' => [
                            'node' => ['type'=>'entity:node']
                        ]
                    ]
                );
                $routes["onboard.legislation.node-$nid"] = new Route(
                    "$alias/legislation/{year}",
                    [
                        '_controller' => '\Drupal\onboard\Controller\OnBoardController::legislation',
                        '_title'      => 'Legislation',
                        'node'        => $nid,
                        'year'        => 0
                    ],
                    [
                        '_permission' => 'access content',
                        'year'        => '\d+'
                    ],
                    [
                        'parameters' => [
                            'node' => ['type'=>'entity:node']
                        ]
                    ]
                );
                $routes["onboard.legislation.years.node-$nid"] = new Route(
                    "$alias/legislation/archive",
                    [
                        '_controller' => '\Drupal\onboard\Controller\OnBoardController::legislationYears',
                        '_title'      => 'Archive',
                        'node'        => $nid
                    ],
                    [
                        '_permission' => 'access content'
                    ],
                    [
                        'parameters' => [
                            'node' => ['type'=>'entity:node']
                        ]
                    ]
                );
            }
        }
        return $routes;
    }
}
