<?php
/**
 * Mage-World
 *
 *  @category    Mage-World
 *  @package     MW
 *  @author      Mage-world Developer
 *
 *  @copyright   Copyright (c) 2018 Mage-World (https://www.mage-world.com/)
 */

namespace MW\Downloads\Controller\Adminhtml\Downloads;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends Action
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_logger;

    protected $fileSystem;
 
    protected $uploaderFactory;

    /**
     * AbstractAction constructor.
     *
     * @param Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory
    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;

        $this->_eventManager = $context->getEventManager();
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            /** @var \MW\Downloads\Model\DownloadsCategory $model */
            $model = $this->_objectManager->create('MW\Downloads\Model\Downloads');

            $destinationPath = $this->getDestinationPath();
            try {

                $data = $this->getRequest()->getPostValue();

                if(isset($_FILES['document']['tmp_name']) && !empty($_FILES['document']['tmp_name'])){

                    $uploader = $this->uploaderFactory->create(['fileId' => 'document'])
                    ->setAllowCreateFolders(true)
                    ->setAllowRenameFiles(true)
                    ->setAllowedExtensions(['pdf']);

                    $file = $uploader->save($destinationPath);
                    $data['size'] = number_format((int)$file['size'] / (1024 * 1024),2). 'MB';
                    $data['document'] = '/downloads/' . $file['file'];
                }
                

                // if ($file = !$uploader->save($destinationPath)) {
                //     throw new LocalizedException(
                //         __('File cannot be saved to path: $1', $destinationPath)
                //     );
                // }else{
                //     print_r($file);die();
                // }

                
                if (isset($data['id']) && $id = $data['id']) {
                    $model = $model->load($id);
                    if(isset($_FILES['document']['tmp_name']) && !empty($_FILES['document']['tmp_name'])){
                        $d = $model->getDocument();
                        if(!empty($d)){
                            unlink($this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('/') . $model->getDocument());
                        }
                    }
                } else {
                    unset($data['id']);
                }


                $model->setData($data);

                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $model->save();
                $this->messageManager->addSuccess(__('You saved the Downloads.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the Downloads data. Please review the error log.')
                );
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath('/downloads/');
    }
}