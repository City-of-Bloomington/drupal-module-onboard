<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *  id = "members_block",
 *  admin_label = "Committee Members"
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
        if (!empty($config['committee_id'])) {
            $json = OnBoardService::committee_info($config['committee_id']);

            return [
                '#theme'     => 'onboard_members',
                '#committee' => $json
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form   = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();

        $form['members_block_committee_id'] = [
            '#type'          => 'number',
            '#title'         => 'Committee ID',
            '#description'   => 'The Committee ID in OnBoard',
            '#min'           => 1,
            '#step'          => 1,
            '#size'          => 5,
            '#default_value' => isset($config['committee_id']) ? $config['committee_id'] : ''
        ];
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->setConfigurationValue('committee_id', $form_state->getValue('members_block_committee_id'));
    }
}
