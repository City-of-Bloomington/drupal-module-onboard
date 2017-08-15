<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
declare (strict_types=1);
namespace Drupal\onboard\Routing;

use Drupal\onboard\OnBoardService;
use Symfony\Component\Routing\Route;

class Routes
{
    private static $legislative = [];

    public function routes()
    {
        $routes  = [];
        $config  = \Drupal::config('onboard.settings');
        $types   = $config->get('onboard_types');
        $field   = $config->get('onboard_committee_field');


        $committees = OnBoardService::committee_list();
        foreach ($committees as $c) {
            if ($c['legislative']) { self::$legislative[] = $c['id']; }
        }


        if ($types) {
            $aliasManager = \Drupal::service('path.alias_manager');
            $storage      = \Drupal::entityManager()->getStorage('node');

            $nids = \Drupal::entityQuery('node')
                    ->condition('status', 1)
                    ->condition('type', $types)
                    ->execute();
            $nodes = $storage->loadMultiple($nids);

            $legislationTypes = OnBoardService::legislation_types();

            foreach ($nodes as $node) {
                $nid          = $node->get('nid' )->value;
                $committee_id = $node->get($field)->value;

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
                    ['parameters' => ['node' => ['type'=>'entity:node']]]
                );
                $routes["onboard.meetings.years.node-$nid"] = new Route(
                    "$alias/meetings/archive",
                    [
                        '_controller' => '\Drupal\onboard\Controller\OnBoardController::meetingYears',
                        '_title'      => 'Archive',
                        'node'        => $nid
                    ],
                    ['_permission' => 'access content' ],
                    ['parameters'  => ['node' => ['type'=>'entity:node']]]
                );



                if (in_array((int)$committee_id, self::$legislative)) {
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
                        ['parameters' => ['node' => ['type'=>'entity:node']]]
                    );
                    $routes["onboard.legislation.years.node-$nid"] = new Route(
                        "$alias/legislation/archive",
                        [
                            '_controller' => '\Drupal\onboard\Controller\OnBoardController::legislationYears',
                            '_title'      => 'Archive',
                            'node'        => $nid
                        ],
                        ['_permission' => 'access content'],
                        ['parameters'  => ['node' => ['type'=>'entity:node']]]
                    );
                    foreach ($legislationTypes as $t) {
                        if (!$t['subtype']) {
                            $routes["onboard.$t[name].node-$nid"] = new Route(
                                "$alias/$t[name]/{number}",
                                [
                                    '_controller' => '\Drupal\onboard\Controller\OnBoardController::legislationView',
                                    '_title'      => "$t[name] $t[number]",
                                    'type'        => $t['name'],
                                    'number'      => '',
                                    'node'        => $nid
                                ],
                                ['_permission' => 'access content'],
                                ['parameters'  => ['node' => ['type'=>'entity:node']]]
                            );
                        }
                    }
                }
            }
        }
        return $routes;
    }
}
