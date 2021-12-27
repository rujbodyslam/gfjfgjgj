<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\Category\LandingPage;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert that displayed category data on category page equals to passed from fixture.
 */
class AssertCategoryPage extends AbstractConstraint
{
    /**
     * CMS Block display mode.
     *
     * @var array
     */
    protected $visibleCmsBlockMode = [
        'Static block only',
        'Static block and products'
    ];

    /**
     * Category view page.
     *
     * @var CatalogCategoryView
     */
    protected $categoryViewPage;

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert that displayed category data on category page equals to passed from fixture.
     *
     * @param Category $category
     * @param FixtureFactory $fixtureFactory
     * @param CatalogCategoryView $categoryView
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        Category $category,
        FixtureFactory $fixtureFactory,
        CatalogCategoryView $categoryView,
        BrowserInterface $browser
    ) {
        $this->browser = $browser;
        $this->categoryViewPage = $categoryView;
        $this->prepareData($fixtureFactory, $category);
        $this->browser->open($this->getCategoryUrl($category));
        
        $this->assertGeneralInformation($category);
        $this->assertDisplaySetting($category);
    }

    /**
     * Prepare comparison data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param Category $category
     * @return void
     */
    protected function prepareData(FixtureFactory $fixtureFactory, Category $category)
    {
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'default',
                'data' => [
                    'category_ids' => [
                        'category' => $category,
                    ],
                ]
            ]
        );
        $product->persist();
    }

    /**
     * Get category url to open.
     *
     * @param Category $category
     * @return string
     */
    protected function getCategoryUrl(Category $category)
    {
        $categoryUrlKey = [];
        while ($category) {
            $categoryUrlKey[] = $category->hasData('url_key')
                ? strtolower($category->getUrlKey())
                : trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $category->getName())), '-');

            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if ($category !== null && 1 == $category->getParentId()) {
                $category = null;
            }
        }

        return $_ENV['app_frontend_url'] . implode('/', array_reverse($categoryUrlKey)) . '.html';
    }

    /**
     * Assert category general information.
     *
     * @param Category $category
     * @return void
     */
    protected function assertGeneralInformation(Category $category)
    {
        $categoryUrl = $this->getCategoryUrl($category);
        \PHPUnit_Framework_Assert::assertEquals(
            $categoryUrl,
            $this->browser->getUrl(),
            'Wrong page URL.'
            . "\nExpected: " . $categoryUrl
            . "\nActual: " . $this->browser->getUrl()
        );

        if ($category->getName()) {
            $title = $this->categoryViewPage->getTitleBlock()->getTitle();
            \PHPUnit_Framework_Assert::assertEquals(
                $category->getName(),
                $title,
                'Wrong page title.'
                . "\nExpected: " . $category->getName()
                . "\nActual: " . $title
            );
        }

        if ($category->getDescription()) {
            $description = $this->categoryViewPage->getViewBlock()->getDescription();
            \PHPUnit_Framework_Assert::assertEquals(
                $category->getDescription(),
                $description,
                'Wrong category description.'
                . "\nExpected: " . $category->getDescription()
                . "\nActual: " . $description
            );
        }
    }

    /**
     * Assert category display settings.
     *
     * @param Category $category
     * @return void
     */
    protected function assertDisplaySetting(Category $category)
    {
        if (
            $category->getLandingPage()
            && $category->getDisplayMode()
            && in_array($category->getDisplayMode(), $this->visibleCmsBlockMode)
        ) {
            /** @var LandingPage $sourceLandingPage */
            $sourceLandingPage = $category->getDataFieldConfig('landing_page')['source'];
            $fixtureContent = $sourceLandingPage->getCmsBlock()->getContent();
            $pageContent = $this->categoryViewPage->getViewBlock()->getContent();

            \PHPUnit_Framework_Assert::assertEquals(
                $fixtureContent,
                $pageContent,
                'Wrong category landing page content.'
                . "\nExpected: " . $fixtureContent
                . "\nActual: " . $pageContent
            );
        }
        if ($category->getDefaultSortBy()) {
            $sortBy = strtolower($category->getDefaultSortBy());
            $sortType = $this->categoryViewPage->getTopToolbar()->getSelectSortType();
            \PHPUnit_Framework_Assert::assertEquals(
                $sortBy,
                $sortType,
                'Wrong sorting type.'
                . "\nExpected: " . $sortBy
                . "\nActual: " . $sortType
            );
        }

        if ($category->getAvailableSortBy()) {
            $availableSortType = array_filter(
                $category->getAvailableSortBy(),
                function (&$value) {
                    return $value !== '-' && ucfirst($value);
                }
            );
            if ($availableSortType) {
                $availableSortType = array_values($availableSortType);
                $availableSortTypeOnPage = $this->categoryViewPage->getTopToolbar()->getSortType();
                \PHPUnit_Framework_Assert::assertEquals(
                    $availableSortType,
                    $availableSortTypeOnPage,
                    'Wrong available sorting type.'
                    . "\nExpected: " . implode(PHP_EOL, $availableSortType)
                    . "\nActual: " . implode(PHP_EOL, $availableSortTypeOnPage)
                );
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category data on category page equals to passed from fixture.';
    }
}
