<?php

namespace App\VO;

class SearchHit
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
     * @param string $field
     * @return mixed|null
     */
    public function getSourceField(string $field)
    {
        return $this->data['_source'][$field] ?? null;
    }

    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->data['_source'];
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->data['_index'];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->data['_type'];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->data['_id'];
    }

    /**
     * @return float|null
     */
    public function getScore(): ?float
    {
        return $this->data['score'];
    }
}