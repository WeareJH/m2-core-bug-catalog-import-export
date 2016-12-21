<?php
namespace JH\CoreBugCatalogImportExport\Model\Export;

use Magento\ImportExport\Model\Import;
use \Magento\Store\Model\Store;
use \Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Export\Product as MagentoProduct;

class Product extends MagentoProduct
{
    /**
     * Collect export data for all products
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function collectRawData()
    {
        $data = [];
        $collection = $this->_getEntityCollection();
        foreach ($this->_storeIdToCode as $storeId => $storeCode) {
            $collection->setStoreId($storeId);
            /**
             * @var int $itemId
             * @var \Magento\Catalog\Model\Product $item
             */
            foreach ($collection as $itemId => $item) {
                $additionalAttributes = [];
                $productLinkId = $item->getData($this->getProductEntityLinkField());
                foreach ($this->_getExportAttrCodes() as $code) {
                    $attrValue = $item->getData($code);
                    if (!$this->isValidAttributeValue($code, $attrValue)) {
                        continue;
                    }

                    if (isset($this->_attributeValues[$code][$attrValue]) && !empty($this->_attributeValues[$code])) {
                        $attrValue = $this->_attributeValues[$code][$attrValue];
                    }
                    $fieldName = isset($this->_fieldsMap[$code]) ? $this->_fieldsMap[$code] : $code;

                    if ($this->_attributeTypes[$code] === 'datetime') {
                        $attrValue = $this->_localeDate->formatDateTime(
                            new \DateTime($attrValue),
                            \IntlDateFormatter::SHORT,
                            \IntlDateFormatter::SHORT
                        );
                    }

                    if ($storeId != Store::DEFAULT_STORE_ID
                        && isset($data[$itemId][Store::DEFAULT_STORE_ID][$fieldName])
                        && $data[$itemId][Store::DEFAULT_STORE_ID][$fieldName] == htmlspecialchars_decode($attrValue)
                    ) {
                        continue;
                    }

                    if ($this->_attributeTypes[$code] !== 'multiselect') {
                        if (is_scalar($attrValue)) {
                            if (!in_array($fieldName, $this->_getExportMainAttrCodes())) {
                                $additionalAttributes[$fieldName] = $fieldName .
                                    ImportProduct::PAIR_NAME_VALUE_SEPARATOR . $attrValue;
                            }
                            $data[$itemId][$storeId][$fieldName] = htmlspecialchars_decode($attrValue);
                        }
                    } else {
                        $this->collectMultiselectValues($item, $code, $storeId);
                        if (!empty($this->collectedMultiselectsData[$storeId][$productLinkId][$code])) {
                            $additionalAttributes[$code] = $fieldName .
                                ImportProduct::PAIR_NAME_VALUE_SEPARATOR . implode(
                                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                                    $this->collectedMultiselectsData[$storeId][$productLinkId][$code]
                                );
                        }
                    }
                }

                if (!empty($additionalAttributes)) {
                    $additionalAttributes = array_map('htmlspecialchars_decode', $additionalAttributes);
                    $data[$itemId][$storeId][self::COL_ADDITIONAL_ATTRIBUTES] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributes);
                } else {
                    unset($data[$itemId][$storeId][self::COL_ADDITIONAL_ATTRIBUTES]);
                }

                if (!empty($data[$itemId][$storeId]) || $this->hasMultiselectData($item, $storeId)) {
                    $attrSetId = $item->getAttributeSetId();
                    $data[$itemId][$storeId][self::COL_STORE] = $storeCode;
                    $data[$itemId][$storeId][self::COL_ATTR_SET] = $this->_attrSetIdToName[$attrSetId];
                    $data[$itemId][$storeId][self::COL_TYPE] = $item->getTypeId();
                }
                $data[$itemId][$storeId][self::COL_SKU] = $item->getSku();
                $data[$itemId][$storeId]['store_id'] = $storeId;
                $data[$itemId][$storeId]['product_id'] = $itemId;
                $data[$itemId][$storeId]['product_link_id'] = $productLinkId;
            }
            $collection->clear();
        }

        return $data;
    }
}