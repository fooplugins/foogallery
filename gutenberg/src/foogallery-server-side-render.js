/**
 * WordPress dependencies.
 */
const {
	ServerSideRender
} = wp.components;

export class FooGalleryServerSideRender extends ServerSideRender {
	componentDidUpdate( prevProps, prevState ) {
		if ( this.state.response !== prevState.response ) {
			if ( this.props.onChange ) {
				this.props.onChange();
			}
		}
	}
}

export default FooGalleryServerSideRender;