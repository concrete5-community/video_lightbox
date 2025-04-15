<?php

namespace Concrete\Package\VideoLightbox;

use Concrete\Core\Asset\AssetList;
use Concrete\Core\Package\Package;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package
{
    protected $pkgHandle = 'video_lightbox';

    protected $pkgVersion = '2.0.2';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.2';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Video Lightbox II');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Show videos in popups');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        parent::install();
        $this->installContentFile('config/install.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgrade()
     */
    public function upgrade()
    {
        parent::upgrade();
        $this->installContentFile('config/install.xml');
    }

    public function on_start()
    {
        $pkg = $this->getPackageEntity();
        $al = AssetList::getInstance();
        $al->register('css', 'ccm-video-lightbox', 'assets/view.css', ['version' => $this->pkgVersion, 'combine' => true, 'minify' => true], $pkg);
        $al->register('javascript', 'ccm-video-lightbox', 'assets/view.js', ['version' => $this->pkgVersion, 'combine' => true, 'minify' => true], $pkg);
        $al->registerGroup('ccm-video-lightbox', [
            ['css', 'ccm-video-lightbox'],
            ['javascript', 'ccm-video-lightbox'],
        ]);
    }
}
