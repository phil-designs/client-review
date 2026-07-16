/* Client Review — Admin Settings page */
(function(){
	/* ── Sync color picker ↔ hex text input ── */
	document.querySelectorAll('.cr-color-wrap').forEach(function(wrap){
		var picker = wrap.querySelector('input[type="color"]');
		var text   = wrap.querySelector('input[type="text"]');
		var cb     = wrap.querySelector('input[type="checkbox"]');

		if ( picker && text ) {
			picker.addEventListener('input', function(){ text.value = picker.value; });
			text.addEventListener('input', function(){
				if ( /^#[0-9a-fA-F]{6}$/.test(text.value) ) picker.value = text.value;
			});
		}

		if ( cb ) {
			function togglePicker(){
				var disabled = cb.checked;
				if ( picker ) picker.disabled = disabled;
				if ( text )   text.disabled   = disabled;
			}
			cb.addEventListener('change', togglePicker);
			togglePicker();
		}
	});

	/* ── Font select preview ──────────────────────────────────
	   The bundled fonts-local.css stylesheet (enqueued alongside this
	   script) already declares @font-face rules for every font in the
	   picker, so the preview can switch font-family directly with no
	   remote request to Google's servers. */
	document.querySelectorAll('.cr-font-select').forEach(function(sel){
		sel.addEventListener('change', function(){
			var font   = sel.value;
			var target = document.getElementById(sel.dataset.target);
			if ( target ) {
				target.style.fontFamily = "'" + font + "', sans-serif";
				target.textContent = (sel.id === 'cr_heading_font') ? font : 'The quick brown fox';
			}
		});
	});
})();
