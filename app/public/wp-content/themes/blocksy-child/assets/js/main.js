/* Prizma front-end interactions
   -------------------------------------------------------------------- */
(function () {
    'use strict';

    /* -----------------------------------------------------------------
       Make the premium motion system site-wide, including older templates
       --------------------------------------------------------------- */
    function initAutoReveals() {
        var selectors = [
            '.apple-card',
            '.ap-story-card',
            '.ap-info-card',
            '.ap-feature-card',
            '.bp-author-card',
            '.bp-about-block',
            '.bp-step',
            '.bp-faq',
            '.meridian-story-card',
            '.meridian-feature-card',
            '.meridian-topic-card',
            '.meridian-stat-card',
            '.meridian-chart-card',
            '.meridian-status-card',
            '.meridian-insight-card',
            '.meridian-content-card',
            '.meridian-comments-card',
            '.meridian-single-content > *',
            '.apple-page__content > *'
        ];

        document.querySelectorAll(selectors.join(',')).forEach(function (el, index) {
            if (!el.classList.contains('reveal')) {
                el.classList.add('reveal');
            }
            if (!el.dataset.delay) {
                el.dataset.delay = String(Math.min((index % 8) * 45, 260));
            }
        });
    }

    function initShimmerText() {
        var selectors = [
            '.ap-hero h1',
            '.ap-section-head h2',
            '.ap-interest h2',
            '.ap-info-card h2',
            '.ap-stat__value',
            '.ap-story-card h3',
            '.apple-archive__head h1',
            '.apple-page__head h1',
            '.apple-404 h1',
            '.apple-card h3',
            '.ap-auth__copy h1',
            '.ap-auth-card h2',
            '.meridian-page-head h1',
            '.meridian-single-head h1',
            '.meridian-dashboard__intro h2',
            '.meridian-topic-card h2',
            '.meridian-content-card h3',
            '.meridian-comments-card h3',
            '.ap-submit-card__header h2',
            '.ap-review-rail__card h2'
        ];

        document.querySelectorAll(selectors.join(',')).forEach(function (el) {
            if (!el.classList.contains('ap-shimmer-text')) {
                el.classList.add('ap-shimmer-text');
            }
            if (!el.dataset.shimmerText) {
                el.dataset.shimmerText = (el.innerText || el.textContent || '').trim().replace(/[ \t\r\f\v]+/g, ' ');
            }
        });
    }

    function initButtonShimmer() {
        var selectors = [
            '.bp-btn',
            '.myspace-new',
            '.myspace-view-all'
        ];

        document.querySelectorAll(selectors.join(',')).forEach(function (button) {
            var existingLabels = button.querySelectorAll('.ap-button-shimmer');
            if (existingLabels.length) {
                existingLabels.forEach(function (label) {
                    if (!label.dataset.shimmerText) {
                        label.dataset.shimmerText = (label.innerText || label.textContent || '').trim().replace(/[ \t\r\f\v]+/g, ' ');
                    }
                });
                return;
            }

            Array.prototype.slice.call(button.childNodes).forEach(function (node) {
                if (node.nodeType !== 3 || !node.textContent.trim()) return;

                var text = node.textContent.trim().replace(/[ \t\r\f\v]+/g, ' ');
                var label = document.createElement('span');
                label.className = 'ap-button-shimmer';
                label.dataset.shimmerText = text;
                label.textContent = text;
                button.replaceChild(label, node);
            });
        });
    }

    /* -----------------------------------------------------------------
       Reveal-on-scroll
       --------------------------------------------------------------- */
    function initReveal() {
        var els = document.querySelectorAll('.reveal');
        if (!els.length || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-in', 'active'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var d = parseInt(entry.target.dataset.delay || '0', 10);
                    setTimeout(function () {
                        entry.target.classList.add('is-in', 'active');
                    }, isNaN(d) ? 0 : d);
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 });
        els.forEach(function (el) { io.observe(el); });
    }

    /* -----------------------------------------------------------------
       User dropdown menu
       --------------------------------------------------------------- */
    function initUserDrop() {
        var drops = document.querySelectorAll('[data-userdrop]');
        drops.forEach(function (drop) {
            var trigger = drop.querySelector('[data-userdrop-trigger]');
            if (!trigger) return;
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = drop.classList.toggle('is-open');
                trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
        document.addEventListener('click', function (e) {
            drops.forEach(function (drop) {
                if (!drop.contains(e.target)) {
                    drop.classList.remove('is-open');
                    var t = drop.querySelector('[data-userdrop-trigger]');
                    if (t) t.setAttribute('aria-expanded', 'false');
                }
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                drops.forEach(function (drop) {
                    drop.classList.remove('is-open');
                    var t = drop.querySelector('[data-userdrop-trigger]');
                    if (t) t.setAttribute('aria-expanded', 'false');
                });
            }
        });
    }

    function initBackgroundReveal() {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                document.body.classList.add('bp-bg-reveal-ready');
            });
        });
    }

    /* -----------------------------------------------------------------
       Cover image drop zone (write page)
       --------------------------------------------------------------- */
    function initCoverDrop() {
        var drop = document.getElementById('bp-cover-drop');
        if (!drop) return;
        var input = drop.querySelector('input[type="file"]');
        if (!input) return;
        var variantInput = document.getElementById('bp_cover_variant');
        var removeInput = document.getElementById('bp_remove_thumbnail');
        var preview = drop.querySelector('[data-cover-preview]');
        var swatches = document.querySelectorAll('[data-cover-variant]');
        var uploadButtons = drop.querySelectorAll('[data-cover-upload]');
        var sourceTabs = document.querySelectorAll('[data-cover-source]');
        var uploadPanel = document.querySelector('[data-cover-upload-panel]');
        var urlPanel = document.querySelector('[data-cover-url-panel]');
        var urlInput = document.querySelector('[data-cover-url-input]');
        var urlPreview = document.querySelector('[data-cover-url-preview]');

        ['dragenter', 'dragover'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) {
                e.preventDefault(); e.stopPropagation();
                drop.classList.add('is-drag');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) {
                e.preventDefault(); e.stopPropagation();
                drop.classList.remove('is-drag');
            });
        });
        drop.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showCoverPreview(input.files[0]);
            }
        });
        input.addEventListener('change', function () {
            if (input.files && input.files[0]) showCoverPreview(input.files[0]);
        });

        swatches.forEach(function (button) {
            button.addEventListener('click', function () {
                activateVariant(button.getAttribute('data-cover-variant'), true);
            });
        });

        uploadButtons.forEach(function (button) {
            button.addEventListener('click', openCoverUpload);
        });

        sourceTabs.forEach(function (button) {
            button.addEventListener('click', function () {
                setCoverSource(button.getAttribute('data-cover-source') || 'upload');
            });
        });

        if (urlInput) {
            urlInput.addEventListener('input', function () {
                showUrlPreview(urlInput.value.trim());
            });
        }

        drop.addEventListener('click', function (e) {
            var upload = e.target.closest('[data-cover-upload]');
            var clear = e.target.closest('[data-cover-clear]');
            if (upload) openCoverUpload(e);
            if (clear) {
                e.preventDefault();
                activateVariant(variantInput ? variantInput.value : 'blue', true);
            }
        });

        window.bpOpenCoverUpload = openCoverUpload;

        setCoverSource(urlInput && urlInput.value.trim() ? 'url' : 'upload');
        if (urlInput && urlInput.value.trim()) {
            showUrlPreview(urlInput.value.trim());
        }

        function openCoverUpload(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            drop.scrollIntoView({ behavior: 'smooth', block: 'center' });
            input.click();
        }

        function setCoverSource(source) {
            source = source === 'url' ? 'url' : 'upload';
            sourceTabs.forEach(function (button) {
                var isActive = button.getAttribute('data-cover-source') === source;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
            if (uploadPanel) uploadPanel.hidden = source !== 'upload';
            if (urlPanel) urlPanel.hidden = source !== 'url';
            if (source === 'upload' && urlInput) {
                urlInput.value = '';
                showUrlPreview('');
            }
            if (source === 'url') {
                input.value = '';
                if (removeInput) removeInput.value = '0';
                if (urlInput) urlInput.focus();
            }
        }

        function activateVariant(variant, clearFile) {
            if (!variant) return;
            if (variantInput) variantInput.value = variant;
            if (clearFile) {
                input.value = '';
                if (removeInput) removeInput.value = '1';
                clearPreview();
            }
            swatches.forEach(function (button) {
                var isActive = button.getAttribute('data-cover-variant') === variant;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
            uploadButtons.forEach(function (button) {
                button.classList.remove('is-active');
                button.setAttribute('aria-pressed', 'false');
            });
            drop.classList.remove('has-upload');
        }

        function clearPreview() {
            if (!preview) return;
            preview.innerHTML = '';
            preview.classList.remove('is-visible');
        }

        function showCoverPreview(file) {
            if (!file || !file.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                if (!preview) return;
                var img = document.createElement('img');
                img.src = ev.target.result;
                var caption = document.createElement('div');
                caption.className = 'bp-cover-drop__caption';
                caption.innerHTML = '<span>' + escapeHtml(file.name) + ' - ' + (file.size / 1024).toFixed(0) + ' KB</span><span class="bp-cover-drop__actions"><button type="button" class="bp-cover-drop__replace" data-cover-upload>Replace</button><button type="button" class="bp-cover-drop__replace" data-cover-clear>Use style</button></span>';
                preview.innerHTML = '';
                preview.appendChild(img);
                preview.appendChild(caption);
                preview.classList.add('is-visible');
                if (removeInput) removeInput.value = '0';
                swatches.forEach(function (button) {
                    button.classList.remove('is-active');
                    button.setAttribute('aria-pressed', 'false');
                });
                uploadButtons.forEach(function (button) {
                    button.classList.add('is-active');
                    button.setAttribute('aria-pressed', 'true');
                });
                drop.classList.add('has-upload');
            };
            reader.readAsDataURL(file);
        }

        function showUrlPreview(url) {
            if (!urlPreview) return;
            var img = urlPreview.querySelector('img');
            if (!url || !/^https?:\/\//i.test(url)) {
                urlPreview.hidden = true;
                if (img) img.removeAttribute('src');
                return;
            }
            if (img) img.src = url;
            urlPreview.hidden = false;
        }
    }

    /* -----------------------------------------------------------------
       Tag input (chips)
       --------------------------------------------------------------- */
    function initTagInput() {
        var wrap   = document.getElementById('bp-tag-input');
        var hidden = document.getElementById('bp_tags');
        var field  = document.getElementById('bp-tag-input-field');
        if (!wrap || !field || !hidden) return;

        var tags = (hidden.value || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);
        renderTags();

        field.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(field.value);
                field.value = '';
            } else if (e.key === 'Backspace' && !field.value && tags.length) {
                tags.pop();
                renderTags();
            }
        });
        field.addEventListener('blur', function () {
            if (field.value.trim()) {
                addTag(field.value);
                field.value = '';
            }
        });

        function addTag(raw) {
            var t = (raw || '').trim().replace(/^#/, '');
            if (!t || tags.indexOf(t) !== -1 || tags.length >= 6) return;
            tags.push(t);
            renderTags();
        }

        function removeTag(t) {
            tags = tags.filter(function (x) { return x !== t; });
            renderTags();
        }

        function renderTags() {
            Array.prototype.slice.call(wrap.querySelectorAll('.bp-tag')).forEach(function (el) { el.remove(); });
            tags.forEach(function (t) {
                var chip = document.createElement('span');
                chip.className = 'bp-tag';
                chip.innerHTML = '# ' + escapeHtml(t) + ' <span class="close" data-tag="' + escapeAttr(t) + '">×</span>';
                wrap.insertBefore(chip, field);
                chip.querySelector('.close').addEventListener('click', function () { removeTag(t); });
            });
            hidden.value = tags.join(',');
        }
    }

    /* -----------------------------------------------------------------
       Category-suggest toggle
       --------------------------------------------------------------- */
    function initSuggestToggle() {
        var trigger = document.querySelector('[data-suggest-toggle]');
        var panel   = document.querySelector('[data-suggest-panel]');
        var select  = document.getElementById('bp_category');
        if (!trigger || !panel) return;
        trigger.addEventListener('click', function () {
            var open = panel.classList.toggle('is-open');
            trigger.textContent = open ? 'Use existing category instead' : 'Suggest a new category';
            if (open && select) {
                select.value = '0';
                var input = panel.querySelector('input');
                if (input) input.focus();
            }
        });
    }

    /* -----------------------------------------------------------------
       Editor: word count + auto-resizing inputs + auto-save dot
       --------------------------------------------------------------- */
    function initEditor() {
        var title    = document.getElementById('bp-title');
        var subtitle = document.getElementById('bp-subtitle');
        var content  = document.getElementById('bp-content');
        var count    = document.getElementById('bp-wordcount');
        var saved    = document.getElementById('bp-savedmark');
        var linkPanel = document.getElementById('bp-link-panel');
        var linkInput = document.getElementById('bp_external_link');
        var linkPreview = linkPanel ? linkPanel.querySelector('[data-link-preview]') : null;
        var linkClear = linkPanel ? linkPanel.querySelector('[data-link-clear]') : null;
        if (!content) return;

        [title, subtitle].forEach(function (el) {
            if (!el) return;
            autoSize(el);
            el.addEventListener('input', function () { autoSize(el); markDirty(); });
        });
        content.addEventListener('input', function () { recount(); markDirty(); });

        recount();

        function recount() {
            var txt = (content.value || '').trim();
            var words = txt ? txt.split(/\s+/).filter(Boolean).length : 0;
            var mins  = Math.max(1, Math.round(words / 220));
            if (count) count.textContent = words + ' words / ' + mins + ' min read';
        }

        var saveTimer = null;
        function markDirty() {
            if (!saved) return;
            saved.style.opacity = '0.4';
            var dot = saved.classList && saved.classList.contains('dot') ? saved : saved.querySelector('.dot');
            if (dot) dot.style.background = 'var(--warn)';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function () {
                saved.style.opacity = '1';
                if (dot) dot.style.background = 'var(--ok)';
            }, 900);
        }

        function autoSize(el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }

        var tools = document.querySelectorAll('.bp-tool');
        tools.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var kind = btn.dataset.md;
                if (!kind) return;
                if (kind === 'link') {
                    openStoryLinkPanel();
                    return;
                }
                if (kind === 'image') {
                    openCoverFromToolbar();
                    return;
                }
                wrapSelection(content, kind);
            });
        });

        content.addEventListener('keydown', function (e) {
            var meta = e.metaKey || e.ctrlKey;
            if (!meta) return;
            if (e.key === 'b') { e.preventDefault(); wrapSelection(content, 'bold'); }
            if (e.key === 'i') { e.preventDefault(); wrapSelection(content, 'italic'); }
            if (e.key === 'k') { e.preventDefault(); openStoryLinkPanel(); }
        });

        if (linkInput) {
            updateLinkPreview();
            linkInput.addEventListener('input', function () {
                updateLinkPreview();
                markDirty();
            });
        }

        if (linkClear && linkInput) {
            linkClear.addEventListener('click', function () {
                linkInput.value = '';
                updateLinkPreview();
                linkInput.focus();
                markDirty();
            });
        }

        function openStoryLinkPanel() {
            if (!linkPanel || !linkInput) return;
            linkPanel.classList.add('is-open');
            linkInput.focus();
            linkInput.select();
        }

        function updateLinkPreview() {
            if (!linkPreview || !linkInput) return;
            var value = linkInput.value.trim();
            if (!value) {
                linkPreview.hidden = true;
                linkPreview.removeAttribute('href');
                linkPreview.textContent = '';
                return;
            }
            var href = /^[a-z][a-z0-9+.-]*:\/\//i.test(value) ? value : 'https://' + value;
            linkPreview.hidden = false;
            linkPreview.href = href;
            linkPreview.textContent = value;
        }

        function openCoverFromToolbar() {
            if (typeof window.bpOpenCoverUpload === 'function') {
                window.bpOpenCoverUpload();
            }
        }
    }

    function wrapSelection(el, kind) {
        var start = el.selectionStart, end = el.selectionEnd;
        var val   = el.value, sel = val.slice(start, end);
        var before = '', after = '', placeholder = '';

        switch (kind) {
            case 'bold':   before = '**'; after = '**'; placeholder = 'bold text'; break;
            case 'italic': before = '*';  after = '*';  placeholder = 'italic';    break;
            case 'h1':     before = '\n## '; after = '\n'; placeholder = 'Heading'; break;
            case 'quote':  before = '\n> '; after = '\n'; placeholder = 'quoted text'; break;
            case 'ul':     before = '\n- '; after = '\n'; placeholder = 'list item'; break;
            default: return;
        }

        var insert = before + (sel || placeholder) + after;
        el.value = val.slice(0, start) + insert + val.slice(end);
        el.focus();
        var caret = start + insert.length;
        el.setSelectionRange(caret, caret);
        el.dispatchEvent(new Event('input'));
    }

    /* -----------------------------------------------------------------
       Search shortcut
       --------------------------------------------------------------- */
    function initSearchShortcut() {
        document.addEventListener('keydown', function (e) {
            if (!((e.metaKey || e.ctrlKey) && e.key === 'k')) return;
            // Don't steal Cmd+K from text fields (the editor uses it for link insertion).
            var tag = (document.activeElement && document.activeElement.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || (document.activeElement && document.activeElement.isContentEditable)) return;
            var target = document.querySelector('.bp-search-shortcut');
            if (target) { e.preventDefault(); window.location.href = target.getAttribute('href'); }
        });
    }

    /* -----------------------------------------------------------------
       Smooth-scroll for in-page anchors
       --------------------------------------------------------------- */
    function initSmoothAnchors() {
        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var id = a.getAttribute('href');
                if (id.length < 2) return;
                var target = document.querySelector(id);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    /* -----------------------------------------------------------------
       Scroll material: topbar state, reading progress and parallax drift
       --------------------------------------------------------------- */
    function initScrollEffects() {
        var topbar = document.querySelector('.bp-topbar');
        var parallaxEls = document.querySelectorAll('[data-parallax]');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var ticking = false;

        function update() {
            var doc = document.documentElement;
            var max = Math.max(1, doc.scrollHeight - window.innerHeight);
            var y = window.scrollY || doc.scrollTop || 0;
            var progress = Math.max(0, Math.min(1, y / max));

            doc.style.setProperty('--bp-scroll-progress', progress.toFixed(4));

            if (topbar) {
                topbar.classList.toggle('is-scrolled', y > 8);
            }

            if (parallaxEls.length && !reduce) {
                parallaxEls.forEach(function (el) {
                    var speed = parseFloat(el.getAttribute('data-parallax') || '0.035');
                    var shift = Math.max(-34, Math.min(18, y * -speed));
                    el.style.setProperty('--bp-parallax-y', shift.toFixed(2) + 'px');
                });
            }

            ticking = false;
        }

        function requestTick() {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(update);
        }

        update();
        window.addEventListener('scroll', requestTick, { passive: true });
        window.addEventListener('resize', requestTick);
    }

    /* -----------------------------------------------------------------
       Pointer glow for premium hover surfaces
       --------------------------------------------------------------- */
    function initPointerGlow() {
        if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;
        var cards = document.querySelectorAll([
            '.meridian-feature-card',
            '.meridian-story-card',
            '.meridian-topic-card',
            '.meridian-stat-card',
            '.meridian-chart-card',
            '.meridian-status-card',
            '.meridian-insight-card',
            '.meridian-content-card',
            '.apple-card',
            '.ap-story-card',
            '.ap-info-card',
            '.ap-feature-card',
            '.bp-author-card',
            '.bp-step',
            '.bp-faq'
        ].join(','));

        cards.forEach(function (card) {
            card.addEventListener('pointermove', function (e) {
                var rect = card.getBoundingClientRect();
                card.style.setProperty('--mx', ((e.clientX - rect.left) / rect.width * 100).toFixed(2) + '%');
                card.style.setProperty('--my', ((e.clientY - rect.top) / rect.height * 100).toFixed(2) + '%');
            });
        });
    }

    /* -----------------------------------------------------------------
       Tilt effect on product surfaces
       --------------------------------------------------------------- */
    function initLeadTilt() {
        if (window.matchMedia && window.matchMedia('(hover: none)').matches) return;
        var cover = document.querySelector('[data-tilt]') || document.querySelector('.bp-product-window') || document.querySelector('.bp-lead__cover');
        if (!cover) return;
        var rect;
        cover.addEventListener('mouseenter', function () { rect = cover.getBoundingClientRect(); });
        cover.addEventListener('mousemove', function (e) {
            if (!rect) rect = cover.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            cover.style.transform = 'translate3d(0, var(--bp-parallax-y, 0), 0) rotateX(' + (-y * 2.2) + 'deg) rotateY(' + (x * 2.2) + 'deg)';
        });
        cover.addEventListener('mouseleave', function () {
            cover.style.transform = 'translate3d(0, var(--bp-parallax-y, 0), 0)';
        });
    }

    /* -----------------------------------------------------------------
       Utilities
       --------------------------------------------------------------- */
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[c];
        });
    }
    function escapeAttr(s) { return escapeHtml(s).replace(/"/g, '&quot;'); }

    /* -----------------------------------------------------------------
       Sign-in required modal
       --------------------------------------------------------------- */
    function initSigninModal() {
        var modal = document.getElementById('bp-signin-modal');
        if (!modal) return;

        function open() {
            modal.classList.add('is-open');
            document.body.classList.add('bp-modal-open');
        }
        function close() {
            modal.classList.remove('is-open');
            document.body.classList.remove('bp-modal-open');
        }

        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-requires-auth]');
            if (trigger) {
                e.preventDefault();
                open();
                return;
            }
            if (e.target.closest('[data-signin-close]') || e.target.closest('.bp-modal__close')) {
                e.preventDefault();
                close();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                close();
            }
        });

        if (document.querySelector('[data-trigger-signin]')) {
            setTimeout(open, 400);
        }
    }

    /* -----------------------------------------------------------------
       Meridian controls
       --------------------------------------------------------------- */
    function initMeridianControls() {
        var topicToggle = document.querySelector('[data-topic-suggest-toggle]');
        var topicPanel = document.querySelector('[data-topic-suggest-panel]');
        if (topicToggle && topicPanel) {
            topicToggle.addEventListener('click', function () {
                topicPanel.classList.toggle('is-open');
                var input = topicPanel.querySelector('input');
                if (topicPanel.classList.contains('is-open') && input) input.focus();
            });
        }

        var topicSearch = document.querySelector('[data-topic-search]');
        var topicCards = document.querySelectorAll('[data-topic-name]');
        var topicFilter = 'all';
        var filterButtons = document.querySelectorAll('[data-topic-filter]');
        var applyTopicFilters = function () {};
        if ((topicSearch || filterButtons.length) && topicCards.length) {
            applyTopicFilters = function () {
                var q = topicSearch ? topicSearch.value.trim().toLowerCase() : '';
                topicCards.forEach(function (card) {
                    var name = card.getAttribute('data-topic-name') || '';
                    var matchesSearch = !q || name.indexOf(q) !== -1;
                    var matchesFilter = topicFilter !== 'following' || card.getAttribute('data-following') === '1';
                    card.hidden = !(matchesSearch && matchesFilter);
                });
            };

            if (topicSearch) {
                topicSearch.addEventListener('input', function () {
                    applyTopicFilters();
                });
            }

            filterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    topicFilter = button.getAttribute('data-topic-filter') || 'all';
                    filterButtons.forEach(function (btn) { btn.classList.toggle('is-active', btn === button); });
                    applyTopicFilters();
                });
            });
        }

        document.querySelectorAll('.meridian-follow').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var following = !button.classList.contains('is-following');
                button.classList.toggle('is-following', following);
                button.textContent = following ? 'Following' : 'Follow';
                var card = button.closest('[data-following]');
                if (card) card.setAttribute('data-following', following ? '1' : '0');
                applyTopicFilters();
            });
        });

        document.querySelectorAll('[data-like-current], [data-like-post]').forEach(function (button) {
            var likeUrl = button.getAttribute('data-like-post') || window.location.pathname;
            var key = 'bp-liked:' + likeUrl;
            var liked = false;
            try { liked = window.localStorage && window.localStorage.getItem(key) === '1'; } catch (err) {}
            setLiked(liked);

            button.addEventListener('click', function () {
                setLiked(!button.classList.contains('is-liked'));
            });

            function setLiked(next) {
                button.classList.toggle('is-liked', next);
                button.setAttribute('aria-label', next ? 'Unlike this story' : 'Like this story');
                button.title = next ? 'Liked' : 'Like';
                try {
                    if (window.localStorage) {
                        if (next) window.localStorage.setItem(key, '1');
                        else window.localStorage.removeItem(key);
                    }
                } catch (err) {}
            }
        });

        document.querySelectorAll('[data-share-current], [data-share-url]').forEach(function (button) {
            button.addEventListener('click', function () {
                var shareUrl = button.getAttribute('data-share-url') || window.location.href;
                var shareTitle = button.getAttribute('data-share-title') || document.title;
                if (navigator.share) {
                    navigator.share({ title: shareTitle, url: shareUrl }).catch(function () {});
                    return;
                }
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(shareUrl).then(function () {
                        button.classList.add('is-copied');
                        setTimeout(function () { button.classList.remove('is-copied'); }, 900);
                    });
                }
            });
        });
    }

    /* -----------------------------------------------------------------
       Aperture filters and contribution tabs
       --------------------------------------------------------------- */
    function initApertureControls() {
        var search = document.querySelector('[data-aperture-search]');
        var grid = document.querySelector('[data-aperture-grid]');
        var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('[data-category]')) : [];
        var empty = document.querySelector('[data-aperture-empty]');
        var filter = 'all';

        function applyFilters() {
            if (!cards.length) return;
            var q = search ? search.value.trim().toLowerCase() : '';
            var visible = 0;
            cards.forEach(function (card) {
                var category = card.getAttribute('data-category') || '';
                var haystack = card.getAttribute('data-search') || card.textContent.toLowerCase();
                var matchFilter = filter === 'all' || category === filter;
                var matchSearch = !q || haystack.indexOf(q) !== -1;
                var show = matchFilter && matchSearch;
                card.hidden = !show;
                if (show) visible++;
            });
            if (empty) empty.hidden = visible !== 0;
        }

        document.querySelectorAll('[data-aperture-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                filter = (button.getAttribute('data-aperture-filter') || 'all').toLowerCase();
                document.querySelectorAll('[data-aperture-filter]').forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === button);
                });
                applyFilters();
            });
        });

        if (search) {
            search.addEventListener('input', applyFilters);
        }

        var tabs = document.querySelectorAll('[data-contribute-tab]');
        var categoryPanel = document.querySelector('[data-category-panel]');
        var essayFields = document.querySelectorAll('[data-essay-field]');
        var categorySelect = document.getElementById('bp_category');
        var contributionMode = document.getElementById('bp_contribution_mode');
        var submitButton = document.querySelector('.ap-submit-card__bottom .bp-btn');
        var modeTitle = document.querySelector('[data-mode-title]');
        var modeDescription = document.querySelector('[data-mode-description]');
        var submitNote = document.querySelector('[data-submit-note]');
        var titleInput = document.getElementById('bp-title');
        var contentInput = document.getElementById('bp-content');
        var suggestInput = document.getElementById('bp_suggest_category');

        function setContributeMode(mode) {
            mode = mode === 'category' ? 'category' : 'essay';
            tabs.forEach(function (btn) {
                btn.classList.toggle('is-active', (btn.getAttribute('data-contribute-tab') || 'essay') === mode);
            });
            if (categoryPanel) categoryPanel.hidden = mode !== 'category';
            essayFields.forEach(function (field) {
                field.hidden = mode === 'category';
            });
            if (categorySelect && mode === 'category') categorySelect.value = '0';
            if (contributionMode) contributionMode.value = mode;
            if (titleInput) titleInput.required = mode !== 'category';
            if (contentInput) contentInput.required = mode !== 'category';
            if (suggestInput) suggestInput.required = mode === 'category';
            if (submitButton) submitButton.textContent = mode === 'category' ? 'Submit category' : 'Submit essay';
            if (modeTitle) {
                modeTitle.textContent = mode === 'category' ? 'Name the next section.' : 'Start with the idea.';
            }
            if (modeDescription) {
                modeDescription.textContent = mode === 'category'
                    ? 'Suggest a category for the public library. Admins approve it before readers or authors can use it.'
                    : 'Write a blog post for readers, then send it into the admin review queue. You can keep refining it before approval.';
            }
            if (submitNote) {
                var dot = submitNote.querySelector('.dot');
                submitNote.textContent = mode === 'category'
                    ? ' Appears after an admin approves the category.'
                    : ' Goes live after an admin approves.';
                if (dot) submitNote.insertBefore(dot, submitNote.firstChild);
            }
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                setContributeMode(tab.getAttribute('data-contribute-tab') || 'essay');
            });
        });

        setContributeMode(suggestInput && suggestInput.value.trim() ? 'category' : 'essay');
    }

    function initMySpaceFilters() {
        var filters = document.querySelectorAll('[data-myspace-filter]');
        var items = Array.prototype.slice.call(document.querySelectorAll('[data-myspace-item]'));
        var count = document.querySelector('[data-myspace-entry-count]');
        if (!filters.length || !items.length) return;

        function applyFilter(kind) {
            var visible = 0;
            filters.forEach(function (filter) {
                filter.classList.toggle('is-active', (filter.getAttribute('data-myspace-filter') || 'all') === kind);
            });
            items.forEach(function (item) {
                var itemKind = item.getAttribute('data-myspace-kind') || '';
                var show = kind === 'all' || itemKind === kind;
                item.classList.remove('is-filtering-in');
                if (show) {
                    item.hidden = false;
                    window.requestAnimationFrame(function () {
                        item.classList.add('is-filtering-in');
                    });
                    visible++;
                } else {
                    item.hidden = true;
                }
            });
            if (count) count.textContent = visible + ' ' + (visible === 1 ? 'entry' : 'entries');
        }

        filters.forEach(function (filter) {
            filter.addEventListener('click', function (event) {
                event.preventDefault();
                applyFilter(filter.getAttribute('data-myspace-filter') || 'all');
            });
        });
    }

    /* -----------------------------------------------------------------
       Boot
       --------------------------------------------------------------- */
    function ready(fn) {
        if (document.readyState !== 'loading') return fn();
        document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initBackgroundReveal();
        initAutoReveals();
        initShimmerText();
        initButtonShimmer();
        initReveal();
        initUserDrop();
        initCoverDrop();
        initTagInput();
        initSuggestToggle();
        initEditor();
        // initSearchShortcut();
        initSmoothAnchors();
        initScrollEffects();
        initPointerGlow();
        initLeadTilt();
        initSigninModal();
        initMeridianControls();
        initApertureControls();
        initMySpaceFilters();
    });
})();
