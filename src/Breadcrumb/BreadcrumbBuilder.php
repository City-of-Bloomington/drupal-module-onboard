<?php
/**
 * @copyright 2017 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
namespace Drupal\onboard\Breadcrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;

class BreadcrumbBuilder implements BreadcrumbBuilderInterface
{
    /**
     * Register breadcrumbs for all onboard routes, except settings
     */
    public function applies(RouteMatchInterface $route_match)
    {
        $name = explode('.', $route_match->getRouteName());
        return ($name[0]=='onboard' && $name[1]!='settings');
    }

    public function build(RouteMatchInterface $route_match)
    {
        list($module, $action) = explode('.', $route_match->getRouteName());
        $node = $route_match->getParameter('node');
        $year = $route_match->getParameter('year');

        $breadcrumb = new Breadcrumb();
        $breadcrumb->addCacheContexts(['url']);
        $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
        $breadcrumb->addLink(Link::createFromRoute($node->title->value, 'entity.node.canonical', ['node'=>$node->nid->value]));
        if ($year) {
            $breadcrumb->addLink(Link::createFromRoute($year, 'onboard.meetings', ['node'=>$node->nid->value, 'year'=>$year]));
        }

        return $breadcrumb;
    }
}
