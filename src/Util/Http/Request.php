<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/21
 * Time: 0:16
 */

namespace NashFrame\Util\Http;


final class Request
{
    private $get, $post, $cookie, $files, $server, $raw;

    public function __construct(array $get, array $post, array $cookie, array $files, array $server, string $raw)
    {
        $this->raw = $raw;
        $this->get = $get;
        $this->post = $post;
        $this->cookie = $cookie;
        $this->files = $files;
        $this->server = $server;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getQuery($key = null)
    {
        return $this->getData($this->get, $key);
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getServer($key = null)
    {
        return $this->getData($this->server, $key);
    }

    /**
     * @param $data
     * @param null $key
     * @return array|null
     */
    private function getData($data, $key = null)
    {
        if (is_string($key)) {
            return $data[$key] ?? null;
        } elseif (is_array($key)) {
            foreach ($key as $index => $item) {
                $key[$index] = $this->getData($data, $item);
            }
            return $key;
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->getServer('REQUEST_METHOD'));
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return explode('?', $this->getServer('REQUEST_URI'), 2)[0];
    }
}
