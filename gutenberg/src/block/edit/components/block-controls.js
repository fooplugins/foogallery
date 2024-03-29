import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { BlockControls } from '@wordpress/block-editor';

export default class FooGalleryEditBlockControls extends Component {
	render(){
		const {
			select, onRequestModalOpen,
				canEdit, edit, onRequestGalleryEdit,
				canReload, reload, onRequestGalleryReload,
				remove, onRequestBlockRemove, children
		} = this.props;

		return (
				<BlockControls>
					<ToolbarGroup className="foogallery__toolbar-group">
						<ToolbarButton icon="trash" label={remove} onClick={onRequestBlockRemove} />
					</ToolbarGroup>
					<ToolbarGroup className="foogallery__toolbar-group">
						<ToolbarButton icon="format-gallery" label={select} onClick={ onRequestModalOpen } />
						{ canEdit ? <ToolbarButton icon="edit" label={edit} onClick={ onRequestGalleryEdit } /> : null }
						{ canReload ? <ToolbarButton icon="update" label={reload} onClick={ onRequestGalleryReload } /> : null }
					</ToolbarGroup>
					{ children }
				</BlockControls>
		);
	}
}

FooGalleryEditBlockControls.defaultProps = {
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