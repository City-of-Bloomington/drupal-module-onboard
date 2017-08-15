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
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays upcoming meetings for a committee
 *
 * @Block(
 *   id = "onboard_meetings_block",
 *   admin_label = "Committee Meetings",
 *   context = {
 *     "node" = @ContextDefinition("entity:node")
 *   }
 * )
 */
class MeetingsBlock extends BlockBase implements BlockPluginInterface
{
    const DEFAULT_NUMDAYS   = 7;
    const DEFAULT_MAXEVENTS = 4;

    public function build()
    {
        $settings  = \Drupal::config('onboard.settings');
        $fieldname = $settings->get('onboard_committee_field');
        $config    = $this->getConfiguration();
        $node      = $this->getContextValue('node');

        $numdays   = !empty($config['numdays'  ]) ? (int)$config['numdays'  ] : self::DEFAULT_NUMDAYS;
        $maxevents = !empty($config['maxevents']) ? (int)$config['maxevents'] : self::DEFAULT_MAXEVENTS;

        if ($node->hasField( $fieldname)) {
            $id = $node->get($fieldname)->value;
            if ($id) {
                $start = new \DateTime();
                $end   = new \DateTime();
                $end->add(new \DateInterval("P{$numdays}D"));

                $c        = 0;
                $meetings = [];
                foreach (OnBoardService::meetings($id,  null, $start, $end) as $m) {
                    $c++;
                    if ($c > $maxevents) { break; }
                    $meetings[] = $m;
                }
                $committee = OnBoardService::committee_info($id);
                return [
                    '#theme'     => 'onboard_upcoming_meetings',
                    '#meetings'  => $meetings,
                    '#committee' => $committee,
                    '#nid'       => $node->id()
                ];
            }
        }
    }

    public function blockForm($form, FormStateInterface $form_state)
    {
        $form   = parent::blockForm($form, $form_state);
        $config = $this->getConfiguration();

        $form['onboard_meetings_numdays'] = [
            '#type'          => 'number',
            '#title'         => 'Number of days',
            '#description'   => 'Maximum number of days in the future to look for events.',
            '#default_value' => isset($config['numdays']) ? $config['numdays'] : self::DEFAULT_NUMDAYS
        ];
        $form['onboard_meetings_maxevents'] = [
            '#type'          => 'number',
            '#title'         => 'Max Events',
            '#description'   => 'Maximum number of events to show in the block',
            '#default_value' => isset($config['maxevents']) ? $config['maxevents'] : self::DEFAULT_MAXEVENTS
        ];
        return $form;
   }

    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->configuration['numdays'  ] = $form_state->getValue('onboard_meetings_numdays'  );
        $this->configuration['maxevents'] = $form_state->getValue('onboard_meetings_maxevents');
   }
}
