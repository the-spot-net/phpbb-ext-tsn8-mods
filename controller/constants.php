<?php
/**
 * Created by thepizzy.net
 * User: @neotsn
 * Date: 12/30/19
 * Time: 2:07 PM
 */

namespace tsn\tsn\controller;

/**
 * Class constants
 * Handles some constant values used frequently
 * @package tsn\tsn\controller
 */
class constants
{
    // URI Route Directories
    const ROUTE_TSN = '/tsn';
    const ROUTE_AJAX = self::ROUTE_TSN . '/ajax';

    // URI Base Routes
    const ROUTE_FORUM = self::ROUTE_TSN . '/forum';
    const ROUTE_GROUP = self::ROUTE_TSN . '/group';
    const ROUTE_MEMBER = self::ROUTE_TSN . '/member';
    const ROUTE_TOPIC = self::ROUTE_TSN . '/topic';
    const ROUTE_USER = self::ROUTE_TSN . '/user';

    // AJAX Slugs for use in switch & routes
    const SLUG_SPECIAL_REPORT = 'special-report';

    // URI AJAX Routes
    const ROUTE_AJAX_SPECIAL_REPORT = self::ROUTE_AJAX . '/' . self::SLUG_SPECIAL_REPORT;
}
