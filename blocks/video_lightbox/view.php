<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Block\View\BlockView $view
 * @var Concrete\Package\VideoLightbox\Block\VideoLightbox\Controller $controller
 *
 * @var string $faPlayIcon
 *
 * @var Concrete\Core\Entity\File\Version|null $selectedImageFileVersion
 * @var string $vTitle
 * @var string $description
 *
 * @var string $vText
 * @var string $bWidth
 *
 * @var string $actualVideoURL
 *
 * @var string $vWidth
 * @var string $vHeight
 *
 * @var string[] $editModeErrorMessages may not be set
 */

if ($editModeErrorMessages !== []) {
    ?>
    <div class="ccm-edit-mode-disabled-item">
        <div style="white-space: pre-wrap"><?= implode("\n\n", array_map('nl2br', $editModeErrorMessages)) ?></div>
    </div>
    <?php
    return;
}

if (($vText === '' && $selectedImageFileVersion === null) || $actualVideoURL === '') {
    return;
}

if ($vText !== '') {
    ?>
    <a
        class="ccm-videolighbox ccm-videolighbox-text"
        href="<?= h($actualVideoURL) ?>"
        target="_blank"
        data-width="<?= $vWidth ?>"
        data-height="<?= $vHeight ?>"
        title="<?= h($vText) ?>"
        <?= $bWidth === '' ? '' : "style=\"width: {$bWidth}\"" ?>
    >
        <?= h($vText) ?>
        <i class="<?= $faPlayIcon ?>" aria-hidden="true"></i>
    </a>
    <?php
} else {
    ?>
    <div class="ccm-videolighbox ccm-videolighbox-image">
        <a
            href="<?= h($actualVideoURL) ?>"
            target="_blank"
            data-width="<?= $vWidth ?>"
            data-height="<?= $vHeight ?>"
            title="<?= h($vTitle) ?>"
        >
            <img
                src="<?= h($selectedImageFileVersion->getRelativePath()) ?>"
                alt="<?= h($vTitle) ?>"
                loading="lazy"
            />
            <i></i>
        </a>
        <?php
        if ($description !== '') {
            ?>
            <div><?= h($description) ?></div>
            <?php
        }
        ?>
    </div>
    <?php
}

