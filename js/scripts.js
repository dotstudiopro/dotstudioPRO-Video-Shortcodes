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

function ready(fn) {
	if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {
		fn();
	} else {
		document.addEventListener('DOMContentLoaded', fn);
	}
}

ready(dspdev_video_shortcodes_playlist_iframes_setup);