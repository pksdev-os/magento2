<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Manager;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Directory\Model\Currency;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;

/**
 * Class Website
 */
class Website
{
    /**
     * @var array
     */
    private $websites;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        DirectoryHelper $directoryHelper
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Retrieve websites
     *
     * @param ProductInterface $product
     * @param EavAttribute $eavAttribute
     * @return array
     */
    public function getWebsites(ProductInterface $product, EavAttribute $eavAttribute)
    {
        if (null !== $this->websites) {
            return $this->websites;
        }

        $websites = [
            [
                'value' => 0,
                'label' => $this->formatLabel(__('All Websites'), $this->directoryHelper->getBaseCurrencyCode()),
            ]
        ];

        if ($this->storeManager->hasSingleStore()
            || ($eavAttribute->getEntityAttribute() && $eavAttribute->getEntityAttribute()->isScopeGlobal()
            )
        ) {
            return $this->websites = $websites;
        }

        if ($storeId = $this->locator->getStore()->getId()) {
            /** @var WebsiteInterface $website */
            $website = $this->storeManager->getStore($storeId)->getWebsite();
            $websites[$website->getId()] = [
                'value' => $website->getId(),
                'label' => $this->formatLabel(
                    $website->getName(),
                    $website->getConfig(Currency::XML_PATH_CURRENCY_BASE)
                )
            ];
        } else {
            /** @var WebsiteInterface $website */
            foreach ($this->storeManager->getWebsites() as $website) {
                if (!in_array($website->getId(), $product->getWebsiteIds())) {
                    continue;
                }
                $websites[$website->getId()] = [
                    'value' => $website->getId(),
                    'label' => $this->formatLabel(
                        $website->getName(),
                        $website->getConfig(Currency::XML_PATH_CURRENCY_BASE)
                    )
                ];
            }
        }

        return $this->websites = $websites;
    }

    /**
     * Retrieve regions
     *
     * @return array
     */
    public function getRegions()
    {
        return $this->directoryHelper->getRegionData();
    }

    /**
     * Format label
     *
     * @param string $websiteName
     * @param string $currencyCode
     * @return string
     */
    protected function formatLabel($websiteName, $currencyCode)
    {
        return $websiteName . ' ' . $currencyCode;
    }

    /**
     * Check is multi websites mode enabled
     *
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !$this->storeManager->hasSingleStore();
    }
}