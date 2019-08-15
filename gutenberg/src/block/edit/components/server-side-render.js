import FooGalleryEditPlaceholder from './placeholder';

/**
 * WordPress dependencies.
 */
const { __, sprintf } = wp.i18n;
const { Component, createRef, createElement } = wp.element;
const { Spinner } = wp.components;
const apiFetch = wp.apiFetch;
const { addQueryArgs } = wp.url;
const { isEqual, debounce } = lodash;

export function rendererPath( block, attributes = null, urlQueryArgs = {} ) {
	return addQueryArgs( `/wp/v2/block-renderer/${ block }`, {
		context: 'edit',
		...( null !== attributes ? { attributes } : {} ),
		...urlQueryArgs,
	} );
}

export default class FooGalleryEditServerSideRender extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			response: null,
			reload: props.reload
		};
		this.galleryRef = createRef();
	}

	static getDerivedStateFromProps(props, state) {
		if (props.reload !== state.reload){
			return {
				response: null,
				reload: props.reload
			};
		}
		return null;
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetch( this.props );
		// Only debounce once the initial fetch occurs to ensure that the first
		// renders show data as soon as possible.
		this.fetch = debounce( this.fetch, 500 );
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	shouldComponentUpdate(nextProps, nextState){
		let propDiff = !isEqual(this.props, nextProps);
		let stateDiff = !isEqual(this.state, nextState);
		return propDiff || stateDiff;
	}

	componentDidUpdate(prevProps) {
		if ( ! isEqual( prevProps, this.props ) ) {
			this.fetch( this.props );
		}
		if (this.galleryRef.current != null){
			jQuery( this.galleryRef.current ).children('.foogallery').foogallery(FooGallery.autoDefaults);
		}
	}

	fetch( props ){
		if ( ! this.isStillMounted ) {
			return;
		}
		if ( null !== this.state.response ) {
			this.setState( { response: null } );
		}
		const { block, attributes = null, urlQueryArgs = {} } = props;

		const path = rendererPath( block, attributes, urlQueryArgs );
		// Store the latest fetch request so that when we process it, we can
		// check if it is the current request, to avoid race conditions on slow networks.
		const fetchRequest = this.currentFetchRequest = apiFetch( { path } )
			.then( ( response ) => {
				if ( this.isStillMounted && fetchRequest === this.currentFetchRequest && response ) {
					this.setState( { response: response.rendered } );
				}
			} )
			.catch( ( error ) => {
				if ( this.isStillMounted && fetchRequest === this.currentFetchRequest ) {
					this.setState( { response: {
							error: true,
							errorMsg: error.message,
						} } );
				}
			} );
		return fetchRequest;
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
		return createElement("div", {
			dangerouslySetInnerHTML: { __html: response },
			ref: this.galleryRef
		});
	}

}

FooGalleryEditServerSideRender.defaultProps = {
	loading: __("Loading gallery...", "foogallery"),
	error: __("Error loading gallery: %s", "foogallery"),
	empty: __("No gallery was found.", "foogallery")
};