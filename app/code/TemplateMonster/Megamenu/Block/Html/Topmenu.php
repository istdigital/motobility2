<?php

namespace TemplateMonster\Megamenu\Block\Html;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;

use TemplateMonster\Megamenu\Helper\Data;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{

    public $_helper;

    public $_layoutFactory;

    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        array $data = []
    ) {
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
        $this->_helper = $helper;
        $this->_layoutFactory = $layoutFactory;
    }

    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    ) {
        if ($this->_helper->isModuleEnabled()) {
            return $this->renderMenu($menuTree, $childrenWrapClass, $limit, $colBrakes);
        } else {
            return parent::_getHtml($menuTree, $childrenWrapClass, $limit, $colBrakes);
        }
    }

    public function renderMenu(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';
        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            $outermostClass .= ' ' . $child->getMmCssClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '>';
            if ($label = $child->getMmLabel()) {
                $html .= '<strong class="mm-label">' . $label . '</strong>';
            }
            $html .= '<span>' . $this->escapeHtml(
                    $child->getName()
                );

            $html .= '</span></a>';
            if ($child->getMmTurnOn() && ($childLevel == 0)) {
                $html .= $this->renderMegamenuBlock($child);
            } else {
                $html .= $this->_addSubMenu(
                        $child,
                        $childLevel,
                        $childrenWrapClass,
                        $limit
                    );
            }
            $html .= '</li>';
            $itemPosition++;
            $counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        if ($this->_helper->isModuleEnabled()) {
            $html = '';
            if (!$child->hasChildren()) {
                return $html;
            }

            $colStops = [];
            if ($childLevel == 0 && $limit) {
                $colStops = $this->_columnBrake($child->getChildren(), $limit);
            }

            $html .= '<ul class="level' . $childLevel . ' submenu rd-navbar-dropdown">'; //TODO check if rd-navbar-dropdown needed?
            $html .= $this->renderMenu($child, $childrenWrapClass, $limit, $colStops);
            $html .= '</ul>';

            return $html;
        } else {
            return parent::_addSubMenu($child, $childLevel, $childrenWrapClass, $limit);
        }
    }

    //TODO fix this method
    public function renderMegamenuBlock($category)
    {
        $block = $this->_layoutFactory->create()->createBlock(
            'TemplateMonster\Megamenu\Block\Html\Topmenu\Block',
            ''
        );
        
        $block->setNode($category);

        return $block->renderBlock();
    }
}

