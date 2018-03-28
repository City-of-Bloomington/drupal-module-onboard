<?php
/**
 * @copyright 2018 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
declare (strict_types=1);
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
 *   id = "onboard_links_block",
 *   admin_label = "Board Links",
 *   context = {
 *     "node" = @ContextDefinition("entity:node")
 *   }
 * )
 */
class LinksBlock extends BlockBase implements BlockPluginInterface
{
    public function getCacheContexts()
    {
        return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $settings  = \Drupal::config('onboard.settings');
        $fieldname = $settings->get('onboard_committee_field');
        $node      = $this->getContextValue('node');


        if ($node->hasField( $fieldname)) {
            $id = $node->get($fieldname)->value;
            if ($id) {
                $json = OnBoardService::committee_info($id);
                return [
                    '#theme'       => 'onboard_links',
                    '#committee'   => $json,
                    '#nid'         => $node->id(),
                    '#onboard_url' => OnBoardService::getUrl(),
                    '#cache'       => ['max-age' => 3600]
                ];
            }
        }
    }
}
