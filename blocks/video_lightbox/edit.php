<?php

use Punic\Misc;

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @var Concrete\Core\Block\View\BlockView $view
 * @var Concrete\Package\VideoLightbox\Block\VideoLightbox\Controller $controller
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Application\Service\UserInterface $ui
 * @var Concrete\Core\Application\Service\FileManager $al
 *
 * @var int|null $selectedImage
 * @var string $vTitle
 * @var string $description
 * @var string $vText
 * @var string $bWidth
 * @var int|null $fID
 * @var string $videoURL
 * @var string $vWidth
 * @var string $vHeight
 */

$tabsPrefix = version_compare(APP_VERSION, '9') < 0 ? 'ccm-tab-content-' : '';
?>
<div id="ccm-videolightbox-editor" v-cloak>

    <?= $ui->tabs([
        ['videolightbox-editor-button', t('Button'), true],
        ['videolightbox-editor-video', t('Video')],
        ['videolightbox-editor-videosize', t('Video Size')],
        ['videolightbox-editor-preview', t('Preview')],
    ]) ?>

    <div class="tab-content">
        <div class="ccm-tab-content tab-pane active" role="tabpanel" id="<?= $tabsPrefix ?>videolightbox-editor-button">
            <div class="form-group">
                <?= $form->label('buttonType', t('Button Type')); ?>
                <?= $form->select(
                    'buttonType',
                    [
                        'image' => t('Image'),
                        'text' => t('Text'),
                    ],
                    [
                        'v-model' => 'buttonType',
                        'required' => 'required',
                    ]
                ) ?>
            </div>
            <div v-show="buttonType === 'image'" class="form-group">
                <?= $al->image('ccm-videolightbox-editor-image-file', 'selectedImage', t('Choose Image'), $selectedImage) ?>
            </div>
            <div v-if="buttonType === 'image'" class="form-group">
                <?= $form->label('vTitle', t('Image Title')) ?>
                <?= $form->text('vTitle', '', ['v-model.trim' => 'vTitle', 'maxlength' => '255']) ?>
                <div class="small text-muted">
                    <?= t('This will also serve as the video title') ?>
                </div>
            </div>
            <div v-if="buttonType === 'image'" class="form-group">
                <?= $form->label('description', t('Description')) ?>
                <?= $form->text('description', '', ['v-model.trim' => 'description', 'maxlength' => '255']) ?>
                <div class="small text-muted">
                    <?= t('Optional text to display below the image') ?>
                </div>
            </div>
            <div v-if="buttonType === 'text'" class="form-group">
                <?= $form->label('vText', t('Button Text')) ?>
                <?= $form->text('vText', '', ['v-model.trim' => 'vText', 'maxlength' => '255', 'required' => 'required']) ?>
                <div class="small text-muted">
                    <?= t('This will also serve as the video title') ?>
                </div>
            </div>
            <div v-if="buttonType === 'text'" class="form-group">
                <?= $form->label('bWidth', t('Button width')) ?>
                <?= $form->number('bWidth', '', ['v-model.trim' => 'bWidth', 'maxlength' => '255', 'step' => '1', 'min' => '1']) ?>
            </div>
        </div>

        <div class="ccm-tab-content tab-pane" role="tabpanel" id="<?= $tabsPrefix ?>videolightbox-editor-video">
            <div class="form-group">
                <?= $form->label('videoType', t('Source of video')); ?>
                <?= $form->select(
                    'videoType',
                    [
                        'internal' => t('File Manager'),
                        'external' => t('External URL'),
                    ],
                    [
                        'v-model' => 'videoType',
                        'required' => 'required',
                    ]
                ) ?>
            </div>
            <div v-show="videoType === 'internal'" class="form-group">
                <?= $al->video('ccm-videolightbox-editor-video-file', 'fID', t('Choose Video'), $fID) ?>
                <div class="small text-muted">
                    <?= t('Usually browsers support these video formats: %s', Misc::joinAnd(['MP4', 'WebM'])) ?>
                </div>
            </div>
            <div v-if="videoType === 'external'" class="form-group">
                <?= $form->label('videoURL', t('External URL')) ?>
                <?= $form->url('videoURL', '', ['v-model.trim' => 'videoURL', 'maxlength' => '255', 'required' => 'required']) ?>
                <div class="small text-muted">
                    <?= t('Examples') ?>
                    <ul>
                        <li>https://youtu.be/YYUt1MdJ6TM</li>
                        <li>https://player.vimeo.com/video/142306245</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="ccm-tab-content tab-pane" role="tabpanel" id="<?= $tabsPrefix ?>videolightbox-editor-videosize">
            <div class="form-group">
                <?= $form->label('vWidth', t('Width')) ?>
                <?= $form->number('vWidth', '', ['v-model.trim' => 'vWidth', 'maxlength' => '255', 'required' => 'required', 'step' => '1', 'min' => '1']) ?>
            </div>
            <div class="form-group">
                <?= $form->label('vHeight', t('Height')) ?>
                <?= $form->number('vHeight', '', ['v-model.trim' => 'vHeight', 'maxlength' => '255', 'required' => 'required', 'step' => '1', 'min' => '1']) ?>
            </div>
            <div class="small text-muted">
                <?= t('Examples') ?>:
                <ul>
                    <li v-for="s in sampleSizes">
                        <a href="#" v-on:click.prevent="vWidth = s[1]; vHeight = s[2]">{{ s[0] }}</a>:
                        <a href="#" v-on:click.prevent="vWidth = s[1]">{{ s[1] }}</a>
                        &times;
                        <a href="#" v-on:click.prevent="vHeight = s[2]">{{ s[2] }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="ccm-tab-content tab-pane" role="tabpanel" id="<?= $tabsPrefix ?>videolightbox-editor-preview">
            <button v-bind:disabled="busy" v-on:click.prevent="showPreview" class="btn btn-default btn-secondary"><?= t('Open Popup Video') ?></button>
        </div>
    </div>
</div>
<?php

$template = ob_get_contents();
ob_end_clean();
$scripts = [];

$template = preg_replace_callback(
    '#<script\b[^>]*>(.*?)</script>#is',
    static function (array $matches) use (&$scripts) {
        $scripts[] = trim($matches[1]);
        return '';
    },
    $template
);

echo $template;
?>

<script>
$(document).ready(function() {

function launchApp() {
    new Vue({
        el: '#ccm-videolightbox-editor',
        data() {
            return <?= json_encode([
                'busy' => false,
                'buttonType' => $selectedImage ? 'image' : 'text',
                'vTitle' => $vTitle,
                'description' => $description,
                'vText' => $vText,
                'bWidth' => $bWidth,
                'videoType' => $fID ? 'internal' : 'external',
                'videoURL' => $videoURL,
                'vWidth' => $vWidth,
                'vHeight' => $vHeight,
                'sampleSizes' => [
                    ['YouTube', 853, 480],
                    ['Vimeo', 500, 281],
                ],
            ]) ?>;
        },
        mounted() {
            this.hookInvalidFields();
            var runScripts = function() {
                <?= implode("\n", $scripts) ?>;
            };
            <?php
            if (version_compare(APP_VERSION, '9') < 0) {
                ?>
                var tmr;
                tmr = setInterval(
                    function() {
                        if ($.fn.concreteFileSelector) {
                            clearInterval(tmr);
                            runScripts();
                        }
                    },
                    100
                );
                <?php
            } else {
                ?>
                runScripts();
                <?php
            }
            ?>
        },
        methods: {
            async showPreview() {
                if (this.busy) {
                    return;
                }
                this.busy = true;
                let a = null;
                try {
                    const form = this.$el.closest('form');
                    const body = new FormData(form);
                    body.delete(<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>);
                    body.append('__ccm_consider_request_as_xhr', '1');
                    body.append(<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, <?= json_encode($token->generate('ccm-video_lightbox-preview')) ?>);
                    const response = await window.fetch(
                        <?= json_encode((string) $controller->getActionURL('generate_preview')) ?>,
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                            method: 'POST',
                            body,
                            cache: 'no-store',
                        }
                    );
                    const responseData = await response.json();
                    if (responseData.error) {
                        throw new Error(responseData.error.message || responseData.error);
                    }
                    a = document.createElement('a');
                    document.body.appendChild(a);
                    for (const [name, value] of Object.entries(responseData)) {
                        a.setAttribute(name, value);
                    }
                    new window.ccmVideoLightbox(a);
                    a.click();
                } catch (e) {
                    window.ConcreteAlert.error({
                        message: e.message || e.toString(),
                        delay: 2000,
                    });
                } finally {
                    this.busy = false;
                    if (a) {
                        document.body.removeChild(a);
                    }
                }
            },
            hookInvalidFields() {
                const form = this.$el.closest('form');
                let reporting = false;
                form.addEventListener(
                    'invalid',
                    (e) => {
                        if (reporting) {
                            return;
                        }
                        const field = e.target;
                        if (!field) {
                            return;
                        }
                        const tab = field.closest('.tab-pane');
                        if (!tab) {
                            return;
                        }
                        const id = tab.getAttribute('id').substring(<?= json_encode($tabsPrefix) ?>.length);
                        const link = form.querySelector(`.nav-tabs a[href="#${id}"]`) || form.querySelector(`a[data-tab="${id}"]`);
                        if (!link) {
                            return;
                        }
                        link.click();
                        reporting = true;
                        try {
                            field.reportValidity();
                        } finally {
                            reporting = false;
                        }
                    },
                    true
                );
            }
        },
    });
}

if (window.Vue) {
    launchApp();
} else {
    let launchAppTimer;
    launchAppTimer = setInterval(
        function() {
            if (window.Vue) {
                clearInterval(launchAppTimer);
                launchApp();
            }
        },
        100
    );
}

});
</script>