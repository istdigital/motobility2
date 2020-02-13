<?php

namespace Expert\EditorPdf\Plugin;

class Storage
{
    public function beforeResizeFile(
        \Magento\Cms\Model\Wysiwyg\Images\Storage $subject,
        $source, 
        $keepRatio = true
    ) {

        $path_parts = pathinfo($source);
        if(in_array($path_parts['extension'],['pdf','mp4','webp'])){
            return false;
        }
        return [$source,$keepRatio];
    }
}
