// Vanilla ready check
function ready(fn) {
	if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {
		fn();
	} else {
		document.addEventListener('DOMContentLoaded', fn);
	}
}

// Set up the modal for iframe video viewing
function dspdev_video_shortcodes_playlist_iframes_setup() {
	var buttons = document.querySelectorAll('.dspdev-show-video');
	if (!buttons) return;
	buttons.forEach(function(elem) {
		elem.onclick = function() {
			var button = this;
			var video = elem.dataset.video;
			var title = elem.dataset.title;
			if (!video) return;

			// Set up modal
			var modal = document.createElement('div');
			modal.classList.add('dspdev-show-video-modal');
			var titleContainer = document.createElement('h3');
			titleContainer.innerHTML = title;
			titleContainer.classList.add('dspdev-show-video-modal-title');

			var close = document.createElement('a');
			close.classList.add('dspdev-show-video-modal-close');
			close.href = '#';
			close.innerHTML = 'Close';

			// Set up iframe
			var iframe = document.createElement('iframe');
			iframe.classList.add('dspdev-show-video-modal-iframe');
			iframe.src = "http://wp.dotstudiopro.com/player/" + video + "?skin=228b22";
			iframe.setAttribute('frameBorder', 0);

			// Stuff the iframe and title into the modal
			modal.appendChild(close);
			modal.appendChild(titleContainer);
			modal.appendChild(iframe);

			// Set up grey box
			var grey = document.createElement('div');
			grey.classList.add('dspdev-grey-modal-background');

			var remove = function() {
				modal.parentNode.removeChild(modal);
				grey.parentNode.removeChild(grey);
			};

			grey.onclick = remove;
			close.onclick = remove;


			// Append both to the body
			document.querySelector('body').appendChild(grey);
			document.querySelector('body').appendChild(modal);
		};
	});

}

// End setup modal for iframe

function dspdev_selection_menu_generate(button, search, dropdown, type) {
	if (!search.value || search.value.length < 1) return console.log("No search value");

	button.setAttribute('disabled', true);
	dropdown.setAttribute('disabled', true);

	const token = window.dspdev_token;

	const q = search.value;

	const current = dropdown.querySelector("option.current");

	if (!q || !token) return console.log("Missing q or token", q, token);

	if (type === 'playlist') {
		route = "s";
	} else {
		route = "videos";
	}

	const request = new XMLHttpRequest();
	request.open('GET', "https://api.myspotlight.tv/search/" + route + "?q=" + q, true);
	request.setRequestHeader("x-access-token", token);

	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			const data = JSON.parse(request.responseText);
			button.removeAttribute('disabled');
			dropdown.removeAttribute('disabled');
			if (data.success) {
				const response = data.data;
				console.log(response.hits);
				if (response.total > 0) {
					dropdown.innerHTML = '';
					if (current) dropdown.prepend(current);
					response.hits.forEach(function(hit) {
						if (current && current.value === hit._id) return;
						let title = hit._source.title;
						dropdown.innerHTML += '<option value="' + hit._id + '">' + title + '</option>';
					});
				}
			}
		} else {
			console.log("Oh snap!");
		}
	};

	request.onerror = function() {
		console.log("Connection Error");
	};
	console.log("Should Send");
	request.send();


}


function dspdev_setup_selection_menu(buttonSelector, searchSelector, dropdownSelector, type) {
	const button = document.querySelector(buttonSelector);
	const search = document.querySelector(searchSelector);
	const dropdown = document.querySelector(dropdownSelector);
	if (!button || !search || !dropdown) return console.log("Nope");
	button.onclick = dspdev_selection_menu_generate.bind(null, button, search, dropdown, type);
}

function dspdev_video_generator() {

	const generator = document.querySelector('#dspdev_video_shortcode_generator');
	const dropdown = document.querySelector('#dspdev_video');

	if (!generator || !dropdown) return;

	generator.onclick = () => {
		if (dropdown.value && dropdown.value.length > 0) {
			const width = document.querySelector("#dspdev_video_width");
			const height = document.querySelector("#dspdev_video_height");
			const autostart = document.querySelector("#dspdev_video_autostart:checked") ? "autostart='true'" : "autostart='false'";
			const loop = document.querySelector("#dspdev_video_loop:checked") ? "loop='true'" : "loop='false'";
			const widthCalculated = width && width.value > 0 ? "width='" + width.value + "'" : "width='640'";
			const heightCalculated = height && height.value > 0 ? "height='" + height.value + "'" : "height='480'";
			const shortcode = "[dspdev_video_shortcode video='" + dropdown.value + "' " + autostart + " " + loop + " " + widthCalculated + " " + heightCalculated + " ]";
			document.querySelector("#dspdev_video_shortcode").innerHTML = shortcode;
		}
	};
}

function dspdev_playlist_generator() {

	const generator = document.querySelector('#dspdev_playlist_shortcode_generator');
	const dropdown = document.querySelector('#dspdev_playlist');

	if (!generator || !dropdown) return;

	generator.onclick = () => {
		if (dropdown.value && dropdown.value.length > 0) {
			const video_class = document.querySelector("#dspdev_playlist_video_class");
			const air_date_class = document.querySelector("#dspdev_playlist_air_date_class");
			const video_css = video_class && video_class.value.length > 0 ? "video_css='" + video_class.value + "'" : "";
			const show_air_date = parseInt(jQuery("#dspdev_playlist_show_air_date").val()) === 1 ? true : false;
			const air_date_css = air_date_class && air_date_class.value.length > 0 ? "air_date_css='" + air_date_class.value + "'" : "";
			const shortcode = "[dspdev_playlist_shortcode video='" + dropdown.value + "' " + video_css + " " + (show_air_date ? air_date_css : "") + " ]";
			document.querySelector("#dspdev_playlist_shortcode").innerHTML = shortcode;
		}
	};
}


ready(dspdev_setup_selection_menu.bind(null, '#dspdev_video_selector_button', '#dspdev_video_search', '#dspdev_video_choices > #dspdev_video', 'video'));
ready(dspdev_video_generator);

ready(dspdev_setup_selection_menu.bind(null, '#dspdev_playlist_selector_button', '#dspdev_playlist_search', '#dspdev_playlist_choices > #dspdev_playlist', 'playlist'));
ready(dspdev_playlist_generator);

ready(dspdev_video_shortcodes_playlist_iframes_setup); // Setup iframes on ready