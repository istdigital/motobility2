<?php
/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace TemplateMonster\FilmSlider\Controller\Adminhtml\Slider;

use \Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_FilmSlider::filmslider_remove');
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('slider_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $sliderRepo = $this->_objectManager->create('TemplateMonster\FilmSlider\Api\SliderRepositoryInterface');
                $model = $sliderRepo->getById($id);
                $title = $model->getTitle();
                $sliderRepo->deleteById($model->getId());
                // display success message
                $this->messageManager->addSuccess(__('The slider has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_slider_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_slider_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['slider_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a slider to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
