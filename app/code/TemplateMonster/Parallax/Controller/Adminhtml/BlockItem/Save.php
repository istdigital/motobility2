<?php

namespace TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;

use TemplateMonster\Parallax\Api\Data\BlockItemInterface;
use TemplateMonster\Parallax\Helper\Data as ParallaxHelper;
use TemplateMonster\Parallax\Api\BlockItemRepositoryInterface;
use TemplateMonster\Parallax\Controller\Adminhtml\BlockItem;
use Magento\Framework\File\Uploader;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;

/**
 * Save action.
 */
class Save extends BlockItem
{
    /**
     * @var ParallaxHelper
     */
    protected $_parallaxHelper;

    /**
     * @var BlockItemRepositoryInterface
     */
    protected $_blockItemRepository;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * Save constructor.
     *
     * @param ParallaxHelper               $parallaxHelper
     * @param BlockItemRepositoryInterface $blockItemRepository
     * @param UploaderFactory              $uploaderFactory
     * @param Filesystem                   $filesystem
     * @param Action\Context               $context
     */
    public function __construct(
        ParallaxHelper $parallaxHelper,
        BlockItemRepositoryInterface $blockItemRepository,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Action\Context $context
    ) {
        $this->_parallaxHelper = $parallaxHelper;
        $this->_blockItemRepository = $blockItemRepository;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->_getRequestData();
        if ($data) {
            $model = $this->_initModel();
            $model->addData($data);

            try {
                $this->_uploadImage($model);
                $this->_uploadVideos($model);
                $this->_blockItemRepository->save($model);
                $this->messageManager->addSuccessMessage(__('The block item has been successfully saved.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
                }
                $this->_getSession()->setFormData(false);

                return $resultRedirect->setPath('*/block/edit', ['block_id' => $model->getBlockId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the block.'));
            }
            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId()]);
        }

        return $resultRedirect->setPath('*/*');
    }

    /**
     * Init model.
     *
     * @param int|null $id
     *
     * @return BlockItemInterface|AbstractModel
     */
    protected function _initModel($id = null)
    {
        $id = $id ?: $this->getRequest()->getParam('item_id');

        return $id
            ? $this->_blockItemRepository->getById($id)
            : $this->_blockItemRepository->getModelInstance();
    }

    /**
     * Handle image uploading.
     *
     * @param AbstractModel $model
     *
     * @throws \Exception
     */
    protected function _uploadImage(AbstractModel $model)
    {
        $media = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDirPath = $media->getAbsolutePath(ParallaxHelper::IMAGE_DIR);

        $image = $this->getRequest()->getParam('image');
        if (isset($image['delete']) && $image['delete']) {
            $this->_deleteImage($model, $imageDirPath);
        }

        if (count($this->getRequest()->getFiles())) {
            try {
                /** @var \Magento\MediaStorage\Model\File\Uploader $fileUploader */
                $fileUploader = $this->_uploaderFactory->create(['fileId' => 'image']);
                $fileUploader->setAllowedExtensions($this->_parallaxHelper->getAvailableImageFormats());
                $fileUploader->setAllowRenameFiles(true);

                if (!$this->_validateFileMaxSize($fileUploader->getFileSize(), ParallaxHelper::IMAGE_MAX_SIZE)) {
                    throw new LocalizedException(__('Image size exceeds limit of %1 mb.', ParallaxHelper::IMAGE_MAX_SIZE));
                }

                if ($model->getData('image')) {
                    $this->_deleteImage($model, $imageDirPath);
                }

                $result = $fileUploader->save($imageDirPath);
                $model->setData('image', $result['name']);
            }
            catch (\Exception $e) {
                if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Handle video uploading.
     *
     * @param AbstractModel $model
     *
     * @throws \Exception
     */
    protected function _uploadVideos(AbstractModel $model)
    {
        $media = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $videoDirPath = $media->getAbsolutePath(ParallaxHelper::VIDEO_DIR);
        $fields = [BlockItemInterface::VIDEO_MP4, BlockItemInterface::VIDEO_WEBM];

        foreach ($fields as $field) {
            $video = $this->getRequest()->getParam($field);
            if (isset($video['delete']) && $video['delete']) {
                unlink($videoDirPath . DIRECTORY_SEPARATOR . $model->getData($field));
                $model->setData($field, null);
            }
        }

        if (count($this->getRequest()->getFiles())) {
            foreach ($fields as $field) {
                try {
                    /** @var \Magento\MediaStorage\Model\File\Uploader $fileUploader */
                    $fileUploader = $this->_uploaderFactory->create(['fileId' => $field]);
                    $fileUploader->setAllowedExtensions($this->_parallaxHelper->getAvailableVideoFormats());
                    $fileUploader->setAllowRenameFiles(true);

                    if (!$this->_validateFileMaxSize($fileUploader->getFileSize(), ParallaxHelper::VIDEO_MAX_SIZE)) {
                        throw new LocalizedException(__('Video size exceeds limit of %1 mb.', ParallaxHelper::VIDEO_MAX_SIZE));
                    }

                    $result = $fileUploader->save($videoDirPath);
                    $model->setData($field, $result['name']);
                }
                catch (\Exception $e) {
                    if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Get request data.
     *
     * @return array
     */
    protected function _getRequestData()
    {
        $ignoredParams = [BlockItemInterface::IMAGE, BlockItemInterface::VIDEO_MP4, BlockItemInterface::VIDEO_WEBM];

        return array_diff_key(
            $this->getRequest()->getPostValue(),
            array_combine($ignoredParams, $ignoredParams)
        );
    }

    /**
     * Delete image.
     *
     * @param AbstractModel $model
     * @param string        $imageDirPath
     */
    protected function _deleteImage(AbstractModel $model, $imageDirPath)
    {
        unlink($imageDirPath . DIRECTORY_SEPARATOR . $model->getData('image'));
        $model->setData('image', null);
    }

    /**
     * Validate file max size.
     *
     * @param int $fileSize
     * @param int $maxFileSize
     *
     * @return bool
     */
    protected function _validateFileMaxSize($fileSize, $maxFileSize)
    {
        return $fileSize <= ($maxFileSize * 1024 * 1024);
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TemplateMonster_Parallax::item_save');
    }
}
