<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Controller;

use Drupal\onboard\OnBoardService;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;

class OnBoardController extends ControllerBase
{
    public function meetings($node, $year)
    {
        if ($node->hasField('field_committee') && $node->field_committee->value) {
            $year = (int)$year;
            if (!$year) { $year = (int)date('Y');  }

            $committee_id = $node->field_committee->value;

            $meetings = OnBoardService::meetings($committee_id, $year);
            $years    = OnBoardService::meetingFile_years($committee_id);

            return [
                '#theme'    => 'onboard_meetingFiles',
                '#meetings' => $meetings,
                '#year'     => $year,
                '#years'    => $years,
                '#node'     => $node
            ];
        }
        return [];
    }

    public function legislation($node, $year)
    {
        if ($node->hasField('field_committee') && $node->field_committee->value) {
            $year = $year ? (int)$year : (int)date('Y');

            $committee_id = $node->field_committee->value;

            $legislation = OnBoardService::legislation($committee_id, $year);
            $years       = OnBoardService::legislation_years($committee_id);

            $maxItems    = 10;
            $half        = (int)$maxItems/2;
            $currentYear = (int)date('Y');

            $maxYear = ($year + $half < $currentYear) ? $year + $half : $currentYear;

            $options = [];
            $c = 0;
            foreach (array_keys($years) as $y) {
                if ($y <= $maxYear) {
                    if ($c >= $maxItems) { break; }

                    $options["$y"] = $y;
                    $c++;
                }
            }

            return [
                '#theme'       => 'onboard_legislation',
                '#legislation' => $legislation,
                '#year'        => $year,
                '#years'       => $options,
                '#node'        => $node
            ];
        }
        return [];
    }

    public function legislationYears($node)
    {
        if ($node->hasField('field_committee') && $node->field_committee->value) {
            $committee_id = $node->field_committee->value;
            $years        = OnBoardService::legislation_years($committee_id);

            $decades = [];
            foreach ($years as $y=>$data) {
                $d = (floor($y / 10)) * 10;
                $decades[$d][$y] = $data;
            }

            return [
                '#theme'   => 'onboard_legislationYears',
                '#decades' => $decades,
                '#node'    => $node
            ];
        }
    }
}
