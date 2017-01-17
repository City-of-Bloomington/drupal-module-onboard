<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Plugin\Block;

use Drupal\onboard\OnBoardService;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *     id = "members_block",
 *     admin_label = "Committee Members",
 *     context = {
 *         "node" = @ContextDefinition("entity:node")
 *     }
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
        if ($node->hasField( 'field_committee')) {
            $id = $node->get('field_committee')->value;
            if ($id) {
                $json = OnBoardService::committee_info($id);

                return [
                    '#theme'     => 'onboard_members',
                    '#committee' => $json
                ];
            }
        }
    }
}
