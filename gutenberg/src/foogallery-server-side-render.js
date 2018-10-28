/**
 * WordPress dependencies.
 */
const {
	ServerSideRender
} = wp.components;

export class FooGalleryServerSideRender extends ServerSideRender {

	componentWillUnmount() {
		super.componentWillUnmount();
		//jQuery(this.el).find('.foogallery').foogallery('destroy');
	}

	componentDidUpdate( prevProps ) {
		super.componentDidUpdate( prevProps );
		jQuery('#foogallery-gallery-' + this.props.attributes.foo ).foogallery();
	}
}

export default FooGalleryServerSideRender;