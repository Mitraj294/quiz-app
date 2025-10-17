<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analytics</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <button id="analytics-topics-card" type="button" data-fragment-url="{{ route('admin.analytics.topics.fragment') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none" aria-controls="analytics-detail-region">
                                <h4 class="text-sm text-gray-500">Topics</h4>
                                <div class="text-2xl font-bold">{{ $topicsCount }}</div>
                                <p class="text-sm text-gray-600 mt-2">Quiz topics in the system</p>
                            </button>

                            <button id="analytics-quizzes-card" type="button" data-fragment-url="{{ route('admin.analytics.quizzes.fragment') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none" aria-controls="analytics-detail-region">
                                <h4 class="text-sm text-gray-500">Quizzes</h4>
                                <div class="text-2xl font-bold">{{ $quizzesCount }}</div>
                                <p class="text-sm text-gray-600 mt-2">Total quizzes in the platform</p>
                            </button>

                            <button id="analytics-users-card" type="button" data-fragment-url="{{ route('admin.analytics.users.fragment') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none" aria-controls="analytics-detail-region">
                                <h4 class="text-sm text-gray-500">Users</h4>
                                @php
                                    // Count users who don't have the 'admin' role via the roles relation.
                                    // Some test/dev databases may not have the roles tables; fall back to total users in that case.
                                    try {
                                        $usersCountNonAdmin = \App\Models\User::whereDoesntHave('roles', function($q){ $q->where('role','admin'); })->count();
                                    } catch (\Throwable $e) {
                                        $usersCountNonAdmin = \App\Models\User::count();
                                    }
                                @endphp
                                <div class="text-2xl font-bold">{{ $usersCountNonAdmin }}</div>
                                <p class="text-sm text-gray-600 mt-2">Registered users</p>
                            </button>
                        </div>
                        <div class="mt-6">
                            <!-- Placeholder where fragments (topics/quizzes/users) will be loaded -->
                            <div id="analytics-detail-region"></div>
                     
                        </div>
                        <script>
                            (function(){
                                const detailRegion = document.getElementById('analytics-detail-region');
                                const debugLog = document.getElementById('analytics-debug-log');
                                function logDebug(...args){
                                    try { console.log(...args); } catch(e){}
                                    if (!debugLog) return;
                                    const time = new Date().toLocaleTimeString();
                                    const text = args.map(a => (typeof a === 'string' ? a : JSON.stringify(a))).join(' ');
                                    debugLog.textContent = `${time} - ${text}\n` + debugLog.textContent;
                                }
                                if (!detailRegion) {
                                    logDebug('analytics: detailRegion not found');
                                    return;
                                }

                                function fetchFragment(url, loadingText = 'Loading...'){
                                    logDebug('fetchFragment start', url);
                                    detailRegion.innerHTML = `<div class="p-6 bg-white shadow-sm sm:rounded-lg">${loadingText}</div>`;
                                    return fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                        .then(resp => {
                                            logDebug('fetch response status', resp.status);
                                            if (!resp.ok) throw new Error('Network error: ' + resp.status);
                                            return resp.text();
                                        })
                                        .then(html => {
                                            logDebug('fetchFragment inserted html, length', html.length);
                                            detailRegion.innerHTML = html;
                                            // after inserting, check for fragment marker and set active button
                                            const fragEl = detailRegion.querySelector('[data-fragment]');
                                            if (fragEl) {
                                                const fragName = fragEl.getAttribute('data-fragment');
                                                logDebug('fragment marker', fragName);
                                                if (fragName === 'topics' || fragName === 'topic-detail' || fragName === 'subtopic-detail') setActiveButton(document.getElementById('analytics-topics-card'));
                                                else if (fragName === 'quizzes') setActiveButton(document.getElementById('analytics-quizzes-card'));
                                                else if (fragName === 'users') setActiveButton(document.getElementById('analytics-users-card'));
                                            }
                                            // attach handlers for topic links inside the inserted fragment
                                            attachTopicLinks();
                                            return html;
                                        })
                                        .catch(err => { logDebug('fetchFragment error', err && err.message ? err.message : err); throw err; });
                                }

                                // Attach click handlers to .topic-link elements inside detailRegion to load topic fragments inline
                                function attachTopicLinks(){
                                    const links = detailRegion.querySelectorAll('.topic-link');
                                    logDebug('attachTopicLinks found', links.length, 'links');
                                    links.forEach(link => {
                                        // avoid attaching twice
                                        if (link.dataset.bound === 'true') return;
                                        link.dataset.bound = 'true';
                                        link.addEventListener('click', function(e){
                                            e.preventDefault();
                                            const title = (link.querySelector('h5') || {}).textContent || link.textContent || 'subtopic';
                                            const target = link.getAttribute('data-target');
                                            logDebug('click:', title, 'target=', target);
                                            if (target) {
                                                const el = document.querySelector(target);
                                                logDebug('inline target element present?', !!el);
                                                if (!el) {
                                                    // fallback to ajax if inline target missing
                                                    const url = link.getAttribute('data-fragment-url') || link.getAttribute('href');
                                                    logDebug('inline target missing, will fetch', url);
                                                    if (!url) return;
                                                    setActiveButton(document.getElementById('analytics-topics-card'));
                                                    fetchFragment(url, 'Loading topic...').catch(err => { logDebug('fetch error', err && err.message ? err.message : err); });
                                                    return;
                                                }
                                                // toggle visibility
                                                el.classList.toggle('hidden');
                                                logDebug('toggled inline target, now hidden=', el.classList.contains('hidden'));
                                                // ensure topics button remains active
                                                setActiveButton(document.getElementById('analytics-topics-card'));
                                            } else {
                                                const url = link.getAttribute('data-fragment-url') || link.getAttribute('href');
                                                logDebug('no inline target, will fetch', url);
                                                if (!url) return;
                                                setActiveButton(document.getElementById('analytics-topics-card'));
                                                fetchFragment(url, 'Loading topic...').catch(err => { logDebug('fetch error', err && err.message ? err.message : err); });
                                            }
                                        });
                                    });
                                }

                                // Mark one of the overview buttons as active (visual highlight)
                                function setActiveButton(activeBtn){
                                    const ids = ['analytics-topics-card','analytics-quizzes-card','analytics-users-card'];
                                    ids.forEach(id => {
                                        const b = document.getElementById(id);
                                        if (!b) return;
                                            // remove our active border/background and restore white bg
                                            b.classList.remove('border-2','border-indigo-700','bg-indigo-50');
                                            b.classList.add('bg-white');
                                            b.removeAttribute('data-active');
                                    });
                                    if (!activeBtn) return;
                                    // add active classes to the clicked button
                                    // set active border and indigo background, remove white background
                                    activeBtn.classList.remove('bg-white');
                                    activeBtn.classList.add('border-2','border-indigo-700','bg-indigo-50');
                                    activeBtn.setAttribute('data-active', 'true');
                                }

                                // If a fragment is already present on page load, set the active button accordingly
                                const existingFrag = detailRegion.querySelector('[data-fragment]');
                                if (existingFrag) {
                                    const fragName = existingFrag.getAttribute('data-fragment');
                                    if (fragName === 'topics') setActiveButton(document.getElementById('analytics-topics-card'));
                                    else if (fragName === 'quizzes') setActiveButton(document.getElementById('analytics-quizzes-card'));
                                    else if (fragName === 'users') setActiveButton(document.getElementById('analytics-users-card'));
                                }

                                // Topics
                                const topicsCard = document.getElementById('analytics-topics-card');
                                if (topicsCard) {
                                    topicsCard.addEventListener('click', function (e) {
                                        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                                        e.preventDefault();
                                        const url = topicsCard.getAttribute('data-fragment-url');
                                        logDebug('topicsCard click, will fetch', url);
                                        setActiveButton(topicsCard);
                                        fetchFragment(url, 'Loading topics...')
                                            .then(html => detailRegion.innerHTML = html)
                                            .catch(err => { detailRegion.innerHTML = '<div class="p-6 bg-white shadow-sm sm:rounded-lg text-red-600">Failed to load topics.</div>'; logDebug('topics fetch failed', err && err.message ? err.message : err); });
                                    });
                                }
                                // Quizzes
                                const quizzesCard = document.getElementById('analytics-quizzes-card');
                                if (quizzesCard) {
                                    quizzesCard.addEventListener('click', function (e) {
                                        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                                        e.preventDefault();
                                        const url = quizzesCard.getAttribute('data-fragment-url');
                                        logDebug('quizzesCard click, will fetch', url);
                                        setActiveButton(quizzesCard);
                                        fetchFragment(url, 'Loading quizzes...')
                                            .then(html => detailRegion.innerHTML = html)
                                            .catch(err => { detailRegion.innerHTML = '<div class="p-6 bg-white shadow-sm sm:rounded-lg text-red-600">Failed to load quizzes.</div>'; logDebug('quizzes fetch failed', err && err.message ? err.message : err); });
                                    });
                                }

                                // Users
                                const usersCard = document.getElementById('analytics-users-card');
                                if (usersCard) {
                                    usersCard.addEventListener('click', function (e) {
                                        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                                        e.preventDefault();
                                        const url = usersCard.getAttribute('data-fragment-url');
                                        logDebug('usersCard click, will fetch', url);
                                        setActiveButton(usersCard);
                                        fetchFragment(url, 'Loading users...')
                                            .then(html => detailRegion.innerHTML = html)
                                            .catch(err => { detailRegion.innerHTML = '<div class="p-6 bg-white shadow-sm sm:rounded-lg text-red-600">Failed to load users.</div>'; logDebug('users fetch failed', err && err.message ? err.message : err); });
                                    });
                                }
                            })();
                        </script>
                    </div>
                </div>
            </div>





        </div>
    </div>
</x-app-layout>