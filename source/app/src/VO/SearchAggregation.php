<?php

namespace App\VO;

class SearchAggregation
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getAsDateTime(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d', $this->data['value_as_string']);
    }

    /**
     * @return array
     */
    public function getBucketKeys(): array
    {
        $keys = array_map(function ($value) {
            return $value['key'];
        }, $this->data['buckets']);

        return array_combine($keys, $keys);
    }
}