/* Client Review — Admin Dashboard */
jQuery(function ($) {

	const { nonce, restNonce, restUrl, ajaxUrl } = crAdmin;

	// ── Generate invite link ──────────────────────────────────
	$('#cr-generate-btn').on('click', async function () {
		const btn   = $(this).prop('disabled', true).text('Generating…');
		const label = $('#cr-invite-label').val().trim();

		try {
			const res = await $.ajax({
				url:    ajaxUrl,
				method: 'POST',
				data:   { action: 'cr_generate_invite', nonce, label },
			});

			if (res.success) {
				$('#cr-result-url').val(res.data.url);
				$('#cr-generated-result').slideDown(200);
			} else {
				alert('Failed to generate invite. Please try again.');
			}
		} catch (e) {
			alert('Request failed. Please try again.');
		} finally {
			btn.prop('disabled', false).text('Generate Link');
		}
	});

	$('#cr-copy-btn').on('click', function () {
		const input = document.getElementById('cr-result-url');
		input.select();
		navigator.clipboard.writeText(input.value).catch(() => {
			document.execCommand('copy');
		});
		$(this).text('Copied!');
		setTimeout(() => $(this).text('Copy'), 2000);
	});

	// ── Delete invite ─────────────────────────────────────────
	$(document).on('click', '.cr-delete-invite-btn', async function () {
		if (!confirm('Delete this invite link? This cannot be undone.')) return;
		const btn   = $(this).prop('disabled', true);
		const token = btn.data('token');

		try {
			const res = await $.ajax({
				url:    ajaxUrl,
				method: 'POST',
				data:   { action: 'cr_delete_invite', nonce, token },
			});
			if (res.success) btn.closest('tr').fadeOut(200);
		} catch (e) {
			alert('Failed to delete invite.');
		} finally {
			btn.prop('disabled', false);
		}
	});

	// ── Status select ─────────────────────────────────────────
	$(document).on('change', '.cr-status-select', async function () {
		const id     = $(this).data('id');
		const status = $(this).val();
		const card   = $(this).closest('.cr-annotation-card');

		try {
			await fetch(restUrl + 'annotations/' + id, {
				method:  'PATCH',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce },
				body:    JSON.stringify({ status }),
			});

			// Update left-border colour to reflect new status
			card
				.removeClass('cr-ann-status--open cr-ann-status--resolved cr-ann-status--needs_clarification')
				.addClass('cr-ann-status--' + status);
		} catch (e) {
			alert('Failed to update status.');
			// revert to previous value — simplest: reload
		}
	});

	// ── Admin note save ───────────────────────────────────────
	$(document).on('click', '.cr-save-note-btn', async function () {
		const btn  = $(this).prop('disabled', true).text('Saving…');
		const id   = btn.data('id');
		const note = btn.closest('.cr-ann-note-row').find('.cr-admin-note-input').val().trim();
		const card = btn.closest('.cr-annotation-card');

		// Get current status from the select in this card
		const status = card.find('.cr-status-select').val() || 'open';

		try {
			await fetch(restUrl + 'annotations/' + id, {
				method:  'PATCH',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce },
				body:    JSON.stringify({ status, admin_note: note }),
			});
			btn.text('Saved!');
			setTimeout(() => btn.text('Save note'), 2000);
		} catch (e) {
			alert('Failed to save note.');
			btn.text('Save note');
		} finally {
			btn.prop('disabled', false);
		}
	});

});
