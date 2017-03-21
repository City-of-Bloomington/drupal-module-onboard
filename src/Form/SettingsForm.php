<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'onboard_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableConfigNames()
    {
        return ['onboard.settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('onboard.settings');

        $form['onboard_url'] = [
            '#type'          => 'textfield',
            '#title'         => 'CKAN Url',
            '#default_value' => $config->get('onboard_url')
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('onboard.settings')
             ->set('onboard_url', $form_state->getValue('onboard_url'))
             ->save();

        parent::submitForm($form, $form_state);
    }
}
