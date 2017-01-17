<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard;

use Drupal\Core\Site\Settings;

class OnBoardService
{
    /**
     * @return stdClass The JSON object
     */
    public static function committee_list()
    {
        $ONBOARD = Settings::get('onboard_url');
        $url = $ONBOARD.'/committees?format=json';

        $client = \Drupal::httpClient();
        $response = $client->get($url);
        return json_decode($response->getBody());
    }
    /**
     * @param  int      $committee_id
     * @return stdClass               The json object
     */
    public static function committee_info($committee_id)
    {
        $ONBOARD = Settings::get('onboard_url');
        $url = $ONBOARD.'/committees/members?format=json;committee_id='.$committee_id;

        $client = \Drupal::httpClient();
        $response = $client->get($url);
        return json_decode($response->getBody());
    }
}
