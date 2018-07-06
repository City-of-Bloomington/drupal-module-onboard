<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
declare (strict_types=1);
namespace Drupal\onboard\Plugin\Block;

use Drupal\onboard\OnBoardService;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;

/**
 * Displays reports for a committee from OnBoard.
 *
 * @Block(
 *   id = "onboard_reports_block",
 *   admin_label = "Committee Reports",
 *   context = {
 *     "node" = @ContextDefinition(
 *          "entity:node",
 *          label = "Current Node",
 *          required = FALSE
 *      )
 *   }
 * )
 */
class ReportsBlock extends BlockBase implements BlockPluginInterface
{
    public function build()
    {
        $node = $this->getContextValue('node');
        if ($node) {
            $settings  = \Drupal::config('onboard.settings');
            $fieldname = $settings->get('onboard_committee_field');


            if ($node && $node->hasField($fieldname)) {
                $id    = $node->get($fieldname)->value;
                if ($id) {
                    $json = OnBoardService::reports($id);
                    if (count($json)) {
                        return [
                            '#theme'       => 'onboard_reports',
                            '#reports'     => $json,
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
}
