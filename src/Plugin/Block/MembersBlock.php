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
 * Displays members for a committee from OnBoard.
 *
 * @Block(
 *   id = "onboard_members_block",
 *   admin_label = "Committee Members",
 *   context = {
 *     "node" = @ContextDefinition("entity:node")
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
        $config = $this->getConfiguration();
        $node   = $this->getContextValue('node');

        $fieldname = !empty($config['fieldname'])
                          ? $config['fieldname']
                          : 'field_committee';

        if ($node->hasField( $fieldname)) {
            $id = $node->get($fieldname)->value;
            if ($id) {
                $json = OnBoardService::committee_info($id);

                return [
                    '#theme'     => 'onboard_members',
                    '#committee' => $json
                ];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form   = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();

        $form['onboard_committee_field'] = [
            '#type'          => 'textfield',
            '#title'         => 'Fieldname',
            '#description'   => 'Name of the field that contains the committee_id',
            '#default_value' => isset($config['fieldname']) ? $config['fieldname'] : ''
        ];
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['fieldname'] = $form_state->getValue('onboard_committee_field');
    }
}
