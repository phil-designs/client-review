/* Client Review — Preview Shell */
(function () {
	'use strict';

	// ── DOM refs ──────────────────────────────────────────────
	const iframe          = document.getElementById('cr-iframe');
	const overlay         = document.getElementById('cr-overlay');
	const frameWrapper    = document.getElementById('cr-frame-wrapper');
	const frameOuter      = frameWrapper.parentElement;
	const frameContainer  = document.getElementById('cr-frame-container');
	const sidebar         = document.getElementById('cr-sidebar');
	const commentsList   = document.getElementById('cr-comments-list');
	const commentCount   = document.getElementById('cr-comment-count');
	const popup          = document.getElementById('cr-popup');
	const popupInput     = document.getElementById('cr-popup-input');
	const annotateToggle = document.getElementById('cr-annotate-toggle');
	const annotateHint   = document.getElementById('cr-annotate-hint');
	const modalBackdrop  = document.getElementById('cr-modal-backdrop');
	const sidebarPage    = document.getElementById('cr-sidebar-page');
	const sidebarDevice  = document.getElementById('cr-sidebar-device');

	// ── State ─────────────────────────────────────────────────
	const DEVICES = { desktop: 1440, tablet: 768, mobile: 390 };
	let currentDevice  = 'desktop';
	let currentPageUrl = '/';
	let isAnnotating   = false;
	let pendingPin     = null; // { el, x_percent, y_percent }
	let annotations    = [];
	let currentScale   = 1;

	// ── Per-user pin colours ──────────────────────────────────
	const USER_COLORS   = ['#7c5cbf', '#2d86b5', '#3d9e6a', '#c05c5c', '#b07a2d', '#5c8a96', '#a05c96'];
	const userColorMap  = {};
	let   nextColorIdx  = 0;

	function getUserColor(ann) {
		if (ann.author_is_admin) return 'var(--cr-accent)';
		const uid = String(ann.user_id);
		if (!userColorMap[uid]) {
			userColorMap[uid] = USER_COLORS[nextColorIdx % USER_COLORS.length];
			nextColorIdx++;
		}
		return userColorMap[uid];
	}

	// ── Frame scaling ─────────────────────────────────────────
	// Uses negative margins to collapse the wrapper's excess CSS footprint
	// so the full device-width content is scaled to fit — not clipped.
	function scaleFrame() {
		const padding     = 56; // 28px each side
		const available   = frameContainer.clientWidth - padding;
		const deviceWidth = DEVICES[currentDevice];
		currentScale      = Math.min(1, available / deviceWidth);

		if (currentScale < 1) {
			const wrapperH = frameWrapper.offsetHeight;
			const excessW  = deviceWidth * (1 - currentScale);
			const excessH  = wrapperH   * (1 - currentScale);

			frameWrapper.style.transform       = `scale(${currentScale})`;
			frameWrapper.style.transformOrigin = 'top left';
			frameWrapper.style.marginRight     = `-${excessW}px`;
			frameWrapper.style.marginBottom    = `-${excessH}px`;
			frameOuter.style.width             = (deviceWidth * currentScale) + 'px';
			frameOuter.style.height            = '';
		} else {
			frameWrapper.style.transform       = '';
			frameWrapper.style.transformOrigin = '';
			frameWrapper.style.marginRight     = '';
			frameWrapper.style.marginBottom    = '';
			frameOuter.style.width             = '';
			frameOuter.style.height            = '';
		}
	}

	new ResizeObserver(scaleFrame).observe(frameContainer);

	// ── Device switching ──────────────────────────────────────
	document.querySelectorAll('.cr-device-btn').forEach(btn => {
		btn.addEventListener('click', () => {
			document.querySelectorAll('.cr-device-btn').forEach(b => b.classList.remove('active'));
			btn.classList.add('active');
			currentDevice = btn.dataset.device;
			frameWrapper.style.width = DEVICES[currentDevice] + 'px';
			sidebarDevice.textContent = btn.textContent.trim();
			scaleFrame();
			fetchAnnotations();
		});
	});

	// ── Sidebar toggle ────────────────────────────────────────
	document.getElementById('cr-sidebar-toggle').addEventListener('click', () => {
		sidebar.classList.toggle('open');
	});
	document.getElementById('cr-sidebar-close').addEventListener('click', () => {
		sidebar.classList.remove('open');
	});

	// ── Annotate mode ─────────────────────────────────────────
	annotateToggle.addEventListener('click', toggleAnnotateMode);

	document.addEventListener('keydown', e => {
		if (e.key === 'Escape') {
			if (pendingPin) { cancelPending(); return; }
			if (isAnnotating) toggleAnnotateMode();
		}
	});

	function toggleAnnotateMode() {
		isAnnotating = !isAnnotating;
		annotateToggle.classList.toggle('active', isAnnotating);
		annotateToggle.querySelector('span').textContent = isAnnotating ? 'Exit Comment Mode' : 'Add Comment';
		overlay.classList.toggle('cr-overlay--active', isAnnotating);
		annotateHint.classList.toggle('visible', isAnnotating);
		if (!isAnnotating) cancelPending();
	}

	// ── Iframe navigation tracking ────────────────────────────
	iframe.addEventListener('load', () => {
		try {
			const loc = iframe.contentWindow.location;
			currentPageUrl = loc.pathname + (loc.search || '');
			sidebarPage.textContent = currentPageUrl;

			// Re-attach scroll listener on each navigation
			iframe.contentWindow.addEventListener('scroll', renderPins, { passive: true });
		} catch (e) {
			// cross-origin guard (shouldn't fire on same-site)
		}
		fetchAnnotations();
	});

	// ── Overlay click → drop pin ──────────────────────────────
	overlay.addEventListener('click', e => {
		if (!isAnnotating) return;

		const rect          = overlay.getBoundingClientRect();
		const x_percent     = ((e.clientX - rect.left) / rect.width) * 100;
		const clickY        = (e.clientY - rect.top) / currentScale;

		let scrollTop  = 0;
		let pageHeight = rect.height;
		try {
			scrollTop  = iframe.contentWindow.scrollY || 0;
			pageHeight = Math.max(iframe.contentDocument.body.scrollHeight, rect.height);
		} catch (_) { /* cross-origin */ }

		const absolute_y = scrollTop + clickY;
		const y_percent  = (absolute_y / pageHeight) * 100;

		cancelPending();

		const pinEl = makePinEl('?', 'pending', x_percent, clickY);
		overlay.appendChild(pinEl);
		pendingPin = { el: pinEl, x_percent, y_percent };

		showPopup(e.clientX, e.clientY);
	});

	// ── Comment popup ─────────────────────────────────────────
	function showPopup(cx, cy) {
		popup.style.display = 'block';
		const pad = 12;
		let left = cx + pad;
		let top  = cy + pad;
		if (left + 310 > window.innerWidth)  left = cx - 310 - pad;
		if (top  + 170 > window.innerHeight) top  = cy - 170 - pad;
		popup.style.left = Math.max(pad, left) + 'px';
		popup.style.top  = Math.max(pad, top)  + 'px';
		popupInput.value = '';
		popupInput.focus();
	}

	function cancelPending() {
		if (pendingPin) { pendingPin.el.remove(); pendingPin = null; }
		popup.style.display = 'none';
		popupInput.value = '';
	}

	document.getElementById('cr-popup-cancel').addEventListener('click', cancelPending);

	document.getElementById('cr-popup-submit').addEventListener('click', submitComment);

	popupInput.addEventListener('keydown', e => {
		if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') submitComment();
		if (e.key === 'Escape') cancelPending();
	});

	async function submitComment() {
		const text = popupInput.value.trim();
		if (!text || !pendingPin) return;

		const btn = document.getElementById('cr-popup-submit');
		btn.disabled = true;

		try {
			const res = await apiFetch('annotations', 'POST', {
				page_url:  currentPageUrl,
				device:    currentDevice,
				x_percent: pendingPin.x_percent,
				y_percent: pendingPin.y_percent,
				comment:   text,
			});

			pendingPin.el.remove();
			pendingPin = null;
			popup.style.display = 'none';

			annotations.push(res);
			renderPins();
			renderSidebar();
			sidebar.classList.add('open');
		} catch (err) {
			toast('Failed to save comment — please try again.', 'error');
		} finally {
			btn.disabled = false;
		}
	}

	// ── Fetch & render annotations ────────────────────────────
	async function fetchAnnotations() {
		if (!currentPageUrl) return;
		try {
			const params = new URLSearchParams({ page_url: currentPageUrl, device: currentDevice });
			annotations = await apiFetch('annotations?' + params, 'GET');
			renderPins();
			renderSidebar();
		} catch (e) {
			console.warn('[ClientReview] Failed to load annotations', e);
		}
	}

	function renderPins() {
		overlay.querySelectorAll('.cr-pin:not(.cr-pin--pending)').forEach(p => p.remove());

		let scrollTop  = 0;
		let pageHeight = overlay.offsetHeight;
		try {
			scrollTop  = iframe.contentWindow.scrollY || 0;
			pageHeight = Math.max(iframe.contentDocument.body.scrollHeight, overlay.offsetHeight);
		} catch (_) { /* cross-origin */ }

		annotations.forEach((ann, idx) => {
			const absY     = (parseFloat(ann.y_percent) / 100) * pageHeight;
			const yFromTop = absY - scrollTop;

			// Only paint pins within ±30 px of visible viewport
			if (yFromTop < -30 || yFromTop > overlay.offsetHeight + 30) return;

			const pin = makePinEl(idx + 1, ann.status, parseFloat(ann.x_percent), yFromTop, ann);
			pin.dataset.id = ann.id;
			pin.addEventListener('click', e => {
				e.stopPropagation();
				highlightComment(ann.id);
				sidebar.classList.add('open');
			});
			overlay.appendChild(pin);
		});

		commentCount.textContent = annotations.length;
	}

	function makePinEl(label, status, xPercent, yFromTop, ann = null) {
		const el = document.createElement('div');
		el.className = `cr-pin cr-pin--${status}`;
		el.textContent = label;
		el.style.left = xPercent + '%';
		el.style.top  = yFromTop + 'px';
		if (ann && status === 'open') el.style.background = getUserColor(ann);
		return el;
	}

	// ── Sidebar ───────────────────────────────────────────────
	function renderSidebar() {
		if (!annotations.length) {
			commentsList.innerHTML = '<p class="cr-empty-state">No comments on this page yet.<br>Switch on <strong>Add Comment</strong> and click anywhere.</p>';
			return;
		}

		commentsList.innerHTML = '';
		annotations.forEach((ann, idx) => {
			const item = document.createElement('div');
			item.className = `cr-comment-item cr-comment-item--${ann.status}`;
			item.dataset.id = ann.id;

			const statusLabel = { open: 'Open', resolved: 'Done', needs_clarification: 'Needs Clarification' }[ann.status] || ann.status;
			const adminNote   = ann.admin_note
				? `<div class="cr-admin-response">
						<p class="cr-admin-response__label">Team response</p>
						<p class="cr-admin-response__text">${esc(ann.admin_note)}</p>
					</div>`
				: '';

			const editActions = ann.can_edit
				? `<div class="cr-comment-actions">
						<button class="cr-btn cr-btn--ghost cr-btn--sm cr-edit-btn" data-id="${ann.id}">Edit</button>
						<button class="cr-btn cr-btn--ghost cr-btn--sm cr-delete-btn" data-id="${ann.id}">Delete</button>
					</div>`
				: '';

			item.innerHTML = `
				<div class="cr-comment-item__header">
					<span class="cr-pin-num" style="background:${getUserColor(ann)}">${idx + 1}</span>
					<strong class="cr-comment-author">${esc(ann.author_name)}</strong>
					<span class="cr-comment-time">${fmtDate(ann.created_at)}</span>
					<span class="cr-comment-status cr-comment-status--${ann.status}">${esc(statusLabel)}</span>
				</div>
				<p class="cr-comment-text">${esc(ann.comment)}</p>
				${adminNote}
				${editActions}
			`;

			commentsList.appendChild(item);
		});

		// Event delegation
		commentsList.querySelectorAll('.cr-edit-btn').forEach(btn => {
			btn.addEventListener('click', () => startInlineEdit(parseInt(btn.dataset.id, 10)));
		});
		commentsList.querySelectorAll('.cr-delete-btn').forEach(btn => {
			btn.addEventListener('click', () => deleteAnnotation(parseInt(btn.dataset.id, 10)));
		});
	}

	function highlightComment(id) {
		commentsList.querySelectorAll('.cr-comment-item').forEach(el => el.classList.remove('highlighted'));
		overlay.querySelectorAll('.cr-pin').forEach(el => el.classList.remove('cr-pin--highlighted'));

		const item = commentsList.querySelector(`[data-id="${id}"]`);
		if (item) { item.classList.add('highlighted'); item.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }

		const pin = overlay.querySelector(`.cr-pin[data-id="${id}"]`);
		if (pin) pin.classList.add('cr-pin--highlighted');
	}

	function startInlineEdit(id) {
		const ann  = annotations.find(a => a.id == id);
		const item = commentsList.querySelector(`[data-id="${id}"]`);
		if (!ann || !item) return;

		const textEl = item.querySelector('.cr-comment-text');
		const ta = document.createElement('textarea');
		ta.className = 'cr-inline-edit';
		ta.value = ann.comment;
		textEl.replaceWith(ta);

		// Replace action buttons
		const existingActions = item.querySelector('.cr-comment-actions');
		const actionsEl = document.createElement('div');
		actionsEl.className = 'cr-inline-edit-actions';
		actionsEl.innerHTML = `
			<button class="cr-btn cr-btn--ghost cr-btn--sm">Cancel</button>
			<button class="cr-btn cr-btn--primary cr-btn--sm">Save</button>
		`;
		if (existingActions) existingActions.replaceWith(actionsEl);
		else item.appendChild(actionsEl);

		const [cancelBtn, saveBtn] = actionsEl.querySelectorAll('button');
		cancelBtn.addEventListener('click', renderSidebar);
		saveBtn.addEventListener('click', async () => {
			const newText = ta.value.trim();
			if (!newText) return;
			saveBtn.disabled = true;
			try {
				await apiFetch('annotations/' + id, 'PUT', { comment: newText });
				ann.comment = newText;
				renderSidebar();
			} catch (e) {
				toast('Failed to update comment.', 'error');
			} finally {
				saveBtn.disabled = false;
			}
		});

		ta.focus();
		ta.addEventListener('keydown', e => {
			if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') saveBtn.click();
			if (e.key === 'Escape') renderSidebar();
		});
	}

	async function deleteAnnotation(id) {
		if (!confirm('Delete this comment?')) return;
		try {
			await apiFetch('annotations/' + id, 'DELETE');
			annotations = annotations.filter(a => a.id != id);
			renderPins();
			renderSidebar();
		} catch (e) {
			toast('Failed to delete comment.', 'error');
		}
	}

	// ── Finish review ─────────────────────────────────────────
	document.getElementById('cr-finish-review').addEventListener('click', () => {
		modalBackdrop.style.display = 'flex';
	});

	document.getElementById('cr-modal-cancel').addEventListener('click', () => {
		modalBackdrop.style.display = 'none';
	});

	modalBackdrop.addEventListener('click', e => {
		if (e.target === modalBackdrop) modalBackdrop.style.display = 'none';
	});

	document.getElementById('cr-modal-confirm').addEventListener('click', async () => {
		const btn = document.getElementById('cr-modal-confirm');
		btn.disabled = true;
		btn.textContent = 'Submitting…';

		try {
			const fd = new FormData();
			fd.append('action', 'cr_finish_review');
			fd.append('nonce',  crPreview.ajaxNonce);

			const res  = await fetch(crPreview.ajaxUrl, { method: 'POST', body: fd });
			const data = await res.json();

			modalBackdrop.style.display = 'none';
			toast(data.success ? data.data.message : (data.data?.message || 'Something went wrong.'), data.success ? 'success' : 'error');
		} catch (e) {
			toast('Failed to submit review.', 'error');
		} finally {
			btn.disabled = false;
			btn.textContent = 'Yes, Submit';
		}
	});

	// ── REST helper ───────────────────────────────────────────
	async function apiFetch(endpoint, method = 'GET', body = null) {
		const opts = {
			method,
			headers: { 'X-WP-Nonce': crPreview.restNonce },
		};
		if (body) {
			opts.headers['Content-Type'] = 'application/json';
			opts.body = JSON.stringify(body);
		}
		const res = await fetch(crPreview.restUrl + endpoint, opts);
		if (!res.ok) throw new Error('API error: ' + res.status);
		return res.json();
	}

	// ── Utilities ─────────────────────────────────────────────
	function esc(str) {
		const d = document.createElement('div');
		d.textContent = str || '';
		return d.innerHTML;
	}

	function fmtDate(str) {
		const d = new Date(str);
		return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) + ' · ' +
		       d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
	}

	function toast(msg, type = 'success') {
		const el = document.createElement('div');
		el.className = `cr-toast cr-toast--${type}`;
		el.textContent = msg;
		document.body.appendChild(el);
		requestAnimationFrame(() => el.classList.add('visible'));
		setTimeout(() => {
			el.classList.remove('visible');
			setTimeout(() => el.remove(), 300);
		}, 4000);
	}

	// ── Init ──────────────────────────────────────────────────
	scaleFrame();
	fetchAnnotations();

})();
