import '@boxslider/components';

class FooGalleryBoxSlider extends HTMLElement {
	connectedCallback() {
		this.setupSlider();
		this.setupControls();
	}

	setupSlider() {
		this.slider = this.querySelector('bs-carousel, bs-fade, bs-tile, bs-cube');
		if (this.slider) {
			this.slider.addEventListener('before', (ev) => console.log('Transition starting', ev.detail));
			this.slider.addEventListener('after', (ev) => console.log('Transition complete', ev.detail));
		}
	}

	setupControls() {
		const controls = this.querySelector('.boxslider-controls');
		if (controls) {
			controls.querySelector('.boxslider-prev').addEventListener('click', () => this.slider.slider.prev());
			controls.querySelector('.boxslider-next').addEventListener('click', () => this.slider.slider.next());
			controls.querySelector('.boxslider-play').addEventListener('click', () => this.slider.slider.play());
			controls.querySelector('.boxslider-pause').addEventListener('click', () => this.slider.slider.pause());

			this.updatePlayPauseButtonState();
		}
	}

	updatePlayPauseButtonState() {
		const playButton = this.querySelector('.boxslider-play');
		const pauseButton = this.querySelector('.boxslider-pause');

		this.slider.slider.addEventListener('play', () => this.togglePlayPauseButtons(playButton, pauseButton, true));
		this.slider.slider.addEventListener('pause', () => this.togglePlayPauseButtons(playButton, pauseButton, false));

		this.togglePlayPauseButtons(playButton, pauseButton, this.slider.hasAttribute('auto-scroll'));
	}

	togglePlayPauseButtons(playButton, pauseButton, isPlaying) {
		if (playButton) playButton.style.display = isPlaying ? 'none' : 'inline-block';
		if (pauseButton) pauseButton.style.display = isPlaying ? 'inline-block' : 'none';
	}
}

customElements.define('foogallery-boxslider', FooGalleryBoxSlider);