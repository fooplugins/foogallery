import FooGalleryEditPlaceholder from './placeholder';

/**
 * WordPress dependencies.
 */
const { __, sprintf } = wp.i18n;
const { RawHTML } = wp.element;
const {
		ServerSideRender,
		Spinner
} = wp.components;
const { isEqual } = lodash;

export default class FooGalleryEditServerSideRender extends ServerSideRender {

	shouldComponentUpdate(nextProps, nextState){
		let propDiff = !isEqual(this.props, nextProps);
		let stateDiff = !isEqual(this.state, nextState);
		return propDiff || stateDiff;
	}

	componentDidUpdate() {
		super.componentDidUpdate( ...arguments );
		const { attributes: { id } } = this.props;
		jQuery( '[id="foogallery-gallery-' + id + '"]' ).foogallery(FooGallery.autoDefaults);
	}

	componentWillReceiveProps( props ){
		//todo: call super.componentWillReceiveProps ???
		const { reload } = this.props;
		if (props.reload != reload){
			this.reload();
		}
	}

	reload(){
		this.setState({response: null});
	}

	render() {
		const { loading, error, empty } = this.props;
		const response = this.state.response;
		if ( ! response ) {
			return (
					<FooGalleryEditPlaceholder instructions={ loading }>
						<Spinner />
					</FooGalleryEditPlaceholder>
			);
		} else if ( response.error ) {
			// translators: %s: error message describing the problem
			const errorMessage = sprintf( error, response.errorMsg );
			return (
					<FooGalleryEditPlaceholder instructions={ errorMessage } />
			);
		} else if ( ! response.length ) {
			return (
					<FooGalleryEditPlaceholder instructions={ empty } />
			);
		}

		return (
				<RawHTML key="html">{ response }</RawHTML>
		);
	}

}

FooGalleryEditServerSideRender.defaultProps = {
	loading: __("Loading gallery...", "foogallery"),
	error: __("Error loading gallery: %s", "foogallery"),
	empty: __("No gallery was found.", "foogallery")
};