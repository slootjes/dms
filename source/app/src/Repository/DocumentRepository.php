<?php

namespace App\Repository;

use App\VO\Document;
use App\VO\SearchHit;
use App\VO\SearchResponse;
use Elasticsearch\Client;

class DocumentRepository
{
    const INDEX = 'documents';
    const RESULT_SIZE = 50;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $data
     * @param string $className
     * @return SearchResponse
     */
    public function search(array $data, string $className): SearchResponse
    {
        $query = [
            'bool' => [
                'must' => []
            ]
        ];
        if (!empty($data['query'])) {
            $query['bool']['must'][] = [
                'multi_match' => [
                    'query' => $data['query'],
                    'fields' => [
                        'attachment.content',
                        'attachment.keywords',
                        'subject'
                    ]
                ]
            ];
        }
        if (!empty($data['from'])) {
            $query['bool']['must'][] = [
                'match' => [
                    'from' => $data['from']
                ]
            ];
        }
        if (!empty($data['to'])) {
            $query['bool']['must'][] = [
                'term' => [
                    'to' => $data['to']
                ]
            ];
        }
        if (!empty($data['created_min'])) {
            $query['bool']['must'][] = [
                'range' => [
                    'created' => [
                        'gte' => $data['created_min']->format('Y-m-d')
                    ]
                ]
            ];
        }
        if (!empty($data['created_max'])) {
            $query['bool']['must'][] = [
                'range' => [
                    'created' => [
                        'lt' => $data['created_max']->format('Y-m-d')
                    ]
                ]
            ];
        }

        $params = [
            'index' => self::INDEX,
            'size' => self::RESULT_SIZE,
            'body' => [
                'query' => $query,
                '_source' => $this->getSourceFields()
            ]
        ];

        if (!empty($data['sort'])) {
            switch ($data['sort']) {
                case 'created_asc':
                    $params['body']['sort'] = [['created' => 'asc']];
                    break;
                case 'created_desc':
                    $params['body']['sort'] = [['created' => 'desc']];
                    break;
            }
        }

        return new SearchResponse($this->client->search($params), $className);
    }

    public function findById(string $id): Document
    {
        return Document::fromSearchHit(new SearchHit($this->client->get([
            'index' => self::INDEX,
            'id' => $id,
            '_source' => $this->getSourceFields()
        ])));
    }

    /**
     * @param Document $document
     * @param bool $useData
     */
    public function add(Document $document, $useData = true)
    {
        $params = [
            'index' => self::INDEX,
            'id' => $document->getId(),
            'body' => $document->getBody($useData)
        ];
        if (isset($params['body']['attachment_data'])) {
            $params['pipeline'] = 'attachment';
        }

        $this->client->index($params);
    }

    /**
     * Configures attachment pipeline and index
     */
    public function setup()
    {
        $this->client->ingest()->putPipeline([
            'id' => 'attachment',
            'body' => [
                'processors' => [
                    ['attachment' => [
                        'field' => 'attachment_data',
                        'target_field' => 'attachment',
                        'indexed_chars' => -1,
                        'ignore_missing' => true
                    ]],
                    ['remove' => [
                        'field' => 'attachment_data'
                    ]]
                ]
            ]
        ]);

        try {
            $this->client->indices()->delete([
                'index' => self::INDEX
            ]);
        }
        catch (\Exception $e) {
            // maybe index didn't exist yet
        }

        $this->client->indices()->create([
            'index' => self::INDEX,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'dutch_stop' => [
                                'type' => 'stop',
                                'stopwords' => '_dutch_'
                            ]
                        ],
                        'analyser' => [
                            'dutch' => [
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'dutch_stop'
                                ]
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'filepath' => [
                            'type' => 'keyword'
                        ],
                        'filename' => [
                            'type' => 'keyword'
                        ],
                        'from' => [
                            'type' => 'text',
                            'analyzer' => 'dutch',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'subject' => [
                            'type' => 'text',
                            'analyzer' => 'dutch',
                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'to' => [
                            'type' => 'keyword'
                        ],
                        'created' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @return SearchResponse
     */
    public function getAggregates(): SearchResponse
    {
        return new SearchResponse($this->client->search([
            'index' => self::INDEX,
            'size' => 0,
            'body' => [
                'aggs' => [
                    'created_min' => [
                        'min' => ['field' => 'created', 'format' => 'yyyy-MM-dd']
                    ],
                    'created_max' => [
                        'max' => ['field' => 'created', 'format' => 'yyyy-MM-dd']
                    ],
                    'to' => [
                        'terms' => ['field' => 'to']
                    ]
                ]
            ]
        ]));
    }

    /**
     * @return array
     */
    private function getSourceFields(): array
    {
        return [
            'filepath',
            'filename',
            'created',
            'from',
            'to',
            'subject'
        ];
    }
}
