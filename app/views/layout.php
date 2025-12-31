<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <meta name="author" content="{{ env('APP_AUTHOR') }}">
        <meta name="description" content="{{ env('APP_DESCRIPTION') }}">

        <meta name="og:title" property="og:title" content="{{ (isset($_meta_title)) ? $_meta_title : env('APP_AUTHOR') }}">
        <meta name="og:description" property="og:description" content="{{ (isset($_meta_description)) ? $_meta_description : env('APP_DESCRIPTION') }}">
        <meta name="og:url" property="og:url" content="{{ (isset($_meta_url)) ? $_meta_url : url('/') }}">
        <meta name="og:image" property="og:image" content="{{ (isset($_meta_image)) ? asset('img/uploads/' . $_meta_image) : Utils::getDefaultMetaImage() }}">

        <title>{{ env('APP_TITLE') }}</title>

        @if (env('APP_ENABLE_PWA'))
        <link rel="manifest" href="{{ asset('manifest.json') }}"/>
        @endif

        <link rel="stylesheet" type="text/css" href="{{ asset('css/98.min.css') }}"/>

        <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}"/>

        <script src="{{ asset('js/fontawesome.js') }}"></script>
        <script src="{{ asset('js/app.js', true) }}"></script>
    </head>

    <body>
        <div class="desktop" onclick="window.closeActiveStartMenu();">
            @include('widgets.php')

            <div class="content">
                {%content%}
            </div>
        </div>

        @include('menu.php')
        @include('taskbar.php')
        @include('notifications.php')
		
        <script>
            @if (env('APP_ENABLE_PWA'))
            window.onload = function() {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('./serviceworker.js', { scope: '/' })
                        .then(function(registration){
                            window.serviceWorkerEnabled = true;
                        }).catch(function(err){
                            window.serviceWorkerEnabled = false;
                            console.error(err);
                        });
                }
            };
            @endif

            window.initDesktop = function() {
                window.loadSettings();

                window.registerWidget('column-window-about', 'About Me', 'about.png');
                @if (env('APP_ENABLE_BLOG'))
                window.registerWidget('column-window-blog', 'Blog Posts', 'blog.png');
                @endif
                window.registerWidget('column-window-projects', 'My Projects', 'projects.png');
                window.registerWidget('column-window-technologies', 'Tech Stack', 'tech.png');
                @if (env('APP_ENABLE_SHOUTBOX'))
                window.registerWidget('column-window-shoutbox', 'Shoutbox', 'shoutbox.png');
                @endif
                @if (env('APP_ENABLE_TERMINAL'))
                window.registerWidget('column-window-terminal', 'Terminal', 'terminal.png');
                document.getElementById('terminal-code-result').innerHTML += 'Welcome to the system terminal' + "<br/>" + '=========================' + "<br/><br/>" + '&gt;&nbsp;' + navigator.userAgent + "<br/>" + '&gt;&nbsp;Session timestamp: ' + (Date.now()).toString() + "<br/>" + '&gt;&nbsp;System locale: ' + navigator.language + ' (' + Intl.DateTimeFormat().resolvedOptions().timeZone + ')' + "<br/><br/>";
                @endif
                window.registerWidget('column-window-settings', 'Settings', 'settings.png');
                @if (env('APP_ENABLE_SERVICES'))
                window.registerWidget('column-window-services', 'Services', 'services.png');
                @endif
                @if (env('APP_ENABLE_APPLETS'))
                window.registerWidget('column-window-applets', 'Applets', 'applets.png');
                window.bootApplets();
                @endif

                window.addStartMenuItem('Contact', 'mail.png', function() { window.location.href = 'mailto:{{ env('APP_CONTACT') }}'; });
                window.addStartMenuDelimiter();
                @foreach (config('socials') as $social)
					@if ((is_string($social->url)) && (strlen($social->url) > 0))
                        window.addStartMenuItem('{{ $social->name }}', '{{ $social->icon }}', function() { window.open('{{ $social->url }}'); });
					@endif
				@endforeach
                window.addStartMenuDelimiter();
                @if (env('APP_ENABLE_APPLETS'))
                window.addStartMenuItem('Applets', 'applets.png', function() { window.openWidget('#column-window-applets'); });
                @endif
                window.addStartMenuItem('Settings', 'settings.png', function() { window.openWidget('#column-window-settings'); });
                window.addStartMenuItem('Reset', 'reset.png', function() { window.resetSettings(true); });
                window.addStartMenuItem('Reboot', 'reboot.png', function() { location.href = '{{ url('/?reboot=1') }}'; });

                window.applySettings();

                @if (isset($_GET['widget']))
                window.openWidget('#column-window-{{ $_GET['widget'] }}');
                @elseif (isset($widget))
                window.openWidget('#column-window-{{ $widget }}');
                @endif
            };

            window.maxProjects = {{ (isset($projects) ? count($projects) : 0) }};
            
            document.addEventListener('DOMContentLoaded', function() {
                window.publicApiToken = '{{ env('APP_PUBLIC_API_TOKEN') }}';

                window.initDesktop();
                window.setDraggableWindows();

                window.switchProjectTab(1);

                window.fetchBlogPosts('#blog-posts');
                window.fetchBlogPosts('#popular-posts', 'popular');
                window.updateAudioIndicator('#taskbar-audio-icon', '#taskbar-audio-label');
                window.updateDateTime('#update-current-time');

                window.hljs.registerLanguage('aquashell', function() {
					return {
						case_insensitive: false,
						keywords: {
							keyword: 'global const set if function elseif else for while local result unset call class method member construct destruct require exec run cwd gwd getscriptpath getscriptname debug textview random sleep bitop timestamp fmtdatetime gettickcount getsystemerror setsystemerror threadfunc hideconsole listlibs print sys pause exit quit',
							literal: 'bool int float string void true false __ALL__',
						},
						contains: [
						{
							className: 'string',
							begin: '"',
							end: '"'
						},
						hljs.COMMENT(
							'#',
							"\n",
							{}
						)
						]
					}
				});
				window.hljs.highlightAll();

                @if (env('APP_ENABLE_SHOUTBOX'))
                window.shoutboxInterval = setInterval(() => {
                    window.queryShout('.sunken-panel-shoutbox');
                }, {{ env('APP_SHOUTBOX_DELAY', 5000) }});
                @endif

                @if (env('APP_ENABLE_SERVICES'))
                setTimeout(function() { window.queryEndpointStatuses(); }, 3500);
                @endif

                @if ((isset($_GET['reboot'])) && ($_GET['reboot']) == 1)
                window.notify('Reboot succeeded', 'The system was successfully rebooted.');
                @endif

                @if ((isset($_GET['reset'])) && ($_GET['reset']) == 1)
                window.notify('System reset', 'The system was reset successfully!', 'success');
                @endif

                let initialVisit = parseInt(window.readSetting('initial-visit', '0'));
                if (!initialVisit) {
                    setTimeout(function() {
                        window.notify('Hello there!', 'Welcome to my portfolio app', 'info', 10000);
                        window.saveSetting('initial-visit', '1', false);
                    }, 3500);
                }
            });
        </script>
    </body>
</html>