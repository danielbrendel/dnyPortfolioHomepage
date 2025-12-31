@include('applets/renderer.php')

@if (env('APP_ENABLE_APPLETS'))
<div class="column is-half is-hidden" id="column-window-applets">
	<div class="window">
		<div class="title-bar">
			<div class="title-bar-text">Applets</div>
			<div class="title-bar-controls">
				<button aria-label="Minimize" onclick="window.toggleWindowSize('#column-window-applets', 'window-minimized');"></button>
				<button aria-label="Maximize" onclick="window.toggleWindowSize('#column-window-applets', 'window-maximized');"></button>
				<button aria-label="Close" onclick="window.closeWidget('#column-window-applets');"></button>
			</div>
		</div>
		<div class="window-body">
            <div class="sunken-panel sunken-panel-applets">
                @if ((isset($applets)) && (is_countable($applets)))
                    @foreach ($applets as $applet)
                        <div class="applet-item" id="applet-{{ $applet->id }}" onclick="window.displayAppletDetails({{ $applet->id }});">
                            <div class="applet-data">
                                <input type="hidden" id="applet-name-{{ $applet->id }}" value="{{ $applet->name }}"/>
                                <input type="hidden" id="applet-version-{{ $applet->id }}" value="{{ $applet->version }}"/>
                                <input type="hidden" id="applet-description-{{ $applet->id }}" value="{{ $applet->description }}"/>
                                <input type="hidden" id="applet-author-{{ $applet->id }}" value="{{ $applet->author }}"/>
                                <input type="hidden" id="applet-resource-{{ $applet->id }}" value="{{ $applet->resource }}"/>
                            </div>

                            <div class="applet-icon">
                                @if ((strpos($applet->icon, 'http://') !== 0) && (strpos($applet->icon, 'https://') !== 0))
                                <img src="{{ asset('img/icons/' . $applet->icon) }}" alt="icon"/>
                                @else
                                <img src="{{ $applet->icon }}" alt="icon"/>
                                @endif
                            </div>

                            <div class="applet-name" id="applet-name-{{ Utils::ruleify($applet->name) }}">{{ $applet->name }}</div>
                        </div>
                    @endforeach
                @endif
			</div>

            <div class="applet-details">
                <div class="applet-info">Select an applet to view details</div>
                <div class="applet-action is-hidden">
                    <div class="applet-install is-hidden" onclick="window.downloadApplet(document.getElementById('applet-action-install-name').value, document.getElementById('applet-action-install-resource').value); this.classList.add('is-hidden');">
                        <input type="hidden" id="applet-action-install-name"/>
                        <input type="hidden" id="applet-action-install-resource"/>

                        <div class="applet-install-icon">
                            <img src="{{ asset('img/icons/download.png') }}" alt="icon"/>
                        </div>

                        <div class="applet-install-label">Install</div>
                    </div>

                    <div class="applet-uninstall is-hidden" onclick="window.removeApplet(document.getElementById('applet-action-uninstall-name').value); window.unselectAllApplets(); this.classList.add('is-hidden');">
                        <input type="hidden" id="applet-action-uninstall-name"/>

                        <div class="applet-uninstall-icon">
                            <img src="{{ asset('img/icons/uninstall.png') }}" alt="icon"/>
                        </div>

                        <div class="applet-uninstall-label">Uninstall</div>
                    </div>
                </div>
            </div>

            <p>
                <a class="btn btn-fixed-padding" href="{{ url('/?widget=applets') }}">Reload</a>
                <a class="btn btn-fixed-padding" href="javascript:void(0);" onclick="window.closeWidget('#column-window-applets');">Close</a>
            </p>
		</div>
	</div>
</div>

