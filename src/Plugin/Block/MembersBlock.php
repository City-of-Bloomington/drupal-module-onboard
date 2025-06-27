<?php
/**
 * @copyright 2017-2025 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL, see LICENSE
 */
namespace Drupal\onboard\Plugin\Block;

use Drupal\onboard\OnBoardService;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;

/**
 * Displays members for a committee from OnBoard.
 *
 * @Block(
 *   id = "onboard_members_block",
 *   admin_label = "Committee Members"
 * )
 */
class MembersBlock extends BlockBase implements BlockPluginInterface
{
    public function getCacheContexts()
    {
        return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
    }

    public function build()
    {
        $node = \Drupal::routeMatch()->getParameter('node');
        if ($node && $node instanceof Node) {
            $settings  = \Drupal::config('onboard.settings');
            $fieldname = $settings->get('onboard_committee_field');


            if ($node->hasField( $fieldname)) {
                $id = (int)$node->get($fieldname)->value;
                if ($id) {
                    $members = OnBoardService::members($id);
                    if (isset($members[0]['committee_id'])) {
                        usort($members, function ($a, $b) {
                            $as = (!$a['member_id'] || $a['carryOver']) ? 'Vacant' : $a['member_lastname'];
                            $bs = (!$b['member_id'] || $b['carryOver']) ? 'Vacant' : $b['member_lastname'];
                            if     ($as == $bs) { return 0; }
                            return ($as <  $bs) ? -1 : 1;
                        });
                    }
                    foreach ($members as $i=>$m) {
                        if ($m['offices']) {
                            $members[$i]['offices'] = self::unserialize_offices($m['offices']);
                        }
                    }

                    return [
                        '#theme'       => 'onboard_members',
                        '#members'     => $members,
                        '#nid'         => $node->id(),
                        '#onboard_url' => OnBoardService::getUrl(),
                        '#cache'       => [
                            'max-age'  => 3600
                        ]
                    ];
                }
            }
        }
    }

    /**
     * Returns an array of Office title strings held by a member
     */
    private static function unserialize_offices(string $offices): array
    {
        $out = [];
        foreach (explode(',', $offices) as $o) {
            list($id, $title) = explode('|', $o);
            $out[] = $title;
        }
        return $out;
    }
}
