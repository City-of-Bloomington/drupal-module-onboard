<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard;

use Drupal\Core\Site\Settings;

class OnBoardService
{
    public static function getUrl()
    {
        $config  = \Drupal::config('onboard.settings');
        return $config->get('onboard_url');
    }

    private static function doJsonQuery($url)
    {
        $client   = \Drupal::httpClient();
        $response = $client->get($url);
        return json_decode($response->getBody(), true);
    }
    /**
     * @return stdClass The JSON object
     */
    public static function committee_list()
    {
        $url = self::getUrl().'/committees?format=json';
        return self::doJsonQuery($url);
    }

    /**
     * @param  int      $committee_id
     * @return stdClass               The json object
     */
    public static function committee_info($committee_id)
    {
        $url = self::getUrl().'/committees/members?format=json;committee_id='.$committee_id;
        return self::doJsonQuery($url);
    }

    public static function meetings($committee_id, $year)
    {
        $url = self::getUrl()."/committees/meetings?format=json;committee_id=$committee_id;year=$year";
        return self::doJsonQuery($url);
    }

    public static function meetingFile_years($committee_id)
    {
        $url = self::getUrl()."/meetingFiles/years?format=json;committee_id=$committee_id";
        return self::doJsonQuery($url);
    }
}
