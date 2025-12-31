/**
 * app.js
 * 
 * Put here your application specific JavaScript implementations
 */

import './../sass/app.scss';

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import hljs from 'highlight.js';
import 'highlight.js/scss/github.scss';

window.hljs = hljs;

window.publicApiToken = '';

const SETTINGS_DEFAULT_TEXT_COLOR = '#ffffff';
const SETTINGS_DEFAULT_TEXT_EMPHASIS = '1';
const SETTINGS_DEFAULT_BACKGROUND_COLOR = '#82acd7';
const SETTINGS_DEFAULT_BACKGROUND_IMAGE = 'none';
const SETTINGS_DEFAULT_SOUND_ENABLE = '1';

window.ajaxRequest = function (method, url, data = {}, successfunc = function(data){}, finalfunc = function(){}, config = {})
{
    let func = window.axios.get;
    if (method == 'post') {
        func = window.axios.post;
    } else if (method == 'patch') {
        func = window.axios.patch;
    } else if (method == 'delete') {
        func = window.axios.delete;
    }

    func(url, data, config)
        .then(function(response){
            successfunc(response.data);
        })
        .catch(function (error) {
            console.log(error);
        })
        .finally(function(){
                finalfunc();
            }
        );
};

window.saveSetting = function(key, value, notify = true) {
    localStorage.setItem(key, value);

    if (notify) {
        window.notify('Settings updated', 'Successfully updated ' + key + ' to ' + value, 'success');
    }
};

window.readSetting = function(key, fallback = null) {
    const result = localStorage.getItem(key);
    if (result === null) {
        return fallback;
    }

    return result;
};

window.removeSetting = function(key) {
    localStorage.removeItem(key);
};

window.clearSettings = function() {
    localStorage.clear();
};

window.loadSettings = function() {
    const textcolor = window.readSetting('style-text-color', SETTINGS_DEFAULT_TEXT_COLOR);
    document.querySelector('#settings-dialog-style-text-color').value = textcolor;

    const textemphasis = window.readSetting('style-text-emphasis', SETTINGS_DEFAULT_TEXT_EMPHASIS);
    document.querySelector('#settings-dialog-style-text-emphasis').checked = (parseInt(textemphasis)) ? true : false;

    const backgroundcolor = window.readSetting('style-background-color', SETTINGS_DEFAULT_BACKGROUND_COLOR);
    document.querySelector('#settings-dialog-style-background-color').value = backgroundcolor;
    
    const backgroundimage = window.readSetting('style-background-image', SETTINGS_DEFAULT_BACKGROUND_IMAGE);
    document.querySelector('#settings-dialog-style-background-image').value = backgroundimage;

    const sndenable = window.readSetting('sound-enable', SETTINGS_DEFAULT_SOUND_ENABLE);
    document.querySelector('#settings-dialog-sound-enable').checked = parseInt(sndenable);
};

window.applySettings = function() {
    const colorText = window.readSetting('style-text-color', SETTINGS_DEFAULT_TEXT_COLOR);
    const emphasisText = window.readSetting('style-text-emphasis', SETTINGS_DEFAULT_TEXT_EMPHASIS);
    
    let colElems = document.querySelectorAll('.widgets-item-title');
    for (let i = 0; i < colElems.length; i++) {
        colElems[i].style.color = colorText;

        if (parseInt(emphasisText)) {
            colElems[i].style.textShadow = 'rgb(0, 0, 0) 1px 1px 1px, rgb(0, 0, 0) 1px 1px 2px';
        } else {
            colElems[i].style.textShadow = 'unset';
        }
    }

    const colorBackground = window.readSetting('style-background-color', SETTINGS_DEFAULT_BACKGROUND_COLOR);
    const backgroundImage = window.readSetting('style-background-image', SETTINGS_DEFAULT_BACKGROUND_IMAGE);

    let bgElem = document.querySelector('.desktop');
    bgElem.style.backgroundColor = colorBackground;
    if (backgroundImage === 'none') {
        bgElem.style.backgroundImage = 'unset';
    } else {
        bgElem.style.backgroundImage = `url('${window.location.origin}/img/backgrounds/${backgroundImage}')`;
    }
};

window.resetSettings = function(ask = false) {
    if (ask) {
        if (!confirm('Do you really want to reset the system? All your preferences will be deleted.')) {
            return;
        }
    }

    window.closeAllWidgets();
    window.clearSettings();

    window.location.href = window.location.origin + '/?reset=1';
};

window.setDesktopStyle = function(key, value) {
    let root = document.querySelector('.widgets');
    if (!root) {
        console.error('Fatal error: widget root element not found.');
        return;
    }

    root.style[key] = value;
};

