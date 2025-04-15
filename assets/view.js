(function() {
'use strict';

/**
 * @param {string} tagName 
 * @param {Object<string,string>} attributes 
 *
 * @returns {HTMLElement}
 */
function createElement(tagName, attributes) {
    const element = document.createElement(tagName);
    for (const [key, value] of Object.entries(attributes)) {
        if (key.startsWith('data-')) {
            element.dataset[key.substring(5)] = value;
        } else {
            element.setAttribute(key, value);
        }
    }
    return element;
}

/**
 * @constructor
 * @param {HTMLAnchorElement} link 
 */
function VideoLightbox(link) {
    if (link.ccmVideoLightbox) {
        return;
    }
    link.ccmVideoLightbox = this;
    this.link = link;
    link.addEventListener('click', (e) => {
        e.preventDefault();
        this.open();
    });
}

VideoLightbox.prototype = {
    open() {
        new Viewer(
            this.link.getAttribute('href'),
            Number(this.link.dataset['width']) || 640,
            Number(this.link.dataset['height']) || 320,
            this.link.getAttribute('title') || ''
        );
    },
};

/**
 * @constructor
 * @param {string} videoUrl
 * @param {Number} fullWidth
 * @param {Number} fullHeight
 * @param {string} titleText
 */
function Viewer(videoUrl, fullWidth, fullHeight, titleText) {
    this.fullWidth = fullWidth;
    this.fullHeight = fullHeight;
    this.aspectRatio = fullWidth / fullHeight;
    this.dialog = createElement('dialog', {'class': 'ccm-videolightbox-dialog'});
    this.viewer = buildViewerElement(videoUrl);
    this.viewer.width = fullWidth;
    this.viewer.height = fullHeight;
    this.dialog.appendChild(this.viewer);
    if (titleText) {
        this.title = createElement('div', {'class': 'ccm-videolightbox-title'});
        this.title.textContent = titleText;
        this.dialog.appendChild(this.title);
    }
    const closeButton = createElement('div', {'class': 'ccm-videolightbox-dialog-close'});
    closeButton.innerText = '\ud83d\uddd9'; // CANCELLATION X
    closeButton.addEventListener('click', () => this.dispose());
    this.dialog.appendChild(closeButton);
    this.dialog.addEventListener('click', (e) => {
        if (e.target === this.dialog) {
            this.dispose();
        }
    });
    this.dialog.addEventListener('close', () => this.dispose());
    let resizeScheduled = false;
    this.resizeHandler = () => {
        if (resizeScheduled) {
            return;
        }
        resizeScheduled = true;
        window.requestAnimationFrame(() => {
            resizeScheduled = false;
            this.resize();
        });
    };
    this.disposed = false;
    window.addEventListener('resize', this.resizeHandler);
    document.body.appendChild(this.dialog);
    this.dialog.showModal();
    this.resize();
}
Viewer.prototype = {
    resize() {
        console.log('resize');
        if (this.disposed) {
            return;
        }
        const maxWidth = Math.min(window.innerWidth - 80, this.fullWidth);
        const maxHeight = Math.min(window.innerHeight - 80 - (this.title?.offsetHeight || 0), this.fullHeight);
        if (maxWidth / maxHeight > this.aspectRatio) {
            this.viewer.width = maxHeight * this.aspectRatio;
            this.viewer.height = maxHeight;
        } else {
            this.viewer.width = maxWidth;
            this.viewer.height = maxWidth / this.aspectRatio;
        }
    },
    dispose() {
        if (this.disposed !== false) {
            return;
        }
        this.disposed = true;
        window.removeEventListener('resize', this.resizeHandler);
        this.dialog.remove();
    },
};

/**
 * @param {URL} url
 *
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
 *
 * @returns {HTMLIFrameElement | HTMLVideoElement}
 */
function buildViewerElement(videoUrl) {
    try {
        const url = new URL(videoUrl);
        const videoInfo = extractVideoInfo(url);
        if (videoInfo) {
            switch (videoInfo.type) {
                case 'youtube':
                    return createElement('iframe', {
                        src: 'https://www.youtube-nocookie.com/embed/' + videoInfo.id + '?rel=0&autoplay=1',
                        allow: 'autoplay; fullscreen',
                        allowfullscreen: 'allowfullscreen',
                    });
                case 'vimeo':
                    return createElement('iframe', {
                        src: 'https://player.vimeo.com/video/' + videoInfo.id + '?autoplay=1',
                        allow: 'autoplay; fullscreen',
                        allowfullscreen: 'allowfullscreen',
                    });
            }
        }
    } catch (e) {
    }
    return createElement('video', {
        src: videoUrl,
        autoplay: 'autoplay',
        controls: 'controls',
    });
}


window.ccmVideoLightbox = VideoLightbox;

(function() {
    function parseLinks() {
        if (!document.documentElement.classList.contains('ccm-edit-mode')) {
            document.querySelectorAll('a.ccm-videolightbox-text, .ccm-videolightbox-image>a').forEach((link) => new VideoLightbox(link));
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', parseLinks);
    }
    else {
        parseLinks();
    }        
})();

})();
