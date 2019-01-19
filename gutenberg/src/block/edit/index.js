import FooGalleryEditRendered from './rendered';
import {
	FooGalleryEditEmpty,
	FooGalleryEditPopulated,
	FooGalleryEditDuplicate
} from './views';

const { sprintf } = wp.i18n;
const { dispatch } = wp.data;
const { Component } = wp.element;
const { editGalleryUrl } = window.FOOGALLERY_BLOCK;

export default class FooGalleryEdit extends Component {
	/**
	 * Called whenever a block is created in the editor.
	 */
	constructor(){
		super(...arguments);

		// Keep track of whether or not the modal is open and a boolean indicating if the component should reload in the state.
		this.state = {
			isModalOpen: false,
			reload: false // to force a reload simply call this.setState({ reload: !this.state.reload })
		};

		// Ensure that whenever this methods are called the `this` variable correctly points to this instance of the component.
		this.showModal = this.showModal.bind(this);
		this.closeModal = this.closeModal.bind(this);
		this.insertGallery = this.insertGallery.bind(this);
		this.editGallery = this.editGallery.bind(this);
		this.reloadGallery = this.reloadGallery.bind(this);
		this.removeBlock = this.removeBlock.bind(this);
	}

	/**
	 * Every time the editor is mounted add the current gallery id and clientId to the static rendered array.
	 */
	componentDidMount(){
		const { clientId, attributes: { id } } = this.props;
		if ( id != 0 ){
			FooGalleryEditRendered.add( id, clientId );
		}
	}

	/**
	 * Whenever the editor is unmounted remove the id and clientId from the static rendered array.
	 */
	componentWillUnmount() {
		const { clientId, attributes: { id } } = this.props;
		if ( id != 0 ){
			FooGalleryEditRendered.remove( clientId );
		}
	}

	showModal(){
		this.setState({isModalOpen: true});
	}

	closeModal(){
		this.setState({isModalOpen: false});
	}

	insertGallery( id ){
		const { clientId, setAttributes } = this.props;
		FooGalleryEditRendered.update( id, clientId );
		setAttributes( { id } );
	}

	editGallery(){
		const { attributes: { id } } = this.props;
		let editPost = sprintf( editGalleryUrl, id );
		window.open( editPost, "_blank" );
	}

	reloadGallery(){
		const { reload } = this.state;
		this.setState({ reload: !reload });
	}

	removeBlock(){
		const { clientId } = this.props;
		dispatch("core/editor").removeBlock( clientId );
	}

	render(){
		const { attributes, clientId } = this.props;
		const { isModalOpen, reload } = this.state;

		let props = {
			disable: FooGalleryEditRendered.ids(),
			isModalOpen: isModalOpen,
			onRequestModalOpen: this.showModal,
			onRequestModalClose: this.closeModal,
			onRequestBlockRemove: this.removeBlock,
			onRequestGalleryInsert: this.insertGallery,
			onRequestGalleryEdit: this.editGallery,
			onRequestGalleryReload: this.reloadGallery
		};

		if ( FooGalleryEditRendered.contains(attributes.id, clientId) ){
			return (<FooGalleryEditDuplicate { ...props }/>);
		}

		if ( !attributes.id ){
			return (<FooGalleryEditEmpty { ...props }/>);
		}

		return (<FooGalleryEditPopulated attributes={attributes} reload={reload} { ...props }/>);
	}
}

FooGalleryEdit.defaultProps = {

};