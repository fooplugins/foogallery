import './editor.scss';
import classnames from 'classnames';
import FooGalleryEditModalItem from './item';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { Button, IconButton, Placeholder, Modal, Spinner } = wp.components;

/**
 * Create the FooGallery Select Modal Component
 */
export default class FooGalleryEditModal extends Component {

	constructor() {
		super( ...arguments );
		this.state = {
			id: this.props.currentId,
			data: null,
			isLoading: false,
			query: ''
		};

		this.onReloadClick = this.onReloadClick.bind( this );
		this.onInsertClick = this.onInsertClick.bind( this );
		this.onQueryChange = this.onQueryChange.bind( this );
	}

	async fetchGalleries(){
		this.setState( { isLoading: true } );
		let data = await wp.apiFetch({ path: "/foogallery/v1/galleries/" });
		data.forEach(gallery => {
			gallery.lowerName = typeof gallery.name === 'string' ? gallery.name.toLowerCase() : '';
		});
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
		if ( id !== 0 ){
			onRequestGalleryInsert( id );
			onRequestModalClose();
		}
	}

	onQueryChange(event){
		this.setState({query: event.target.value});
	}

	render() {
		const { isModalOpen, className, title, insert, reload, search, onRequestModalClose } = this.props;

		if ( !isModalOpen ){
			return null;
		}

		const { id, isLoading, query } = this.state;

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
						<input type="text" className="foogallery-modal__footer-search" placeholder={ search } value={ query } onChange={ this.onQueryChange } />
						<div className="foogallery-modal__footer-buttons">
							<IconButton isDefault icon="update" label={ reload } onClick={ this.onReloadClick } disabled={ isLoading }/>
							<Button isPrimary onClick={ this.onInsertClick } disabled={ id === 0 }>{ insert }</Button>
						</div>
					</div>
				</Modal>
		);
	}

	renderContent(){
		const { disable, empty, loading } = this.props;
		const { id, data, isLoading, query } = this.state;

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

		let self = this,
				hasQuery = query && query.length > 2,
				lowerQuery = hasQuery ? query.toLowerCase() : '',
				filtered = hasQuery ? data.filter(gallery => {
					return gallery.lowerName.indexOf(lowerQuery) !== -1;
				}) : data;

		return filtered.map(gallery => {
			return (
					<FooGalleryEditModalItem
							data={ gallery }
							isSelected={ id === gallery.id }
							isDisabled={ disable.indexOf(gallery.id) !== -1 }
							onSelected={ ( id ) => { self.setState( { id: id } ); } }
					/>
			);
		});
	}

}

FooGalleryEditModal.defaultProps = {
	currentId: 0,
	isModalOpen: false,
	className: "",
	title: __("Select the gallery you want to insert"),
	empty: __("No galleries found!"),
	insert: __("Insert Gallery"),
	reload: __("Reload Galleries"),
	loading: __("Loading galleries please wait..."),
	search: __("Search..."),
	disable: [],
	onRequestGalleryInsert: _.noop,
	onRequestModalClose: _.noop
};
