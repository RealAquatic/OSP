function setSiteCookie(name, value, days) {
    var expires = '';
    if (typeof days === 'number') {
        var d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + d.toUTCString();
    }
    var cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    document.cookie = cookie;
}

function getSiteCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function deleteSiteCookie(name) {
    document.cookie = name + '=; Max-Age=0; path=/; SameSite=Lax';
}

window.SiteCookies = {
    set: setSiteCookie,
    get: getSiteCookie,
    del: deleteSiteCookie
};

(function () {
    function buildUI() {
        if (document.getElementById('SiteCookieRoot')) return;
        var root = document.createElement('div');
        root.id = 'SiteCookieRoot';
        root.innerHTML = '\n    <div id="CookieBanner" style="position:fixed;left:0;right:0;bottom:16px;display:none;z-index:99999;">\n        <div style="max-width:1100px;margin:0 auto;background:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.12);display:flex;align-items:center;justify-content:space-between;gap:12px;">\n            <div style="flex:1">We use cookies to improve your experience. Manage your preferences or accept all.</div>\n            <div style="display:flex;gap:8px;align-items:center;">\n                <button id="CookieSettingsBtn" class="PrimaryButton" style="background:#fff;color:#33683E;border:1px solid #33683E;padding:0.5rem 0.75rem;">Settings</button>\n                <button id="CookieAcceptBtn" class="PrimaryButton">Accept all</button>\n            </div>\n        </div>\n    </div>\n\n    <div id="CookieModal" style="display:none;position:fixed;inset:0;z-index:100000;align-items:center;justify-content:center;">\n        <div id="CookieOverlay" style="position:absolute;inset:0;background:rgba(0,0,0,0.5);"></div>\n        <div style="position:relative;background:#fff;border-radius:10px;padding:1rem;max-width:720px;margin:2rem;z-index:100001;">\n            <h3>Cookie preferences</h3>\n            <form id="CookieForm">\n                <div style="margin:0.5rem 0;">\n                    <label><input type="checkbox" name="necessary" checked disabled> Necessary (required)</label>\n                </div>\n                <div style="margin:0.5rem 0;">\n                    <label><input type="checkbox" name="analytics"> Analytics</label>\n                </div>\n                <div style="margin:0.5rem 0;">\n                    <label><input type="checkbox" name="marketing"> Marketing</label>\n                </div>\n                <div style="margin-top:0.75rem;text-align:right;display:flex;gap:8px;justify-content:flex-end;">\n                    <button type="button" id="CookieSaveBtn" class="PrimaryButton">Save preferences</button>\n                    <button type="button" id="CookieCloseBtn" class="PrimaryButton" style="background:#ccc;color:#000;">Close</button>\n                </div>\n            </form>\n        </div>\n    </div>\n';
        document.body.appendChild(root);
    }

    function init() {
        buildUI();
        var banner = document.getElementById('CookieBanner');
        var modal = document.getElementById('CookieModal');
        var acceptBtn = document.getElementById('CookieAcceptBtn');
        var settingsBtn = document.getElementById('CookieSettingsBtn');
        var saveBtn = document.getElementById('CookieSaveBtn');
        var closeBtn = document.getElementById('CookieCloseBtn');

        function openModal() { modal.style.display = 'flex'; }
        function closeModal() { modal.style.display = 'none'; }

        var existing = getSiteCookie('cookie_consent');
        if (!existing) banner.style.display = 'block';

        acceptBtn.addEventListener('click', function () {
            setSiteCookie('cookie_consent', JSON.stringify({ analytics: true, marketing: true }), 365);
            banner.style.display = 'none';
            closeModal();
        });
        settingsBtn.addEventListener('click', function () {
            var frm = document.getElementById('CookieForm');
            if (existing) {
                try { var p = JSON.parse(existing); frm.elements['analytics'].checked = !!p.analytics; frm.elements['marketing'].checked = !!p.marketing; } catch (e) { }
            }
            openModal();
        });
        saveBtn.addEventListener('click', function () {
            var form = document.getElementById('CookieForm');
            var analytics = !!form.elements['analytics'].checked;
            var marketing = !!form.elements['marketing'].checked;
            setSiteCookie('cookie_consent', JSON.stringify({ analytics: analytics, marketing: marketing }), 365);
            banner.style.display = 'none';
            closeModal();
        });
        closeBtn.addEventListener('click', function () { closeModal(); });

        document.addEventListener('click', function (e) {
            var el = e.target.closest && e.target.closest('a');
            if (!el) return;
            var href = el.getAttribute('href') || '';
            if (href.indexOf('cookies') !== -1) {
                e.preventDefault();
                var frm = document.getElementById('CookieForm');
                var cur = getSiteCookie('cookie_consent');
                if (cur) {
                    try { var p = JSON.parse(cur); frm.elements['analytics'].checked = !!p.analytics; frm.elements['marketing'].checked = !!p.marketing; } catch (e) { }
                }
                openModal();
            }
        }, false);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

(function(){
    try {
        var STORAGE_KEY = 'site_a11y_settings';

        function getSettings(){
            try{ var s = SiteCookies.get(STORAGE_KEY); if(s) return JSON.parse(s); }catch(e){}
            return { font: 'normal', contrast: 'normal', reduceMotion: false };
        }
        function saveSettings(s){ try{ SiteCookies.set(STORAGE_KEY, JSON.stringify(s), 365); }catch(e){} }

        var css = '\n#A11yRoot { position: fixed; left: 8px; bottom: 12px; z-index: 200000; font-family: Inter, system-ui, sans-serif; }\n#A11yToggle { width:44px;height:44px;border-radius:8px;background:#33683E;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,0.18); }\n#A11yPanel { position: fixed; left: 0; top: 0; bottom: 0; width: 280px; transform: translateX(-100%); transition: transform 260ms ease; z-index:200000; background: #fff; box-shadow: 2px 0 18px rgba(0,0,0,0.12); padding: 1rem; overflow:auto; }\n#A11yPanel.open{ transform: translateX(0); }\n#A11yPanel h4{ margin:0 0 0.5rem 0; font-size:1rem; color:#175424; display:flex; align-items:center; justify-content:space-between; }\n#A11yPanel .a11y-row{ display:flex; gap:0.5rem; align-items:center; margin-bottom:0.6rem; }\n#A11yPanel .closeBtn { background:transparent;border:none;font-size:1.1rem;cursor:pointer;color:#175424; }\nhtml.a11y-font-large { font-size: 18px; }\nhtml.a11y-font-small { font-size: 13px; }\n/* High contrast: set page background dark and ensure text is light */\nhtml.a11y-contrast-high body { background-color: #000 !important; }\nhtml.a11y-contrast-high, html.a11y-contrast-high * { color: #fff !important; background-color: transparent !important; border-color: #fff !important; }\nhtml.a11y-contrast-high a { color: #ffd54f !important; }\nhtml.a11y-reduce-motion * { transition: none !important; animation: none !important; }\n@media (max-width:480px){ #A11yPanel{ width:220px; } }\n';

        var s = document.createElement('style'); s.type = 'text/css'; s.appendChild(document.createTextNode(css)); document.head.appendChild(s);

        var root = document.createElement('div'); root.id = 'A11yRoot';
        var panel = document.createElement('div'); panel.id = 'A11yPanel'; panel.setAttribute('aria-hidden','true');
        panel.innerHTML = '<h4>Accessibility <button id="A11yClose" class="closeBtn" aria-label="Close accessibility menu">×</button></h4>' +
            '<div class="a11y-row"><label style="flex:1">Font size</label><div><button id="A11yFontSm" class="PrimaryButton" style="margin-right:6px;">A-</button><button id="A11yFontDefault" class="PrimaryButton" style="margin-right:6px;">A</button><button id="A11yFontLg" class="PrimaryButton">A+</button></div></div>' +
            '<div class="a11y-row"><label style="flex:1">Contrast</label><div><button id="A11yContrast" class="PrimaryButton">Toggle</button></div></div>' +
            '<div class="a11y-row"><label style="flex:1">Reduce motion</label><div><button id="A11yMotion" class="PrimaryButton">Toggle</button></div></div>' +
            '<div style="margin-top:0.75rem; text-align:right;"><button id="A11yReset" class="PrimaryButton" style="background:#ccc;color:#000;">Reset</button></div>';

        var toggle = document.createElement('button'); toggle.id = 'A11yToggle'; toggle.setAttribute('aria-label','Open accessibility menu'); toggle.title = 'Accessibility'; toggle.innerHTML = '&#x2190;';

        root.appendChild(panel);
        root.appendChild(toggle);
        document.body.appendChild(root);

        function applySettings(settings){
            document.documentElement.classList.remove('a11y-font-large','a11y-font-small','a11y-contrast-high','a11y-reduce-motion');
            if (settings.font === 'large') document.documentElement.classList.add('a11y-font-large');
            if (settings.font === 'small') document.documentElement.classList.add('a11y-font-small');
            if (settings.contrast === 'high') document.documentElement.classList.add('a11y-contrast-high');
            if (settings.reduceMotion) document.documentElement.classList.add('a11y-reduce-motion');
        }

        var settings = getSettings();
        applySettings(settings);

        toggle.addEventListener('click', function(){ var open = panel.classList.toggle('open'); panel.setAttribute('aria-hidden', !open); toggle.setAttribute('aria-pressed', open); });

        document.getElementById('A11yFontLg').addEventListener('click', function(){ settings.font = 'large'; applySettings(settings); saveSettings(settings); });
        document.getElementById('A11yFontSm').addEventListener('click', function(){ settings.font = 'small'; applySettings(settings); saveSettings(settings); });
        var dbtn = document.getElementById('A11yFontDefault'); if (dbtn) dbtn.addEventListener('click', function(){ settings.font = 'normal'; applySettings(settings); saveSettings(settings); });
        document.getElementById('A11yContrast').addEventListener('click', function(){ settings.contrast = (settings.contrast === 'high') ? 'normal' : 'high'; applySettings(settings); saveSettings(settings); });
        document.getElementById('A11yMotion').addEventListener('click', function(){ settings.reduceMotion = !settings.reduceMotion; applySettings(settings); saveSettings(settings); });
        document.getElementById('A11yReset').addEventListener('click', function(){ settings = { font:'normal', contrast:'normal', reduceMotion:false }; applySettings(settings); saveSettings(settings); });
        var closeBtnInner = document.getElementById('A11yClose'); if (closeBtnInner) closeBtnInner.addEventListener('click', function(){ panel.classList.remove('open'); panel.setAttribute('aria-hidden','true'); toggle.setAttribute('aria-pressed','false'); });

        document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && panel.classList.contains('open')) { panel.classList.remove('open'); panel.setAttribute('aria-hidden','true'); toggle.setAttribute('aria-pressed','false'); } });

    } catch (e) {
        console.error('Accessibility widget failed to initialise', e);
    }
})();
