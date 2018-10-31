
import './editor.scss';
import classnames from 'classnames';
import FooGalleryModalItem from './item';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { Button, IconButton, Placeholder, Modal, Spinner } = wp.components;

/**
 * Create the FooGallery Select Modal Component
 */
export default class FooGalleryModal extends Component {

	constructor() {
		super( ...arguments );
		this.state = {
			id: this.props.currentId,
			data: null,
			isLoading: false
		};

		this.onReloadClick = this.onReloadClick.bind( this );
		this.onInsertClick = this.onInsertClick.bind( this );
	}

	async fetchGalleries(){
		this.setState( { isLoading: true } );
		let data = await wp.apiFetch({ path: "/foogallery/v1/galleries/" });
		this.setState( { data: data, isLoading: false } );
	}

	onReloadClick(event){
		event.stopPropagation();
		this.setState( { data: null } );
	}

	onInsertClick(event){
		event.stopPropagation();
		const { onRequestModalClose, onRequestGalleryInsert } = this.props;
		const { id } = this.state;
		if ( id != 0 ){
			onRequestGalleryInsert( id );
			onRequestModalClose();
		}
	}

	render() {
		const { isModalOpen, className, title, insert, reload, onRequestModalClose } = this.props;

		if ( !isModalOpen ){
			return null;
		}

		const { id, isLoading } = this.state;

		return (
				<Modal className={ classnames( "foogallery-modal", className ) }
							 title={title}
							 onRequestClose={ onRequestModalClose }>
					<div className="foogallery-modal__content">
						<div className="foogallery-modal__content-container">
							{ this.renderContent() }
						</div>
					</div>
					<div className="foogallery-modal__footer">
						<div className="foogallery-modal__footer-container">
							<IconButton isDefault icon="update" label={ reload } onClick={ this.onReloadClick } disabled={ isLoading }/>&nbsp;
							<Button isPrimary onClick={ this.onInsertClick } disabled={ id == 0 }>
								{ insert }
							</Button>
						</div>
					</div>
				</Modal>
		);
	}

	renderContent(){
		const { disable, empty, loading } = this.props;
		const { id, data, isLoading } = this.state;

		if ( data === null && !isLoading ){
			this.fetchGalleries();
			return null;
		}

		if ( isLoading ){
			return (<Placeholder className="foogallery-modal__content-placeholder" instructions={ loading }><Spinner/></Placeholder>);
		}

		if ( data === null || !data.length ){
			return (<Placeholder className="foogallery-modal__content-placeholder" instructions={ empty }/>);
		}

		let self = this;
		return data.map(gallery => {
			return (
					<FooGalleryModalItem
							data={ gallery }
							isSelected={ id == gallery.id }
							isDisabled={ disable.indexOf(gallery.id) !== -1 }
							onSelected={ ( id ) => { self.setState( { id: id } ); } }
					/>
			);
		});
	}

}

FooGalleryModal.defaultProps = {
	currentId: 0,
	isModalOpen: false,
	className: "",
	title: __("Select the gallery you want to insert"),
	empty: __("No galleries found!"),
	insert: __("Insert Gallery"),
	reload: __("Reload Galleries"),
	loading: __("Loading galleries please wait..."),
	disable: [],
	onRequestGalleryInsert: _.noop,
	onRequestModalClose: _.noop
};