window.registerWidget = function(ident, title, icon, appletId = null) {
    let root = document.querySelector('.widgets');
    if (!root) {
        console.error('Fatal error: widget root element not found.');
        return;
    }

    let imageAsset = icon;
    if ((!imageAsset.includes('http://')) && (!imageAsset.includes('https://'))) {
        imageAsset = window.location.origin + '/img/icons/' + imageAsset;
    }

    let appendIfApplet = '';
    if (appletId !== null) {
        appendIfApplet = `, window.applets[` + appletId + `].instance.onShow`;
    }

    root.innerHTML += `
        <div class="widgets-item" id="desktop-widget-` + ident + `" onclick="window.openWidget('#` + ident + `'` + appendIfApplet + `);">
            <div class="widgets-item-icon">
                <img src="` + imageAsset + `" alt="icon"/>
            </div>

            <div class="widgets-item-title">
                ` + title + `
            </div>
        </div>
    `;
};

window.openWidget = function(which, onOpenCallback = function(){}) {
    let elem = document.querySelector(which);
    if (!elem) {
        console.error('Element not found: ' + which);
        return;
    }

    if (elem.classList.contains('is-hidden')) {
        elem.classList.remove('is-hidden');
    }

    window.setWidgetCentered(elem);
    window.setWidgetToTop(which);
    window.setDraggableWindows();

    window.playAudio('open.wav');

    onOpenCallback();
};

window.setWidgetCentered = function(elem) {
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight - 35;

    const windowWidth = elem.offsetWidth;
    const windowHeight = elem.offsetHeight;

    elem.style.position = 'fixed';
    elem.style.top = (screenHeight / 2 - windowHeight / 2).toString() + 'px';
    elem.style.left = (screenWidth / 2 - windowWidth / 2).toString() + 'px';
};

window.setWidgetToTop = function(which) {
    let elem = document.querySelector(which);
    if (!elem) {
        console.error('Element not found: ' + which);
        return;
    }

    let others = document.querySelectorAll('.window');
    for (let i = 0; i < others.length; i++) {
        others[i].parentElement.style.zIndex = 'unset';
    }

    elem.style.zIndex = '100';
};

window.closeWidget = function(which, onCloseCallback = function(){}) {
    let elem = document.querySelector(which);
    if (!elem) {
        console.error('Element not found: ' + which);
        return;
    }

    if (!elem.classList.contains('is-hidden')) {
        elem.classList.add('is-hidden');
    }

    window.playAudio('close.wav');

    onCloseCallback();
};

window.closeAllWidgets = function() {
    let widgets = document.querySelectorAll('.column');
    for (let i = 0; i < widgets.length; i++) {
        const widget = widgets[i];

        if (!widget.classList.contains('is-hidden')) {
            widget.classList.add('is-hidden');
        }
    }
};

window.startMenuCallbacks = [];
window.addStartMenuItem = function(title, icon, callback = function() {}) {
    let root = document.querySelector('.menu-actions');
    if (!root) {
        console.error('Fatal error: start menu root element not found.');
        return;
    }

    const ident = window.startMenuCallbacks.length;

    root.innerHTML += `
        <div class="menu-item" onclick="window.startMenuCallbacks[` + ident + `]();">
            <div class="menu-item-icon">
                <img src="` + window.location.origin + '/img/icons/' + icon + `" alt="icon"/>
            </div>

            <div class="menu-item-title">
                ` + title + `
            </div>
        </div>
    `;

    window.startMenuCallbacks.push(function() { callback(); window.closeActiveStartMenu(); window.playAudio('select.wav'); });
};

window.addStartMenuDelimiter = function() {
    let root = document.querySelector('.menu-actions');
    if (!root) {
        console.error('Fatal error: start menu root element not found.');
        return;
    }

    root.innerHTML += `<div class="menu-delimiter"><hr/></div>`;
};

window.toggleStartMenu = function() {
    let root = document.querySelector('.menu');
    if (!root) {
        console.error('Fatal error: start menu root element not found.');
        return;
    }

    root.classList.toggle('is-hidden');

    let button = document.querySelector('.startmenu');
    if (button) {
        button.classList.toggle('set-active');
    }

    window.playAudio('toggle.wav');
};

window.closeActiveStartMenu = function() {
    let root = document.querySelector('.menu');
    if (!root) {
        console.error('Fatal error: start menu root element not found.');
        return;
    }

    if (!root.classList.contains('is-hidden')) {
        let button = document.querySelector('.startmenu');
        if (button) {
            button.classList.remove('set-active');
        }

        root.classList.add('is-hidden');
    }
};

