<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\LayeredNavigation\Resources;

use Exception;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\LayeredNavigation\Models\Filter;
use Manadev\LayeredNavigation\Registries\FilterTypes;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Zend_Db_Expr;

class FilterResource extends Db\AbstractDb {
    protected $fields;
    protected $typeFields = [];

    /**
     * @var FilterTypes
     */
    protected $filterTypes;
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    public function __construct(Db\Context $context, FilterTypes $filterTypes, EventManagerInterface $eventManager,
        $connectionName = null)
    {
        $this->filterTypes = $filterTypes;
        parent::__construct($context, $connectionName);
        $this->eventManager = $eventManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('mana_filter', 'id');
    }

    protected function _getLoadSelect($field, $value, $object) {
        if(is_null($object->getData('store_id'))) {
            throw new \Exception(__("Please call setStore() method before calling load() on %s objects.", get_class($this)));
        }
        return parent::_getLoadSelect($field, $value, $object)
            ->where('store_id = ?', $object->getData('store_id'));
    }

    /**
     * @param Filter $model
     * @return array
     */
    public function getFields($model) {
        if (!$this->fields) {
            $this->fields = [
                'filter_id' => [
                    'is_reference' => true,
                ],
                'store_id' => [
                    'is_reference' => true,
                ],
                'attribute_id' => [
                    'is_reference' => true,
                ],
                'type' => [
                    'is_reference' => true,
                ],
                'param_name' => [
                    'global_use_default_label' => __('Use attribute code'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'title' => [
                    'global_use_default_label' => __('Use attribute label'),
                    'store_level_use_default_label' => __('Use store-level attribute label / Same for all stores'),
                ],
                'position' => [
                    'global_use_default_label' => __('Use attribute position'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'template' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'minimum_product_count_per_option' => [
                    'global_use_default_label' => __("Use attribute's 'Use In Layered Navigation'"),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'is_enabled_in_categories' => [
                    'global_use_default_label' => __("Use attribute's 'Use In Layered Navigation'"),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'is_enabled_in_search' => [
                    'global_use_default_label' => __("Use attribute's 'Use in Search Layered Navigation'"),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_in_main_sidebar' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_in_additional_sidebar' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_above_products' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_on_mobile' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'calculate_slider_min_max_based_on' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'number_format' => [
                    'global_use_default_label' => __('Use Price Format'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'decimal_digits' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'is_two_number_formats' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'use_second_number_format_on' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'second_number_format' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'second_decimal_digits' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_thousand_separator' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'is_slide_on_existing_values' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'is_manual_range' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'slider_style' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'min_max_role' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'min_slider_code' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'hide_filter_with_single_visible_item' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'use_filter_title_in_url' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'url_part' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'position_in_url' => [
                    'global_use_default_label' => __('Use position'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'include_in_canonical_url' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'force_no_index' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'force_no_follow' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'include_in_meta_title' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'include_in_meta_description' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'include_in_meta_keywords' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'include_in_sitemap' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_selected_options_first' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'sort_options_by' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_more_method' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_more_item_limit' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'show_option_search' => [
                    'global_use_default_label' => __('Use store configuration'),
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
                'use_logical_and' => [
                    'store_level_use_default_label' => __('Same for all stores'),
                ],
            ];
        }

        if (!isset($this->typeFields[$model->getData('type')])) {
            $this->typeFields[$model->getData('type')] =
                $this->filterTypes->get($model->getData('type'))->refineFields($this->fields);
        }

        return $this->typeFields[$model->getData('type')];
    }

    /**
     * @param Filter $model
     * @param array $data
     * @throws Exception
     */
    public function edit($model, array $data) {
        $db = $this->getConnection();

        $db->beginTransaction();

        $data = $this->filterData($model, $data);
        $edit = $this->loadEdit($model);
        $edit = $this->mergeEdit($edit, $data);
        $this->validateEdit($model, $edit);
        $this->saveEdit($model, $edit);

        try {
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollBack();

            throw $e;
        }

        $model->afterEdit();
        $this->eventManager->dispatch('after_mana_filter_edit', ['ids' => [$model->getData('filter_id')]]);
    }

    public function addEditedData($model, &$edit, $defaults, $data) {
        $data = $this->filterData($model, $data);
        $edit = $this->mergeEdit($edit, $data);

        foreach ($edit as $field => $value) {
            if ($value !== null) {
                $model->setData($field, $value);
            }
            elseif (isset($defaults[$field])) {
                $model->setData($field, $defaults[$field]);
            }
        }
    }

    protected function getEditRecordReferenceFields($model) {
        foreach ($this->getFields($model) as $name => $settings) {
            if (!empty($settings['is_reference'])) {
                yield $name;
            }
        }
    }

    /**
     * @param Filter $model
     * @return array
     */
    public function loadEdit($model) {
        $db = $this->getConnection();

        if ($editId = $model->getData('edit_id')) {
            $select = $db->select()
                ->from($this->getTable('mana_filter_edit'))
                ->where("`id` = ?", $editId);

            $edit = $db->fetchRow($select);

            if (isset($edit['id'])) {
                unset($edit['id']);
            }

            foreach ($this->getEditRecordReferenceFields($model) as $field) {
                if (isset($edit[$field])) {
                    unset($edit[$field]);
                }
            }

            foreach (array_keys($edit) as $field) {
                if (is_null($edit[$field])) {
                    unset($edit[$field]);
                }
            }
        }
        else {
            $edit = [];
        }

        return $edit;
    }

    /**
     * @param array $edit
     * @param array $data
     * @return array
     */
    protected function mergeEdit($edit, $data) {
        foreach ($data as $field => $value) {
            $edit[$field] = $value;
        }

        return $edit;
    }

    /**
     * @param Filter $model
     * @param array $edit
     */
    protected function saveEdit($model, $edit) {
        /* @var $db \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $db = $this->getConnection();
        
        $isEmpty = $this->isEditEmpty($edit);
        if ($editId = $model->getData('edit_id')) {
            if (!$isEmpty) {
                $db->update($this->getTable('mana_filter_edit'), $edit, $db->quoteInto("`id` = ?", $editId));
            }
            else {
                $db->delete($this->getTable('mana_filter_edit'), $db->quoteInto("`id` = ?", $editId));
            }
        }
        else {
            if (!$isEmpty) {
                foreach ($this->getEditRecordReferenceFields($model) as $field) {
                    $edit[$field] = $model->getData($field);
                }
                $db->insert($this->getTable('mana_filter_edit'), $edit);

                $model
                    ->setData('edit_id', $db->lastInsertId($this->getTable('mana_filter_edit')))
                    ->save();
            }
        }
    }

    protected function isEditEmpty($edit) {
        foreach ($edit as $field => $value) {
            if (!is_null($value)) {
                return false;
            }
        }

        return true;
    }

    public function filterData($model, $data) {
        $fields = $this->getFields($model);
        if (isset($data['use_default'])) {
            $useDefault = array_flip($data['use_default']);
            unset($data['use_default']);
        }
        else {
            $useDefault = [];
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (!isset($fields[$key])) {
                continue;
            }

            if (!empty($fields[$key]['is_reference'])) {
                continue;
            }

            if (isset($useDefault[$key])) {
                $result[$key] = null;
            }
            else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param Filter $model
     * @param array $edit
     * @throws Exception
     */
    protected function validateEdit($model, $edit) {
        /* @var $db \Magento\Framework\DB\Adapter\Pdo\Mysql */
        $db = $this->getConnection();

        if (isset($edit['param_name'])) {
            $select = $db->select()
                ->from(['f' => $this->getTable('mana_filter')], [new Zend_Db_Expr("`f`.`id`")])
                ->where("`f`.`store_id` = ?", $model->getData('store_id'))
                ->where("`f`.`param_name` = ?", $edit['param_name'])
                ->where("`f`.`filter_id` <> ?", $model->getData('filter_id'));
                
            if ($db->fetchOne($select)) {
                throw new Exception(__('Filter parameter name should be unique.'));
            }
        }
    }
}