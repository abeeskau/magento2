<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\ObjectManager;

/**
 * Check attribute on product form.
 */
class AssertAddedProductAttributeOnProductForm extends AbstractConstraint
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog Product Index page.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog Product Edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Add this attribute to Default attribute Template. Create product and Assert that created attribute
     * is displayed on product form (Products > Inventory > Catalog).
     *
     * @param InjectableFixture $product
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductAttribute $productAttributeOriginal
     * @throws \Exception
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        FixtureFactory $fixtureFactory,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $attributeSet,
        CatalogProductAttribute $productAttributeOriginal = null
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;

        if (!$product->hasData('sku')) {
            if (!$productAttributeOriginal) {
                $productAttributeOriginal = $attribute;
            }
            $product = $this->objectManager->create(
                'Magento\Catalog\Test\TestStep\CreateProductWithAttributeSetStep',
                [
                    'attribute' => $productAttributeOriginal,
                    'attributeSet' => $attributeSet
                ]
            )->run();
            $product = $product['product'];
        }
        $filterProduct = ['sku' => $product->getSku()];
        $catalogProductIndex->open();
        $catalogProductIndex->getProductGrid()->searchAndOpen($filterProduct);

        $catalogProductAttribute = ($productAttributeOriginal !== null)
            ? array_merge($productAttributeOriginal->getData(), $attribute->getData())
            : $attribute->getData();

        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductEdit->getProductForm()->checkAttributeLabel($catalogProductAttribute),
            "Product Attribute is absent on Product form."
        );
    }

    /**
     * Text of Product Attribute is present on the Product form.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Attribute is present on Product form.';
    }
}
