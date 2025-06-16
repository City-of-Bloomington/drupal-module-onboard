<?php
/**
 * @copyright 2017-2025 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL, see LICENSE
 */
declare (strict_types=1);
namespace Drupal\onboard;

use Drupal\Core\Site\Settings;

class OnBoardService
{
    public static function getUrl()
    {
        $config  = \Drupal::config('onboard.settings');
        return $config->get('onboard_url');
    }

    private static function doJsonQuery($url): array
    {
        $client   = \Drupal::httpClient();
        $response = $client->get($url);
        return json_decode((string)$response->getBody(), true) ?? [];
    }
    /**
     * @param  array    $query  Search parameters for the committee list
     * @return array            The JSON object
     */
    public static function committee_list(array $query=null): array
    {
        $base_url = self::getUrl();
        if ($base_url) {
            $params = $query ? array_merge(['format' => 'json'], $query) : ['format' => 'json'];
            $url    = $base_url.'/committees?'.http_build_query($params, '', ';');
            return self::doJsonQuery($url);
        }
        return [];
    }

    public static function committee_info(int $committee_id): array
    {
        $url = self::getUrl()."/committees/$committee_id?format=json";
        return self::doJsonQuery($url);
    }

    public static function members(int $committee_id): array
    {
        $url = self::getUrl()."/committees/$committee_id/members?format=json";
        return self::doJsonQuery($url);
    }

    public static function meetings(int $committee_id, ?int $year=null, ?\DateTime $start=null, ?\DateTime $end=null, ?int $limit=0): array
    {
        $params = ['format'=>'json'];
        if ($start) {
            if (!$end) {
                $end = clone $start;
                $end->add(new \DateInterval('P1Y'));
            }
            $params['start'] = $start->format('Y-m-d');
            $params['end'  ] = $end  ->format('Y-m-d');
        }
        else {
            $params['year'] = $year;
        }

        $url      = self::getUrl()."/committees/$committee_id/meetings?".http_build_query($params);
        $meetings = self::doJsonQuery($url);
        if ($meetings) {
            return $limit ? array_slice($meetings, 0, $limit) : $meetings;
        }
        return [];
    }

    public static function meetingFile_years(int $committee_id)
    {
        $url = self::getUrl()."/meetingFiles/years?format=json;committee_id=$committee_id";
        return self::doJsonQuery($url);
    }

    /**
     * @param in    $committee_id
     * @param array $params        Additional search parameters
     */
    public static function legislation_list(int $committee_id, array $params)
    {
        $params['format'] = 'json';
        $url = self::getUrl()."/committees/$committee_id/legislation?".http_build_query($params);
        return self::doJsonQuery($url);
    }

    public static function legislation_years(int $committee_id, int $type_id)
    {
        $url = self::getUrl()."/committees/$committee_id/legislation/years?format=json;type_id=$type_id";
        return self::doJsonQuery($url);
    }

    public static function legislation_types()
    {
        static $types = null;
        if (!$types) {
            $url   = self::getUrl()."/legislationTypes?format=json";
            $types = self::doJsonQuery($url);
        }
        return $types;
    }

    public static function reports(int $committee_id)
    {
        $url = self::getUrl()."/reports?format=json;committee_id=$committee_id";
        return self::doJsonQuery($url);
    }

    public static function type_id(string $type): ?int
    {
        foreach (self::legislation_types() as $t) {
            if ($t['name'] == $type) {
                return (int)$t['id'];
            }
        }
        return null;
    }
}
