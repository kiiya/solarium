<?php

namespace Solarium\Tests\Component\ResponseParser;

use PHPUnit\Framework\TestCase;
use Solarium\Component\Facet\FacetInterface;
use Solarium\Component\Facet\Field;
use Solarium\Component\FacetSet;
use Solarium\Component\ResponseParser\FacetSet as Parser;
use Solarium\Component\Result\Stats\Result;
use Solarium\Exception\RuntimeException;
use Solarium\QueryType\Select\Query\Query;

class FacetSetTest extends TestCase
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var FacetSet
     */
    protected $facetSet;

    /**
     * @var Query
     */
    protected $query;

    public function setUp(): void
    {
        $this->parser = new Parser();

        $this->facetSet = new FacetSet();
        $this->facetSet->createFacet('field', ['key' => 'keyA', 'field' => 'fieldA']);
        $this->facetSet->createFacet('query', ['key' => 'keyB']);
        $this->facetSet->createFacet(
            'multiquery',
            [
                'key' => 'keyC',
                'query' => [
                    'keyC_A' => ['query' => 'id:1'],
                    'keyC_B' => ['query' => 'id:2'],
                ],
            ]
        );
        $this->facetSet->createFacet('range', ['key' => 'keyD']);
        $this->facetSet->createFacet('pivot', ['key' => 'keyE', 'fields' => 'cat,price']);

        $this->query = new Query();
    }

    public function testParse()
    {
        $data = [
            'facet_counts' => [
                'facet_fields' => [
                    'keyA' => [
                        'value1',
                        12,
                        'value2',
                        3,
                    ],
                ],
                'facet_queries' => [
                    'keyB' => 23,
                    'keyC_A' => 25,
                    'keyC_B' => 16,
                ],
                'facet_ranges' => [
                    'keyD' => [
                        'before' => 3,
                        'after' => 5,
                        'between' => 4,
                        'counts' => [
                            '1.0',
                            1,
                            '101.0',
                            2,
                            '201.0',
                            1,
                        ],
                    ],
                ],
                'facet_pivot' => [
                    'keyE' => [
                        [
                            'field' => 'cat',
                            'value' => 'abc',
                            'count' => '123',
                            'pivot' => [
                                ['field' => 'price', 'value' => 1, 'count' => 12],
                                ['field' => 'price', 'value' => 2, 'count' => 8],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->parser->parse($this->query, $this->facetSet, $data);
        $facets = $result->getFacets();

        $this->assertEquals(['keyA', 'keyB', 'keyC', 'keyD', 'keyE'], array_keys($facets));

        $this->assertEquals(
            ['value1' => 12, 'value2' => 3],
            $facets['keyA']->getValues()
        );

        $this->assertEquals(23, $facets['keyB']->getValue());

        $this->assertEquals(
            ['keyC_A' => 25, 'keyC_B' => 16],
            $facets['keyC']->getValues()
        );

        $this->assertEquals(
            ['1.0' => 1, '101.0' => 2, '201.0' => 1],
            $facets['keyD']->getValues()
        );

        $this->assertEquals(3, $facets['keyD']->getBefore());
        $this->assertEquals(4, $facets['keyD']->getBetween());
        $this->assertEquals(5, $facets['keyD']->getAfter());
        $this->assertEquals(1, count($facets['keyE']));

        $this->assertEquals(23, $result->getFacet('keyB')->getValue());
    }

    public function testParseExtractFromResponse()
    {
        $data = [
            'facet_counts' => [
                'facet_fields' => [
                    'keyA' => [
                        'value1',
                        12,
                        'value2',
                        3,
                    ],
                ],
                'facet_queries' => [
                    'keyB' => 23,
                    'keyC_A' => 25,
                    'keyC_B' => 16,
                ],
                'facet_ranges' => [
                    'keyD' => [
                        'before' => 3,
                        'after' => 5,
                        'between' => 4,
                        'counts' => [
                            '1.0',
                            1,
                            '101.0',
                            2,
                            '201.0',
                            1,
                        ],
                    ],
                ],
                'facet_pivot' => [
                    'cat,price' => [
                        [
                            'field' => 'cat',
                            'value' => 'abc',
                            'count' => '123',
                            'pivot' => [
                                ['field' => 'price', 'value' => 1, 'count' => 12],
                                ['field' => 'price', 'value' => 2, 'count' => 8],
                            ],
                            'stats' => [
                                'min' => 4,
                                'max' => 6,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $facetSet = new FacetSet();
        $facetSet->setExtractFromResponse(true);

        $result = $this->parser->parse($this->query, $facetSet, $data);
        /** @var FacetInterface[] $facets */
        $facets = $result->getFacets();

        $this->assertEquals(['keyA', 'keyB', 'keyC_A', 'keyC_B', 'keyD', 'cat,price'], array_keys($facets));

        $this->assertEquals(
            ['value1' => 12, 'value2' => 3],
            $facets['keyA']->getValues()
        );

        $this->assertEquals(
            23,
            $facets['keyB']->getValue()
        );

        // As the multiquery facet is a Solarium virtual facet type, it cannot be detected based on Solr response data
        $this->assertEquals(
            25,
            $facets['keyC_A']->getValue()
        );

        $this->assertEquals(
            16,
            $facets['keyC_B']->getValue()
        );

        $this->assertEquals(
            ['1.0' => 1, '101.0' => 2, '201.0' => 1],
            $facets['keyD']->getValues()
        );

        $this->assertEquals(
            3,
            $facets['keyD']->getBefore()
        );

        $this->assertEquals(
            4,
            $facets['keyD']->getBetween()
        );

        $this->assertEquals(
            5,
            $facets['keyD']->getAfter()
        );

        $this->assertCount(
            1,
            $facets['cat,price']
        );

        $pivots = $facets['cat,price']->getPivot();

        $this->assertCount(
            2,
            $pivots[0]->getStats()
        );
    }

    public function testParseNoData()
    {
        $result = $this->parser->parse($this->query, $this->facetSet, []);
        $this->assertEquals([], $result->getFacets());
    }

    public function testInvalidFacetType()
    {
        $facetStub = $this->createMock(Field::class);
        $facetStub->expects($this->any())
             ->method('getType')
             ->willReturn('invalidfacettype');
        $facetStub->expects($this->any())
             ->method('getKey')
             ->willReturn('facetkey');

        $this->facetSet->addFacet($facetStub);

        $this->expectException(RuntimeException::class);
        $this->parser->parse($this->query, $this->facetSet, []);
    }

    public function testParseJsonFacet()
    {
        $data = [
            'facets' => [
                'top_genres' => [
                    'buckets' => [
                        [
                            'val' => 'Fantasy',
                            'count' => 5432,
                            'top_authors' => [
                                'buckets' => [
                                    [
                                        'val' => 'Mercedes Lackey',
                                        'count' => 121,
                                    ],
                                    [
                                        'val' => 'Piers Anthony',
                                        'count' => 98,
                                    ],
                                ],
                            ],
                            'highpop' => [
                                'count' => 876,
                                'publishers' => [
                                    'buckets' => [
                                        [
                                            'val' => 'Bantam Books',
                                            'count' => 346,
                                        ],
                                        [
                                            'val' => 'Tor',
                                            'count' => 217,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'val' => 'Science Fiction',
                            'count' => 4188,
                            'top_authors' => [
                                'buckets' => [
                                    [
                                        'val' => 'Terry Pratchett',
                                        'count' => 87,
                                    ],
                                ],
                            ],
                            'highpop' => [
                                'count' => 876,
                                'publishers' => [
                                    'buckets' => [
                                        [
                                            'val' => 'Harper Collins',
                                            'count' => 43,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'empty_buckets' => [
                    'buckets' => [],
                ],
            ],
        ];

        $result = $this->parser->parse($this->query, $this->facetSet, $data);
        $facets = $result->getFacets();

        $this->assertEquals(['top_genres'], array_keys($facets));

        $buckets = $facets['top_genres']->getBuckets();

        $this->assertEquals(
            'Fantasy',
            $buckets[0]->getValue()
        );
        $this->assertEquals(
            5432,
            $buckets[0]->getCount()
        );

        $nested_facets = $buckets[0]->getFacets();

        $this->assertEquals(['top_authors', 'highpop'], array_keys($nested_facets));

        $this->assertFalse(isset($facets['empty_buckets']));

        $this->assertEquals('Fantasy', $result->getFacet('top_genres')->getBuckets()[0]->getValue());
    }

    public function testParseFacetPivotStats(): void
    {
        $key = 'cat,country,inStock';

        $data = [
            'facet_counts' => [
                'facet_pivot' => [
                    $key => [
                        [
                            'field' => 'cat',
                            'value' => 'electronics',
                            'count' => 12,
                            'pivot' => [
                                [
                                    'field' => 'country',
                                    'value' => 'nl',
                                    'count' => 8,
                                    'stats' => [
                                        'stats_fields' => [
                                            'price' => [
                                                'min' => 74.98,
                                                'max' => 399.0,
                                            ],
                                        ],
                                    ],
                                    'pivot' => [
                                        [
                                            'field' => 'inStock',
                                            'value' => true,
                                            'count' => 4,
                                            'stats' => [
                                                'stats_fields' => [
                                                    'price' => [
                                                        'min' => 128.98,
                                                        'max' => 240.65,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'stats' => [
                                'stats_fields' => [
                                    'price' => [
                                        'min' => 12.32,
                                        'max' => 1024.20,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $facetSet = new FacetSet();
        $facetSet->setExtractFromResponse(true);

        $result = $this->parser->parse($this->query, $facetSet, $data);
        $pivot = $result->getFacets()[$key];

        $first = $pivot->getPivot()[0];
        $this->assertInstanceOf(Result::class, $first->getStats()->getResult('stats_fields'));

        $second = $first->getPivot()[0];
        $this->assertInstanceOf(Result::class, $second->getStats()->getResult('stats_fields'));

        $third = $first->getPivot()[0];
        $this->assertInstanceOf(Result::class, $third->getStats()->getResult('stats_fields'));
    }
}
