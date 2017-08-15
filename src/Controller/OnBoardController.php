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
    private $fieldname = '';

    public function getCommitteeIdField()
    {
        if (!$this->fieldname) {
            $config  = \Drupal::config('onboard.settings');
            $this->fieldname = $config->get('onboard_committee_field');
        }
        return $this->fieldname;
    }

    public function meetings($node, $year)
    {
        $field = $this->getCommitteeIdField();

        if ($node->hasField($field) && $node->$field->value) {
            $year = (int)$year;
            if (!$year) { $year = (int)date('Y');  }

            $committee_id = $node->$field->value;

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
        $field = $this->getCommitteeIdField();

        if ($node->hasField($field) && $node->$field->value) {
            $year = $year ? (int)$year : (int)date('Y');

            $committee_id = $node->$field->value;

            $legislation = OnBoardService::legislation_list ($committee_id, $year);
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
        $field = $this->getCommitteeIdField();

        if ($node->hasField($field) && $node->$field->value) {
            $committee_id = $node->$field->value;
            $years        = OnBoardService::legislation_years($committee_id);

            $decades = [];
            foreach ($years as $y=>$data) {
                $d = (floor($y / 10)) * 10;
                $decades[$d][$y] = $data;
            }

            return [
                '#theme'   => 'onboard_archiveYears',
                '#decades' => $decades,
                '#node'    => $node,
                '#route'   => 'onboard.legislation.node-'.$node->get('nid')->value
            ];
        }
    }

    public function legislationInfo($node, $legislation_id)
    {
        $legislation = OnBoardService::legislation_info($legislation_id);

        return [
            '#theme'       => 'onboard_legislationInfo',
            '#legislation' => $legislation,
            '#node'        => $node
        ];
    }

    public function meetingYears($node)
    {
        $field = $this->getCommitteeIdField();

        if ($node->hasField($field) && $node->$field->value) {
            $committee_id = $node->$field->value;
            $years        = OnBoardService::meetingFile_years($committee_id);

            $decades = [];
            foreach ($years as $y=>$data) {
                $d = (floor($y / 10)) * 10;
                $decades[$d][$y] = $data;
            }

            return [
                '#theme'   => 'onboard_archiveYears',
                '#decades' => $decades,
                '#node'    => $node,
                '#route'   => 'onboard.meetings.node-'.$node->get('nid')->value
            ];
        }
    }

    public function legislationView($node, $type, $number)
    {
        $field = $this->getCommitteeIdField();

        $fields = [
            'committee_id' => $node->$field->value,
            'type'         => $type,
            'number'       => $number
        ];
        $list = OnBoardService::legislation_find($fields);
        if (count($list) === 1) {
            return [
                '#theme'       => 'onboard_legislationInfo',
                '#legislation' => $list[0],
                '#node'        => $node

            ];
        }
        return [];
    }
}
