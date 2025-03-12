<?php
namespace Dolphin\ShippingMethod\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Dolphin\ShippingMethod\Block\Adminhtml\CategoryDropDown;

/**
 * Class Pricerange
 */
class ShippingCategory extends AbstractFieldArray
{
    /**
     * @var CategoryDropDown
     */
    private $CategoryRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('Category', [
            'label' => __('Category'),
            'renderer' => $this->getCategoryRenderer(),
            'class' => 'required-entry',
        ]);
        $this->addColumn('Price', ['label' => __('Price'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        //cateory selection
        $Category = $row->getCategory();
        if ($Category !== null) {
            $options['option_' . $this->getCategoryRenderer()->calcOptionHash($Category)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get the Category renderer (dropdown)
     *
     * @return CategoryDropDown
     * @throws LocalizedException
     */
    private function getCategoryRenderer(): CategoryDropDown
    {
        if (!$this->CategoryRenderer) {
            $this->CategoryRenderer = $this->getLayout()->createBlock(
                CategoryDropDown::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->CategoryRenderer;
    }
}
