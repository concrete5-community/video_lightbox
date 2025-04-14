(function() {

function VideoLightbox(link) {
    if (link.ccmVideoLightbox) return;
    link.ccmVideoLightbox = this;
    this.link = link;
    link.addEventListener('click', (e) => {
        e.preventDefault();
        this.open();
    });
}

VideoLightbox.prototype = {
    open: function() {
        const videoStyle = [
            'width: ' + (Number(this.link.dataset['width']) || 640) + 'px',
            'height: ' + (Number(this.link.dataset['height']) || 320) + 'px',
        ].join('; ');
        const viewer = buildViewerHtml(this.link.getAttribute('href'), videoStyle);
        const dialog = document.createElement('dialog');
        dialog.className = 'ccm-videolighbox-dialog';
        dialog.innerHTML = viewer;
        const titleText = this.link.getAttribute('title');
        if (titleText) {
            const title = document.createElement('div');
            title.className = 'ccm-videolighbox-title';
            title.textContent = titleText;
            dialog.appendChild(title);
        }
        const closeDialog = () => {
            dialog.close();
            try {
                document.body.removeChild(dialog);
            } catch (e) {
            }
        };
        const closeButton = document.createElement('div');
        closeButton.innerText = '\ud83d\uddd9'; // CANCELLATION X
        closeButton.className = 'ccm-videolighbox-dialog-close';
        closeButton.addEventListener('click', closeDialog);
        dialog.appendChild(closeButton);
        document.body.appendChild(dialog);
        dialog.addEventListener('close', closeDialog);
        dialog.addEventListener('click', (e) => {
            if (e.target !== dialog) {
                return;
            }
            const dialogArea = dialog.getBoundingClientRect();
            if (
                e.clientX < dialogArea.left
                || e.clientX > dialogArea.left + dialogArea.width
                || e.clientY < dialogArea.top
                || e.clientY > dialogArea.top + dialogArea.height
            ) {
                closeDialog();
            }
        });
        dialog.showModal();
    },
};

/**
 * @param {URL} url
 * @returns {{type: 'youtube' | 'vimeo', id: string} | null}
 */
function extractVideoInfo(url) {
    let m;
    switch (url.hostname.toLowerCase()) {
        case 'www.youtube.com':
        case 'youtube.com':
        case 'www.youtube-nocookie.com':
        case 'youtube-nocookie.com':
            if (m = url.pathname.match(/^\/(embed|v)\/([\w\-]+)/)) return {type: 'youtube', id: m[2]};
            if (m = url.searchParams.get('v')) return {type: 'youtube', id: m};
            break;
        case 'youtu.be':
            if (m = url.pathname.match(/^\/([\w\-]+)/)) return {type: 'youtube', id: m[1]};
            break;
        case 'www.vimeo.com':
        case 'vimeo.com':
            if (m = url.pathname.match(/^\/(\d+)/)) return {type: 'vimeo', id: m[1]};
            break;
        case 'player.vimeo.com':
            if (m = url.pathname.match(/^\/video\/(\d+)/)) return {type: 'vimeo', id: m[1]};
            break;
    }
    return null;
}

/**
 * @param {string} videoUrl
 * @param {string} style
 *
 * @returns {string}
 */
function buildViewerHtml(videoUrl, style) {
    try {
        const url = new URL(videoUrl);
        const videoInfo = extractVideoInfo(url);
        if (videoInfo) {
            switch (videoInfo.type) {
                case 'youtube':
                    return '<iframe style="' + style + '" src="https://www.youtube-nocookie.com/embed/' + videoInfo.id + '?rel=0&autoplay=1" allow="autoplay; fullscreen" allowfullscreen></iframe>';
                case 'vimeo':
                    return '<iframe style="' + style + '" src="https://player.vimeo.com/video/' + videoInfo.id + '?autoplay=1" allow="autoplay; fullscreen" allowfullscreen></iframe>';
            }
        }
    } catch (e) {
    }

    return '<video style="' + style + '" autoplay src="' + encodeURI(videoUrl) + '"></video>';
}

function parseLinks() {
    if (!document.documentElement.classList.contains('ccm-edit-mode')) {
        document.querySelectorAll('a.ccm-videolighbox-text, .ccm-videolighbox-image>a').forEach((link) => new VideoLightbox(link));
    }
}

window.ccmVideoLightbox = VideoLightbox;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', parseLinks);
}
else {
    parseLinks();
}
})();
