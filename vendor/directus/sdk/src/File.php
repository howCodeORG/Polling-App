<?php

namespace Directus\SDK;

class File implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $caption;

    /**
     * @var string
     */
    protected $tags;

    public function __construct($path, $attributes = [])
    {
        $this->path = $path;

        foreach($attributes as $attribute) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $attribute;
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = $this->parseFile();

        if ($this->title) {
            $data['title'] = $this->title;
        }

        if ($this->tags) {
            $data['tags'] = $this->tags;
        }

        if ($this->caption) {
            $data['caption'] = $this->caption;
        }

        return $data;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function parseFile()
    {
        $attributes = [];
        $path = $this->path;
        if (file_exists($path)) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $mimeType = mime_content_type($path);
            $attributes['name'] = pathinfo($path, PATHINFO_FILENAME) . '.' . $ext;
            $attributes['type'] = $mimeType;
            $content = file_get_contents($path);
            $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($content);
            $attributes['data'] = $base64;
        } else {
            throw new \Exception(sprintf('File %s not found', $path));
        }

        return $attributes;
    }
}