window.notify = function(title, message, icon = 'info', duration = 5000) {
    const root = document.querySelector('.notifications');
    if (!root) {
        console.error('Parent notification element not found');
        return;
    }

    const ident = 'notification-item-' + parseInt(Date.now());

    const html = `
        <div class="notification" id="` + ident + `">
            <div class="notification-icon"><img src="` + window.location.origin + '/img/icons/' + icon + `.png" alt="icon"/></div>
            
            <div class="notification-info">
                <div class="notification-info-title">` + title + `</div>
                <div class="notification-info-message">` + message + `</div>
            </div>
        </div>
    `;

    root.innerHTML += html;

    window.playAudio('notification.wav');

    setTimeout(function() { 
        let el = document.getElementById(ident);
        if (el) {
            el.style.transition = '1.0s';
            el.style.opacity = '0';
            el.style.visibility = 'hidden';

            setTimeout(function() {
                root.removeChild(document.getElementById(ident));
            }, 2000);
        }
    }, duration);
};

window.switchProjectTab = function(which) {
    for (let i = 1; i <= window.maxProjects; i++) {
        let elHide = document.getElementById('tab-project-' + i);
        if (elHide) {
            if (!elHide.classList.contains('is-hidden')) {
                elHide.classList.add('is-hidden');
            }
        }

        let roleHide = document.getElementById('role-project-' + i);
        if (roleHide) {
            roleHide.ariaSelected = false;
        }
    }

    let targetTab = document.getElementById('tab-project-' + which);
    if (targetTab) {
        if (targetTab.classList.contains('is-hidden')) {
            targetTab.classList.remove('is-hidden');
        }
    }

    let targetRole = document.getElementById('role-project-' + which);
    if (targetRole) {
        targetRole.ariaSelected = true;
    }
};

window.toggleWindowSize = function(wnd, style, repos = true) {
    let elem = document.querySelector(wnd);
    if (elem) {
        elem.classList.toggle(style);

        if (repos) {
            window.setWidgetCentered(elem);
        }
    }
};

window.setDraggableWindows = function() {
    const elems = document.querySelectorAll('.title-bar');
    for (let i = 0; i < elems.length; i++) {
        const parent = elems[i].parentElement.parentElement;
        const bar = elems[i];

        parent.offsetX = 0;
        parent.offsetY = 0;
        parent.style.position = 'absolute';

        const onMouseMove = function(event) {
            parent.style.left = (event.clientX - parent.offsetX).toString() + 'px';
            parent.style.top = (event.clientY - parent.offsetY).toString() + 'px';
        };

        const onMouseUp = function () {
            document.removeEventListener("mousemove", onMouseMove);
            document.removeEventListener("mouseup", onMouseUp);
        };

        bar.addEventListener("mousedown", (event) => {
            parent.offsetX = event.clientX - parent.offsetLeft;
            parent.offsetY = event.clientY - parent.offsetTop;

            document.addEventListener("mousemove", onMouseMove);
            document.addEventListener("mouseup", onMouseUp);

            window.setWidgetToTop('#' + parent.id);
        });        
    }
};

window.updateAudioIndicator = function(icon, label) {
    const elIcon = document.querySelector(icon);
    const elLabel = document.querySelector(label);

    const sndenable = parseInt(window.readSetting('sound-enable', SETTINGS_DEFAULT_SOUND_ENABLE));
    
    if (sndenable) {
        elIcon.src = window.location.origin + '/img/icons/audio_on.png';
        elLabel.innerText = 'On';
    } else {
        elIcon.src = window.location.origin + '/img/icons/audio_off.png';
        elLabel.innerText = 'Off';
    }

    setTimeout(function() {
        window.updateAudioIndicator(icon, label);
    }, 1000);
};

window.updateDateTime = function(target) {
    let elem = document.querySelector(target);
    if (elem) {
        let dt = new Date();
        elem.innerText = ('0' + dt.getHours()).slice(-2) + ':' + ('0' + dt.getMinutes()).slice(-2);

        setTimeout(function() {
            window.updateDateTime(target);
        }, 1000);
    }
};

window.fetchBlogPosts = function(target, type = 'default') {
    let elem = document.querySelector(target);
    if (elem) {
        window.ajaxRequest('get', window.location.origin + '/blog/posts/fetch?limit=' + elem.dataset.limit + '&type=' + type, {}, function(response) {
            if (response.code == 200) {
                elem.innerHTML = '';

                response.data.forEach(function(post, index) {
                    let tableEntry = `
                        <tr>
                            <td><a href="` + window.location.origin + '/blog/' + post.slug + `">` + post.title + `</a></td>
                            <td>` + post.created_at + `</td>
                        </tr>
                    `;

                    elem.innerHTML += tableEntry;
                });
            }
        });
    }
};

