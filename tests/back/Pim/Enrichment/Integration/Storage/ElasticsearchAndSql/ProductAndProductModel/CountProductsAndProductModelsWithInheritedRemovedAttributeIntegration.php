<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class CountProductsAndProductModelsWithInheritedRemovedAttributeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_only_count_products_and_product_models_with_an_inherited_removed_attribute()
    {
        $count = $this->getCountProductsAndProductModelsWithInheritedRemovedAttribute()->count(['an_attribute', 'a_third_attribute']);

        self::assertEquals(1, $count);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getCountProductsAndProductModelsWithInheritedRemovedAttribute(): CountProductsAndProductModelsWithInheritedRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_products_and_product_models_with_inherited_removed_attribute');
    }
}
