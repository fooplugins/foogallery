/**
 * External dependencies.
 */
import { isEqual, get } from 'lodash';

/**
 * WordPress dependencies.
 */
const {
	ServerSideRender
} = wp.components;

export class FooGalleryServerSideRender extends ServerSideRender {
	componentDidUpdate( prevProps, prevState ) {
		if ( ! isEqual( prevProps, this.props ) ) {
			this.fetch( this.props );
		}
		if ( this.state.response !== prevState.response ) {
			if ( this.props.onChange ) {
				this.props.onChange();
			}
		}
	}
}

export default FooGalleryServerSideRender;