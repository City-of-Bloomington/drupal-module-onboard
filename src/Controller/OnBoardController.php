<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\onboard\OnBoardService;

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
            $year = (int)$year;
            if (!$year) { $year = (int)date('Y');  }

            $committee_id = $node->field_committee->value;

            $legislation = OnBoardService::legislation($committee_id, $year);
            $years       = OnBoardService::legislation_years($committee_id);

            return [
                '#theme'       => 'onboard_legislation',
                '#legislation' => $legislation,
                '#year'        => $year,
                '#years'       => $years,
                '#node'        => $node
            ];
        }
        return [];
    }
}