<script>
    window.applets = [];

    window.displayAppletDetails = function(id) {
        const name = document.getElementById('applet-name-' + id).value;
        const version = document.getElementById('applet-version-' + id).value;
        const description = document.getElementById('applet-description-' + id).value;
        const author = document.getElementById('applet-author-' + id).value;
        const resource = document.getElementById('applet-resource-' + id).value;

        let info = document.querySelector('.applet-info');
        info.innerHTML = `
            <div>Name: ` + name + `</div>
            <div>Version: ` + version + `</div>
            <div>Description: ` + description + `</div>
            <div>Author: ` + author + `</div>
        `;

        let action = document.querySelector('.applet-action');
        action.style.visibility = 'unset';

        window.unselectAllApplets();

        let applet = document.getElementById('applet-' + id);
        applet.classList.add('applet-active');

        action.children[0].classList.add('is-hidden');
        action.children[1].classList.add('is-hidden');

        const exists = localStorage.getItem('applet-' + window.transformRuleCase(name));
        if (!exists) {
            action.children[0].classList.toggle('is-hidden');

            let dlres = document.getElementById('applet-action-install-resource');
            dlres.value = resource;

            let dlname = document.getElementById('applet-action-install-name');
            dlname.value = name;
        } else {
            action.children[1].classList.toggle('is-hidden');

            let remname = document.getElementById('applet-action-uninstall-name');
            remname.value = name;
        }
    };

    window.unselectAllApplets = function() {
        let applets = document.querySelectorAll('.applet-item');
        for (let i = 0; i < applets.length; i++) {
            const applet = applets[i];

            if (applet.classList.contains('applet-active')) {
                applet.classList.remove('applet-active');
            }
        }
    };

    window.transformPascalCase = function(expression) {
        return expression.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join('');
    };

    window.transformRuleCase = function(expression) {
        return expression.split(' ').map(word => word.charAt(0).toLowerCase() + word.slice(1)).join('-');
    };

    window.initApplet = function(cls, firsttime = false) {
        let instance = new cls();

        const infos = instance.infos();
        const settings = instance.settings();
        const styles = instance.styles();

        const elStyle = document.createElement('style');
        elStyle.textContent = styles;
        document.head.appendChild(elStyle);

        const entryIdent = window.applets.length;

        const appletWindow = window.buildAppletWindow(infos.name, entryIdent, instance.view(), settings.wndWidth, settings.wndHeight, settings.btnMinimize, settings.btnMaximize, settings.btnClose);
        let container = document.querySelector('.content');
        container.appendChild(appletWindow);

        window.registerWidget('column-window-' + window.transformRuleCase(infos.name), infos.name, infos.icon, entryIdent);

        if (firsttime) {
            instance.onInstall();
        }

        instance.onLoad();

        window.applets.push({
            name: infos.name.toLowerCase(),
            cls: cls,
            instance: instance
        });
    };

    window.loadApplet = function(name, code, firsttime = false) {
        const blobObj = new Blob([code], { type: 'text/javascript' });
        const scriptUrl = URL.createObjectURL(blobObj);

        let embed = document.createElement('script');
        embed.src = scriptUrl;
        embed.onload = function() {
            URL.revokeObjectURL(scriptUrl);

            let transformName = window.transformPascalCase(name);
            window.initApplet(window[transformName], firsttime);

            window.applySettings();
        };

        document.head.appendChild(embed);
    };

    window.downloadApplet = function(name, resource) {
        let label = document.querySelector('.applet-install-label');
        const origLabel = label.innerHTML;
        label.innerHTML = '<i class="fas fa-spinner fa-spin"></i>&nbsp;' + label.innerHTML;

        window.ajaxRequest('get', resource, {}, function(response) {
            window.saveApplet(window.transformRuleCase(name), response);
            window.loadApplet(name, response, true);

            label.innerHTML = origLabel;

            let indicator = document.getElementById('applet-name-' + window.transformRuleCase(name));
            if (indicator) {
                indicator.innerHTML = '<img src="' + window.location.origin + '/img/icons/success.png" alt="icon"/>&nbsp;' + indicator.innerHTML;
            }

            window.notify('Applet installation', 'Successfully downloaded and installed applet.', 'download');
        });
    };

    window.queryApplet = function(name) {
        return localStorage.getItem('applet-' + name.toLowerCase());
    };

    window.saveApplet = function(name, contents) {
        localStorage.setItem('applet-' + name.toLowerCase(), contents);
    };

    window.bootApplets = function() {
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);

            if (key.startsWith('applet-')) {
                const name = key.substr(key.indexOf('-') + 1);
                const data = localStorage.getItem(key);
                
                window.loadApplet(name.replaceAll('-', ' '), data);

                let indicator = document.getElementById('applet-name-' + name);
                if (indicator) {
                    indicator.innerHTML = '<img src="' + window.location.origin + '/img/icons/success.png" alt="icon"/>&nbsp;' + indicator.innerHTML;
                }
            }
        }
    };

    window.removeApplet = function(name) {
        const transformedName = window.transformRuleCase(name);
    
        for (let i = 0; i < window.applets.length; i++) {
            if (window.transformRuleCase(window.applets[i].name) === transformedName) {
                window.applets[i].instance.onRemove();
                window.applets.splice(i, 1);
                break;
            }
        }

        window.closeWidget('#column-window-' + transformedName);

        localStorage.removeItem('applet-' + transformedName);

        let indicator = document.getElementById('applet-name-' + transformedName);
        indicator.innerHTML = name;

        let desktopWidgets = document.querySelector('.widgets');
        let desktopItem = document.getElementById('desktop-widget-column-window-' + transformedName);
        desktopWidgets.removeChild(desktopItem);

        window.notify('Applet removal', 'Applet was successfully uninstalled.', 'success');
    };
</script>
@endif