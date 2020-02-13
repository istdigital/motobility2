<?php

namespace TemplateMonster\ThemeOptions\Model\Config\Backend;

use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Filesystem;

/**
 * Image backend model.
 *
 * @package TemplateMonster\ThemeOptions\Model\Config\Backend
 */
class Image extends ArraySerialized
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Upload max file size in kilobytes.
     *
     * @var int
     */
    protected $_maxFileSize = 0;

    /**
     * Image constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param UploaderFactory       $uploaderFactory
     * @param Filesystem            $filesystem
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_filesystem = $filesystem;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_uploaderFactory = $uploaderFactory;
    }


    public function beforeSave()
    {
        $value = (array) $this->getValue();
        foreach ($value as $id => &$row) {
            if (isset($row['upload']) && is_array($row['upload'])) {
                $row['image'] = $this->_uploadImage($row['upload']);
            }
        }
        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * Validation callback for checking max file size
     *
     * @param  string $filePath Path to temporary uploaded file
     * @return void
     * @throws LocalizedException
     */
    public function validateMaxSize($filePath)
    {
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        if ($this->_maxFileSize > 0 && $directory->stat(
                $directory->getRelativePath($filePath)
            )['size'] > $this->_maxFileSize * 1024
        ) {
            throw new LocalizedException(
                __('The file you\'re uploading exceeds the server size limit of %1 kilobytes.', $this->_maxFileSize)
            );
        }
    }

    protected function _uploadImage($upload)
    {
        try {
            $uploader = $this->_uploaderFactory->create(['fileId' => $upload]);
            $uploader->setAllowedExtensions($this->_getAllowedExtensions());
            $uploader->setAllowRenameFiles(true);
            $uploader->addValidateCallback('size', $this, 'validateMaxSize');
            $uploader->save('pub/media/theme_options/social_icons');
        }
        catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new LocalizedException(__('%1', $e->getMessage()));
            }
        }

        return $upload['name'];
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }
}