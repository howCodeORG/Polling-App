<?php

/**
 * Directus – <http://getdirectus.com>
 *
 * @link      The canonical repository – <https://github.com/directus/directus>
 * @copyright Copyright 2006-2016 RANGER Studio, LLC – <http://rangerstudio.com>
 * @license   GNU General Public License (v3) – <http://www.gnu.org/copyleft/gpl.html>
 */

namespace Directus\SDK\Response;

/**
 * Response Interface
 *
 * @author Welling Guzmán <welling@rngr.org>
 */
interface ResponseInterface extends \JsonSerializable
{
    /**
     * Get the response data
     *
     * @return array
     */
    public function getData();

    /**
     * Get the response metadata
     *
     * @return array
     */
    public function getMetaData();
}