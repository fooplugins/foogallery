const { __ } = wp.i18n;
const { Component } = wp.element;
const { Toolbar, IconButton } = wp.components;
const { BlockControls } = wp.editor;

export default class FooGalleryEditorBlockControls extends Component {
	render(){
		const {
			select, onRequestModalOpen,
				canEdit, edit, onRequestGalleryEdit,
				canReload, reload, onRequestGalleryReload,
				remove, onRequestBlockRemove, children
		} = this.props;

		return (
				<BlockControls>
					<Toolbar>
						<IconButton icon="trash" label={remove} onClick={onRequestBlockRemove} />
					</Toolbar>
					<Toolbar>
						<IconButton icon="format-gallery" label={select} onClick={ onRequestModalOpen } />
						{ canEdit ? <IconButton icon="edit" label={edit} onClick={ onRequestGalleryEdit } /> : null }
						{ canReload ? <IconButton icon="update" label={reload} onClick={ onRequestGalleryReload } /> : null }
					</Toolbar>
					{ children }
				</BlockControls>
		);
	}
}

FooGalleryEditorBlockControls.defaultProps = {
	canEdit: false,
	canReload: false,
	select: __("Select gallery", "foogallery"),
	remove: __("Remove gallery", "foogallery"),
	reload: __("Reload gallery", "foogallery"),
	edit: __("Edit gallery", "foogallery"),
	onRequestModalOpen: _.noop,
	onRequestBlockRemove: _.noop,
	onRequestGalleryEdit: _.noop,
	onRequestGalleryReload: _.noop
};