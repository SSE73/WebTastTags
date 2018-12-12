<?php

namespace XLiteWeb\tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * @author cerber
 */
class testTags extends \XLiteWeb\AXLiteWeb
{
    public function testTagCRUD()
    {
        $data = [
            'tagName' => 'Tag_1',
            'newTagName' => 'Changed_Tag_1',
            'productId' => '37',
            'productName' => 'Apple iPhone 6S [Options & Attributes] [Custom tabs]'
        ];

        // Add new tag
        $tags = $this->AdminTags;
        $this->assertTrue($tags->load(true), 'Error loading tags page.');
        $this->assertTrue($tags->validate(),'Loaded page is not tags page.');

        $tags->addTag($data['tagName']);
        $tags->saveChanges();
        $this->assertEquals($tags->getNameTag(),$data['tagName'], 'Не правильно добавилось в БД');

        // Assign tag to a product
        $adminProduct = $this->AdminProduct;
        $adminProduct->loadProductId(false, $data['productId']);

        $adminProduct->inputSelectNameTag();
        $adminProduct->saveChanges();

        $customProduct = $this->CustomerProduct;

        $customProduct->load(false,$data['productId']);
        $this->assertEquals($customProduct->getNameTag(), $data['tagName'], 'Нет такого Тега на странице продукта ');

        // Search by tag
        $customProduct->linkNameTag();

        $searchPage = $this->CustomerSearch;
        $this->assertTrue($searchPage->validate(),'Search page did not load properly.');

        $this->assertEquals($searchPage->getCountFoundProducts(), 1, 'Number of found produсts is wrong.');
        $this->assertEquals($data['productName'], $searchPage->getNameProduct(), 'Продукт не найден');

        $searchPage->setWhereToSearch('by-sku');
        $searchPage->clickButtonSearch();
        $searchPage->setWaitForAjax();

        $this->assertEquals($searchPage->getCountFoundProducts(), 0, 'Number of found produсts is wrong.');

        $searchPage->setWhereToSearch('by-tag');
        $searchPage->clickButtonSearch();
        $searchPage->setWaitForAjax();

        $this->assertEquals($searchPage->getCountFoundProducts(), 1, 'Number of found produсts is wrong.');
        $this->assertEquals($data['productName'], $searchPage->getNameProduct(), 'Продукт не найден');

        // Change tag name
        $tags->load();

        $tags->changeNameTag($data['newTagName']);
        $tags->saveChanges();

        $this->assertEquals($data['newTagName'], $tags->get_clickNameTagText->getText(), 'Tag Не правильно добавился в БД');

        $adminProduct->loadProductId(false,$data['productId']);

        $this->assertTrue($adminProduct->isFindNameTag($data['newTagName']), 'Нет такого Тега на странице продукта ');

        $customProduct->load(false,$data['productId']);

        $this->assertEquals($customProduct->getNameTag(), $data['newTagName'], 'Нет такого Тега на странице продукта ');

        // Detach tag from product
        $adminProduct->detachTagByName($data['newTagName']);
        $adminProduct->saveChanges();

        $customProduct->load(false,$data['productId']);
        $this->assertTrue($customProduct->isNotFindTag(), 'Tag Не удалился из БД');

        // Remove tag
        $tags->load();
        $tags->deleteTagByName($data['newTagName']);

        $this->assertTrue($tags->isNotFindTag(), 'Tag Не удалился из БД');

        $customProduct->load(false,$data['productId']);

        $this->assertTrue($customProduct->isNotFindTag(), 'Tag Не удалился из БД');
    }
}
