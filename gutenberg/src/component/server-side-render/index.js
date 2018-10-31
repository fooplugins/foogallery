import FooGalleryPlaceholder from '../placeholder';

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

export default class FooGalleryServerSideRender extends ServerSideRender {

	shouldComponentUpdate(nextProps, nextState){
		let propDiff = !isEqual(this.props, nextProps);
		let stateDiff = !isEqual(this.state, nextState);
		console.log("shouldComponentUpdate", propDiff, stateDiff);
		return propDiff || stateDiff;
	}

	componentDidUpdate() {
		console.log("componentDidUpdate");
		super.componentDidUpdate( ...arguments );
		const { attributes: { id } } = this.props;
		jQuery( '[id="foogallery-gallery-' + id + '"]' ).foogallery(FooGallery.autoDefaults);
	}

	componentWillReceiveProps( props ){
		console.log("componentWillReceiveProps");
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
					<FooGalleryPlaceholder instructions={ loading }>
						<Spinner />
					</FooGalleryPlaceholder>
			);
		} else if ( response.error ) {
			// translators: %s: error message describing the problem
			const errorMessage = sprintf( error, response.errorMsg );
			return (
					<FooGalleryPlaceholder instructions={ errorMessage } />
			);
		} else if ( ! response.length ) {
			return (
					<FooGalleryPlaceholder instructions={ empty } />
			);
		}

		return (
				<RawHTML key="html">{ response }</RawHTML>
		);
	}

}

FooGalleryServerSideRender.defaultProps = {
	loading: __("Loading gallery...", "foogallery"),
	error: __("Error loading gallery: %s", "foogallery"),
	empty: __("No gallery was found.", "foogallery")
};