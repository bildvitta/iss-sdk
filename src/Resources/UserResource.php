<?php

namespace BildVitta\Hub\Resources;

use BildVitta\Hub\Contracts\Resources\UserResourceContract;
use BildVitta\Hub\Hub;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;

/**
 * Class UserResource.
 *
 * @package BildVitta\Hub\Resources
 */
class UserResource extends Resource implements UserResourceContract
{
    /**
     * @const string
     */
    private const PREFIX = '/users';

    /**
     * @const string
     */
    private const ENDPOINT_ME = self::PREFIX . '/me';

    /**
     * @const string
     */
    private const ENDPOINT_FIND_BY_UUID = self::PREFIX . '/%s';

    /**
     * @const string
     */
    private const ENDPOINT_GET_BY_UUIDS = self::PREFIX;

    /**
     * @var Hub
     */
    private Hub $hub;

    /**
     * UserResource constructor.
     *
     * @param Hub $hub
     */
    public function __construct(Hub $hub)
    {
        $this->hub = $hub;
    }

    /**
     * User data token informed.
     *
     * @return Response.
     *
     * @throws RequestException
     */
    public function me(): Response
    {
        $params = [
            'project' => Config::get('app.slug', ''),
            'programmatic' => true,
        ];
        $endpoint = self::ENDPOINT_ME . '?' . http_build_query($params);
        return $this->hub->request->get($endpoint)->throw();
    }

    public function findByUuid(string $uuid, bool $programatic = false): Response
    {
        $endpoint = self::ENDPOINT_FIND_BY_UUID;

        if ($programatic) {
            $endpoint = $this->hub::PREFIX_PROGRAMMATIC . self::ENDPOINT_FIND_BY_UUID;
            $this->hub = $this->hub->setToken('', true);
        }

        $url = vsprintf($endpoint, [$uuid]);

        return $this->hub->request->get($url)->throw();
    }

    public function getByUuids(array $uuids, $attributes = [], bool $programatic = false): Response
    {
        $url = self::ENDPOINT_GET_BY_UUIDS;

        if ($programatic) {
            $url = $this->hub::PREFIX_PROGRAMMATIC . self::ENDPOINT_GET_BY_UUIDS;
            $this->hub = $this->hub->setToken('', true);
        }

        $body = [];
        if (!empty($uuids)) {
            $body['uuids'] = $uuids;
        }

        $query = [];
        if ($attributes) {
            $query['attributes'] = $attributes;
        }

        return $this->hub->request
            ->withBody(json_encode($body), 'application/json')
            ->get($url, $query)
            ->throw();
    }

    /**
     * Get all Users that HAVE a specific Permission
     * This function is only programmatic
     * @param string $permissionProjectSlug
     * @param mixed $permission
     * @param array $query
     * @return Response
     */
    public function getWhereHasPermission(string $permissionProjectSlug, mixed $permission, array $query = []): Response
    {
        $url = '/programmatic/users';
        $this->hub = $this->hub->setToken('', true);

        $body = [];
        if (!empty($permissionProjectSlug)) {
            $body['has_permission']['project_slug'] = $permissionProjectSlug;
        }
        if (!empty($permission)) {
            $body['has_permission']['permission'] = $permission;
        }

        $query = array_merge([
            'limit' => 999999,
            'offset' => 0,
        ], $query);

        return $this->hub->request
            ->withBody(json_encode($body), 'application/json')
            ->get($url, $query)
            ->throw();
    }

    /**
     * Get all users that BELONGS to a specifc permission of a specifc user
     * This function is only programmatic
     * @param string $permissionProjectSlug
     * @param array $permission
     * @param string|array $userUuid
     * @param array $attributes
     * @return Response
     * @throws RequestException
     */
    public function getWhereBelongsToPermission(string $permissionProjectSlug, string $permission, string|array $userUuid, array $query = []): Response
    {
        $url = '/programmatic/users';
        $this->hub = $this->hub->setToken('', true);

        $body = [];
        if (!empty($permissionProjectSlug)) {
            $body['belongs_to_permission']['project_slug'] = $permissionProjectSlug;
        }
        if (!empty($permission)) {
            $body['belongs_to_permission']['permission'] = $permission;
        }
        if (!empty($userUuid)) {
            $body['belongs_to_permission']['user_uuid'] = $userUuid;
        }

        $query = array_merge([
            'limit' => 999999,
            'offset' => 0,
        ], $query);

        return $this->hub->request
            ->withBody(json_encode($body), 'application/json')
            ->get($url, $query)
            ->throw();
    }

    /**
     * @param  array  $query
     * @param  bool  $programatic
     *
     * @return Response
     * @throws RequestException
     */
    public function search(array $query = [], bool $programatic = false): Response
    {
        $url = self::PREFIX;

        if ($programatic) {
            $url = $this->hub::PREFIX_PROGRAMMATIC . $url;
            $this->hub = $this->hub->setToken('', true);
        }

        return $this->hub->request->get($url, $query)->throw();
    }
}
