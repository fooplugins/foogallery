import './editor.scss';

import classnames from 'classnames';
import FooGalleryModal from '../modal';
import FooGalleryEditorEmpty from './empty';
import FooGalleryEditorPopulated from './populated';
import FooGalleryEditorDuplicate from './duplicate';
import FooGalleryRendered from './rendered';
import FooGalleryServerSideRender from '../server-side-render';
import FooGalleryEditorInspectorControls from './inspector-controls';

const { __, sprintf } = wp.i18n;
const { dispatch } = wp.data;
const { Component, Fragment } = wp.element;

export default class FooGalleryEditor extends Component {
	constructor(){
		super(...arguments);
		this.state = {
			isModalOpen: false,
			reload: false
		};

		this.showModal = this.showModal.bind(this);
		this.closeModal = this.closeModal.bind(this);
		this.insertGallery = this.insertGallery.bind(this);
		this.editGallery = this.editGallery.bind(this);
		this.reloadGallery = this.reloadGallery.bind(this);
		this.removeBlock = this.removeBlock.bind(this);
	}

	componentDidMount(){
		const { clientId, attributes: { id } } = this.props;
		if ( id != 0 ){
			FooGalleryRendered.add( id, clientId );
		}
		console.log("componentDidMount", clientId, id, FooGalleryRendered.array);
	}

	componentWillUnmount() {
		const { clientId, attributes: { id } } = this.props;
		if ( id != 0 ){
			FooGalleryRendered.remove( clientId );
		}
		console.log("componentWillUnmount", clientId, id, FooGalleryRendered.array);
	}

	showModal(){
		this.setState({isModalOpen: true});
	}

	closeModal(){
		this.setState({isModalOpen: false});
	}

	insertGallery( id ){
		const { clientId, setAttributes } = this.props;
		FooGalleryRendered.update( id, clientId );
		setAttributes( { id } );
		console.log( id, clientId );
	}

	editGallery(){
		const { attributes: { id } } = this.props;
		let editPost = sprintf( "/wp-admin/post.php?post=%s&action=edit", id );
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
			disable: FooGalleryRendered.ids(),
			isModalOpen: isModalOpen,
			onRequestModalOpen: this.showModal,
			onRequestModalClose: this.closeModal,
			onRequestBlockRemove: this.removeBlock,
			onRequestGalleryInsert: this.insertGallery,
			onRequestGalleryEdit: this.editGallery,
			onRequestGalleryReload: this.reloadGallery
		};

		if ( FooGalleryRendered.contains(attributes.id, clientId) ){
			return (<FooGalleryEditorDuplicate { ...props }/>);
		}

		if ( !attributes.id ){
			return (<FooGalleryEditorEmpty { ...props }/>);
		}

		return (<FooGalleryEditorPopulated attributes={attributes} reload={reload} { ...props }/>);
	}
}

FooGalleryEditor.defaultProps = {

};