window.queryShout = function(target) {
    let container = document.querySelector(target);
    if (container) {
        window.ajaxRequest('get', window.location.origin + '/shoutbox/query', {}, function(response) {
            if (response.code == 200) {
                let tableEntry = `
                    <tr>
                        <td>` + response.shout.username + `</td>
                        <td>` + response.shout.message + `</td>
                    </tr>
                `;

                container.children[0].innerHTML += tableEntry;

                container.scrollTop = container.scrollHeight;
            }
        });
    }
};

window.previewBlogPost = function(title, content, token) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.origin + '/blog/posts/submit/preview?token=' + token;
    form.target = '_blank';

    const inpTitle = document.createElement('input');
    inpTitle.type = 'text';
    inpTitle.name = 'title';
    inpTitle.value = title;
    inpTitle.style.display = 'none';
    form.appendChild(inpTitle);

    const inpContent = document.createElement('textarea');
    inpContent.name = 'content';
    inpContent.value = content;
    inpContent.style.display = 'none';
    form.appendChild(inpContent);

    document.body.appendChild(form);
    form.submit();
};

window.queryEndpointStatuses = function(callback = function(){}, notifyOnSuccess = false) {
    window.ajaxRequest('post', window.location.origin + '/services/endpoints/status/all?token=' + window.publicApiToken, {}, function(response) {
        if (response.metadata.status == 200) {
            window.clearEndpointsTable();

            const quantity = response.metadata.endpoints;
            for (let i = 0; i < quantity; i++) {
                const ep = response['ep' + (i + 1).toString()];
                window.addEndpointStatusToTable(ep.title, ep.description, ep.host, ep.status);
            }

            if (notifyOnSuccess) {
                window.notify('Endpoint statuses', 'Successfully fetched endpoint statuses', 'success');
            }
        } else {
            window.notify('Request error', 'Failed to query endpoint statuses', 'error');
        }

        callback();
    });
};

window.addEndpointStatusToTable = function(name, description, host, status) {
    const table = document.querySelector('#services-endpoints');
    if (!table) {
        console.error('Required data table not found');
        return;
    }

    const html = `
        <tr>
            <td><strong>` + name + `</strong></td>
            <td>` + description + `</td>
            <td><a href="` + host + `" target="_blank">` + host + `</a></td>
            <td class="align-right color-dark-orange">` + status + `</td>
        </tr>
    `;

    table.innerHTML += html;
};

window.clearEndpointsTable = function() {
    const table = document.querySelector('#services-endpoints');
    if (!table) {
        console.error('Required data table not found');
        return;
    }

    table.innerHTML = '';
};

window.runCode = function(src, output) {
    let elem = document.querySelector(output);
    if (elem) {
        const origConLog = console.log;
        const origConWarn = console.warn;
        const origConErr = console.error;

        console.log = function(...args) {
            elem.innerHTML += args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : arg).join(' ') + "<br/>";
            origConLog.apply(console, args); 
        };

        console.warn = function(...args) {
            elem.innerHTML += '<font color="#cccc00">' + args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : arg).join(' ') + '</font>' + "<br/>";
            origConWarn.apply(console, args); 
        };

        console.error = function(...args) {
            elem.innerHTML += '<font color="#cc0000">' + args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : arg).join(' ') + '</font>' + "<br/>";
            origConErr.apply(console, args); 
        };

        try {
            const ev = eval(src);
            if (typeof ev !== 'undefined') {
                elem.innerHTML += ev + "<br/>";
            } else {
                elem.innerHTML += '<font color="#d1cf47">' + src + '</font>' + "<br/>";
            }
        } catch (err) {
            elem.innerHTML += '<font color="#cc0000">' + err + '</font>' + "<br/>";
            window.notify('Exception', err, 'error');
        }

        console.log = origConLog;
        console.warn = origConWarn;
        console.error = origConErr;

        elem.scrollTop = elem.scrollHeight;
    }
};

window.playAudio = function(soundfile) {
    const sndEnabled = parseInt(window.readSetting('sound-enable', SETTINGS_DEFAULT_SOUND_ENABLE));
    if (!sndEnabled) {
        return;
    }

    let audio = new Audio(window.location.origin + '/sounds/' + soundfile);
    audio.onloadeddata = function() {
        audio.play();
    };
};

window.echo = function(...args) {
    console.log.apply(console, args);
};

window.random = function(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
};
