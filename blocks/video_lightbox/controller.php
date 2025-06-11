<?php

namespace Concrete\Package\VideoLightbox\Block\VideoLightbox;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\File\File;
use Concrete\Core\File\Tracker\FileTrackableInterface;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Page;
use Concrete\Core\Statistics\UsageTracker\AggregateTracker;

class Controller extends BlockController implements FileTrackableInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$helpers
     */
    protected $helpers = [];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btTable
     */
    protected $btTable = 'btVideoLightbox';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btInterfaceWidth
     */
    protected $btInterfaceWidth = 600;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btInterfaceHeight
     */
    protected $btInterfaceHeight = 550;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btCacheBlockOutput
     */
    protected $btCacheBlockOutput = true;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$supportSavingNullValues
     */
    protected $supportSavingNullValues = true;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::$btExportFileColumns
     */
    protected $btExportFileColumns = ['selectedImage', 'fID'];

    /**
     * @var \Concrete\Core\Statistics\UsageTracker\AggregateTracker|null
     */
    protected $tracker;

    /**
     * Button image.
     *
     * @var int|string|null
     */
    protected $selectedImage;

    /**
     * Button image - Title/alternative text.
     *
     * @var string|null
     */
    protected $vTitle;

    /**
     * Button image - Video title/description.
     *
     * @var string|null
     */
    protected $description;

    /**
     * Button text.
     *
     * @var string|null
     */
    protected $vText;

    /**
     * Button text - Width.
     *
     * @var string|null
     */
    protected $bWidth;

    /**
     * Video - Local file.
     *
     * @var int|string|null
     */
    protected $fID;

    /**
     * Video - External URL.
     *
     * @var string|null
     */
    protected $videoURL;

    /**
     * Popup - Width.
     *
     * @var string|null
     */
    protected $vWidth;

    /**
     * Popup - Height.
     *
     * @var string|null
     */
    protected $vHeight;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeName()
     */
    public function getBlockTypeName()
    {
        return t("Video Lightbox");
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeDescription()
     */
    public function getBlockTypeDescription()
    {
        return t('Show videos in popups');
    }

    public function add()
    {
        $this->prepareEditUI();
        $this->set('selectedImage', null);
        $this->set('vTitle', '');
        $this->set('description', '');
        $this->set('vText', '');
        $this->set('bWidth', '');
        $this->set('fID', null);
        $this->set('videoURL', '');
        $this->set('vWidth', '');
        $this->set('vHeight', '');
    }

    public function edit()
    {
        $this->prepareEditUI();
        $this->set('selectedImage', ((int) $this->selectedImage) ?: null);
        $this->set('fID', ((int) $this->fID) ?: null);
    }

    public function action_generate_preview()
    {
        $token = $this->app->make('token');
        if (!$token->validate('ccm-video_lightbox-preview')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $args = $this->normalizeArgs($this->request->request->all());
        if (!is_array($args)) {
            throw new UserMessageException(implode("\n", $args->getList()));
        }
        $actualVideoURL = $args['videoURL'];
        if ($actualVideoURL === '') {
            $actualVideoURL = (string) File::getByID($args['fID'])->getApprovedVersion()->getRelativePath();
        }
        $attrs = [
            'href' => $actualVideoURL,
            'target' => '_blank',
            'data-width' => $args['vWidth'],
            'data-height' => $args['vHeight'],
        ];

        return $this->app->make(ResponseFactoryInterface::class)->json($attrs);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::registerViewAssets()
     */
    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('ccm-video-lightbox');
    }

    public function view()
    {
        $this->requireAsset('css', 'font-awesome');
        if (version_compare(APP_VERSION, '9') < 0) {
            $this->set('faPlayIcon', 'fa fa-play-circle');
        } else {
            $this->set('faPlayIcon', 'far fa-play-circle');
        }
        $editModeErrorMessages = [];
        $selectedImageFileVersion = null;
        if ($this->selectedImage) {
            $file = File::getByID($this->selectedImage);
            $selectedImageFileVersion = $file ? $file->getApprovedVersion() : null;
        }
        $this->set('selectedImageFileVersion', $selectedImageFileVersion);
        if ($this->vText === '' && $selectedImageFileVersion === null) {
            $c = Page::getCurrentPage();
            if ($c && !$c->isError && $c->isEditMode()) {
                $loc = $this->app->make(Localization::class);
                $loc->pushActiveContext(Localization::CONTEXT_UI);
                $editModeErrorMessages[] = t("Button can't be created since we don't have neither its text nor its image");
                $loc->popActiveContext();
            }
        }
        $actualVideoURL = $this->videoURL;
        if ($actualVideoURL === '' && $this->fID) {
            $file = File::getByID($this->fID);
            $fIDFileVersion = $file ? $file->getApprovedVersion() : null;
            if ($fIDFileVersion) {
                $actualVideoURL = (string) $fIDFileVersion->getRelativePath();
            } else {
                $c = Page::getCurrentPage();
                if ($c && !$c->isError && $c->isEditMode()) {
                    $loc = $this->app->make(Localization::class);
                    $loc->pushActiveContext(Localization::CONTEXT_UI);
                    $editModeErrorMessages[] = t("Button can't be created since we don't have neither its text nor its image");
                    $loc->popActiveContext();
                }
            }
        }
        if ($actualVideoURL === '') {
            $c = Page::getCurrentPage();
            if ($c && !$c->isError && $c->isEditMode()) {
                $loc = $this->app->make(Localization::class);
                $loc->pushActiveContext(Localization::CONTEXT_UI);
                $editModeErrorMessages[] = t("The video URL is not available");
                $loc->popActiveContext();
            }
        }
        $this->set('actualVideoURL', $actualVideoURL);
        $this->set('editModeErrorMessages', $editModeErrorMessages);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::validate()
     */
    public function validate($args)
    {
        $check = $this->normalizeArgs(is_array($args) ? $args : []);

        return is_array($check) ? true : $check;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::save()
     */
    public function save($args)
    {
        $normalized = $this->normalizeArgs(is_array($args) ? $args : []);
        if (!is_array($normalized)) {
            throw new UserMessageException(implode("\n", $normalized->getList()));
        }
        parent::save($normalized);
        $this->selectedImage = $normalized['selectedImage'];
        $this->fID = $normalized['fID'];
        if (version_compare(APP_VERSION, '9.0.2') < 0) {
            $this->getTracker()->track($this);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::delete()
     */
    public function delete()
    {
        if (version_compare(APP_VERSION, '9.0.2') < 0) {
            $this->getTracker()->forget($this);
        }
        parent::delete();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedFiles()
     */
    public function getUsedFiles()
    {
        $result = [];
        if (($id = (int) $this->selectedImage) > 0) {
            $result[] = $id;
        }
        if (($id = (int) $this->fID) > 0) {
            $result[] = $id;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\File\Tracker\FileTrackableInterface::getUsedCollection()
     */
    public function getUsedCollection()
    {
        return $this->getCollectionObject();
    }

    private function prepareEditUI()
    {
        if (version_compare(APP_VERSION, '9') < 0) {
            $this->requireAsset('javascript', 'vue');
            $this->addHeaderItem('<style>.ccm-ui [v-cloak] { display: none!important; }</style>');
        }
        $this->requireAsset('ccm-video-lightbox');
        $this->set('token', $this->app->make('token'));
        $this->set('form', $this->app->make('helper/form'));
        $this->set('ui', $this->app->make('helper/concrete/ui'));
        $this->set('al', $this->app->make('helper/concrete/asset_library'));
    }

    /**
     * @param array $args
     *
     * @return \Concrete\Core\Error\Error|\Concrete\Core\Error\ErrorList\ErrorList|array
     */
    protected function normalizeArgs(array $args)
    {
        $args += [
            'buttonType' => '',
            'videoType' => '',
        ];
        $errors = $this->app->make('helper/validation/error');
        $normalized = [
            'selectedImage' => null,
            'vTitle' => '',
            'description' => '',
            'vText' => '',
            'bWidth' => '',
            'fID' => null,
            'videoURL' => '',
            'vWidth' => '',
            'vHeight' => '',
        ];
        $normalized['selectedImage'] = $args['buttonType'] === 'text' || empty($args['selectedImage']) ? null : (int) $args['selectedImage'];
        if ($normalized['selectedImage'] !== null) {
            $file = File::getByID($normalized['selectedImage']);
            $fileVersion = $file ? $file->getApprovedVersion() : null;
            if (!$fileVersion) {
                $errors->add(t('Please specify the text or the image of the button'));
            }
            if (isset($args['vTitle'])) {
                $normalized['vTitle'] = trim((string) $args['vTitle']);
                if (mb_strlen($normalized['vTitle']) > 255) {
                    $errors->add(t('The title of the button image is too long (maximum length: %s characters)', 255));
                }
            }
            if (isset($args['description'])) {
                $normalized['description'] = trim((string) $args['description']);
                if (mb_strlen($normalized['description']) > 255) {
                    $errors->add(t('The description of the button image is too long (maximum length: %s characters)', 255));
                }
            }
        } else {
            $normalized['vText'] = $args['buttonType'] !== 'image' && isset($args['vText']) ? trim($args['vText']) : '';
            if ($normalized['vText'] !== '') {
                if (mb_strlen($normalized['vText']) > 255) {
                    $errors->add(t('The button text is too long (maximum length: %s characters)', 255));
                }
                $int = empty($args['bWidth']) ? 0 : (int) trim($args['bWidth']);
                $normalized['bWidth'] = $int > 0 ? (string) $int : '';
            } else {
                $errors->add(t('Please specify the text or the image of the button'));
            }
        }
        $normalized['fID'] = $args['videoType'] === 'external' || empty($args['fID']) ? null : (int) $args['fID'];
        if ($normalized['fID'] !== null) {
            $file = File::getByID($normalized['fID']);
            $fileVersion = $file ? $file->getApprovedVersion() : null;
            if (!$fileVersion) {
                $errors->add(t('Video missing'));
            }
        } else {
            $normalized['videoURL'] = $args['videoType'] !== 'internal' || isset($args['videoURL']) ? trim($args['videoURL']) : '';
            if ($normalized['videoURL'] === '') {
                $errors->add(t('Please specify the video to be displayed'));
            }
        }
        $int = empty($args['vWidth']) ? 0 : (int) trim($args['vWidth']);
        if ($int > 0) {
            $normalized['vWidth'] = (string) $int;
        } else {
            $errors->add(t('Please specify the video width'));
        }
        $int = empty($args['vHeight']) ? 0 : (int) trim($args['vHeight']);
        if ($int > 0) {
            $normalized['vHeight'] = (string) $int;
        } else {
            $errors->add(t('Please specify the video height'));
        }

        return $errors->has() ? $errors : $normalized;
    }

    /**
     * @return \Concrete\Core\Statistics\UsageTracker\AggregateTracker
     */
    protected function getTracker()
    {
        if ($this->tracker === null) {
            $this->tracker = $this->app->make(AggregateTracker::class);
        }

        return $this->tracker;
    }
}
