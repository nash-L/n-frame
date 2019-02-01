<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 15:30
 */

namespace NashFrame\Util\Http;


class Request
{
    protected $query, $post, $raw, $server, $files, $cookie, $rawData;

    public function __construct(array $get, array $post, array $server, array $files, array $cookie, string $raw)
    {
        $this->query = $get;
        $this->post = $post;
        $this->server = $server;
        $this->files = $files;
        $this->cookie = $cookie;
        $this->raw = $raw;
        $this->rawData = [];
        if ($raw) {
            if ($rawDataJson = json_decode($raw, true)) {
                $this->rawData = $rawDataJson;
            } elseif ($xml = simplexml_load_string($raw)) {
                $this->rawData = json_decode(json_encode($xml), true);
            }
        }
    }

    public function getQuery($args = null)
    {
        return $this->getData($this->query, $args);
    }

    public function getPost($args = null)
    {
        return $this->getData($this->post, $args);
    }

    public function getCookie($args = null)
    {
        return $this->getData($this->cookie, $args);
    }

    public function getServer($args = null)
    {
        return $this->getData($this->server, $args);
    }

    public function getFiles($args = null)
    {
        return $this->getData($this->files, $args);
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getRawData($args = null)
    {
        return $this->getData($this->rawData, $args);
    }

    protected function getData(array $pool, $keys)
    {
        if (empty($keys)) {
            return $pool;
        } elseif (is_string($keys)) {
            return isset($pool[$keys]) ? $pool[$keys] : null;
        } elseif (is_array($keys)) {
            foreach ($keys as $index => $key) {
                $keys[$index] = $this->getData($pool, $key);
            }
            return $keys;
        }
        return null;
    }
}
