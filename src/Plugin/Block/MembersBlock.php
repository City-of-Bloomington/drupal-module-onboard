<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Plugin\Block;

use Drupal\onboard\OnBoardService;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays members for a committee from OnBoard.
 *
 * @Block(
 *   id = "onboard_members_block",
 *   admin_label = "Committee Members",
 *   context = {
 *     "node" = @ContextDefinition(
 *          "entity:node",
 *          label = "Current Node",
 *          required = FALSE
 *      )
 *   }
 * )
 */
class MembersBlock extends BlockBase implements BlockPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $node = $this->getContextValue('node');
        if ($node) {
            $settings  = \Drupal::config('onboard.settings');
            $fieldname = $settings->get('onboard_committee_field');


            if ($node->hasField( $fieldname)) {
                $id = $node->get($fieldname)->value;
                if ($id) {
                    $json = OnBoardService::committee_info($id);
                    if (!empty($json['seats'])) {
                        usort($json['seats'], function ($a, $b) {
                            $as = $a['vacant'] ? 'Vacant' : $a['currentMember']['lastname'];
                            $bs = $b['vacant'] ? 'Vacant' : $b['currentMember']['lastname'];
                            if     ($as == $bs) { return 0; }
                            return ($as <  $bs) ? -1 : 1;
                        });
                    }
                    return [
                        '#theme'       => 'onboard_members',
                        '#committee'   => $json,
                        '#nid'         => $node->id(),
                        '#onboard_url' => OnBoardService::getUrl(),
                        '#cache'       => [
                            'contexts' => ['route'],
                            'max-age'  => 3600
                        ]
                    ];
                }
            }
        }
    }
}
