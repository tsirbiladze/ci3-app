(function () {
	var tokenNameMeta = document.querySelector('meta[name="csrf-token-name"]');
	var tokenValueMeta = document.querySelector('meta[name="csrf-token"]');
	if (tokenNameMeta && tokenValueMeta) {
		var tokenName = tokenNameMeta.getAttribute("content");
		var tokenValue = tokenValueMeta.getAttribute("content");
		if (window.jQuery) {
			$.ajaxSetup({
				data: {},
				headers: { "X-Requested-With": "XMLHttpRequest" },
			});
			$(document).ajaxSend(function (e, xhr, settings) {
				if (settings.type && settings.type.toUpperCase() === "POST") {
					if (typeof settings.data === "string") {
						settings.data +=
							(settings.data ? "&" : "") +
							encodeURIComponent(tokenName) +
							"=" +
							encodeURIComponent(tokenValue);
					} else if (typeof settings.data === "object") {
						settings.data[tokenName] = tokenValue;
					}
				}
			});
		}
	}

	window.toast = function (message, variant) {
		variant = variant || "secondary";
		var el = document.createElement("div");
		el.className = "position-fixed top-0 end-0 p-3";
		el.style.zIndex = 1080;
		el.innerHTML =
			'\
      <div class="toast align-items-center text-bg-' +
			variant +
			' border-0 show" role="alert">\
        <div class="d-flex">\
          <div class="toast-body">' +
			message +
			'</div>\
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>\
        </div>\
      </div>';
		document.body.appendChild(el);
		setTimeout(function () {
			el.remove();
		}, 3500);
	};

	// Simple confirm modal helper (Bootstrap 5)
	window.confirmModal = function (options) {
		options = options || {};
		var title = options.title || "Confirm";
		var message = options.message || "Are you sure?";
		var confirmText = options.confirmText || "Confirm";
		var cancelText = options.cancelText || "Cancel";
		var variant = options.variant || "danger";

		var wrapper = document.createElement("div");
		wrapper.innerHTML =
			'\
		<div class="modal fade" tabindex="-1">\
		  <div class="modal-dialog">\
			<div class="modal-content">\
			  <div class="modal-header">\
				<h5 class="modal-title">' +
			title +
			'</h5>\
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>\
			  </div>\
			  <div class="modal-body"><p>' +
			message +
			'</p></div>\
			  <div class="modal-footer">\
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' +
			cancelText +
			'</button>\
				<button type="button" class="btn btn-' +
			variant +
			'" id="__confirmBtn__">' +
			confirmText +
			'</button>\
			  </div>\
			</div>\
		  </div>\
		</div>';
		document.body.appendChild(wrapper);
		var modalEl = wrapper.firstElementChild;
		var modal = new bootstrap.Modal(modalEl);
		modal.show();

		return new Promise(function (resolve) {
			modalEl.querySelector("#__confirmBtn__").addEventListener("click", function () {
				resolve(true);
				modal.hide();
			});
			modalEl.addEventListener("hidden.bs.modal", function () {
				wrapper.remove();
				resolve(false);
			});
		});
	};

})();
