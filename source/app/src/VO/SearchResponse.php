<?php

namespace App\VO;

class SearchResponse
{
    /**
     * @var array
     */
    private $response;

    /**
     * @var array
     */
    private $hits = [];

    /**
     * @var array
     */
    private $aggregations = [];

    /**
     * @var string
     */
    private $className;

    /**
     * @param array $response
     * @param string|null $className
     */
    public function __construct(array $response, string $className = null)
    {
        $this->response = $response;
        $this->className = $className;
    }

    /**
     * @return array
     */
    public function getHits(): array
    {
        if (empty($this->hits) && !empty($this->response['hits']['hits'])) {
            foreach ($this->response['hits']['hits'] as $hit) {
                $hit = new SearchHit($hit);
                if ($this->className) {
                    $hit = $this->className::fromSearchHit($hit);
                }
                $this->hits[] = $hit;
            }
        }

        return $this->hits;
    }

    /**
     * @return array
     */
    public function getAggregations(): array
    {
        if (empty($this->aggregations) && !empty($this->response['aggregations'])) {
            foreach ($this->response['aggregations'] as $name => $aggregation) {
                $this->aggregations[$name] = new SearchAggregation($aggregation);
            }
        }

        return $this->aggregations;
    }
}
