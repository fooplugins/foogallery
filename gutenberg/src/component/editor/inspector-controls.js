const { __ } = wp.i18n;
const { Component } = wp.element;
const { Button, IconButton } = wp.components;
const { InspectorControls } = wp.editor;

export default class FooGalleryEditorInspectorControls extends Component {
	render(){
		const { select, onRequestModalOpen, canEdit, edit, onRequestGalleryEdit, children } = this.props;
		return (
				<InspectorControls>
					<div className="foogallery-inspector-controls__button-container">
						{ canEdit ? <IconButton isDefault isLarge onClick={ onRequestGalleryEdit } icon="edit" label={ edit }/> : null }&nbsp;
						<Button isPrimary isLarge onClick={ onRequestModalOpen }>{ select }</Button>
					</div>
					{ children }
				</InspectorControls>
		);
	}
}

FooGalleryEditorInspectorControls.defaultProps = {
	canEdit: false,
	edit: __("Edit Gallery", "foogallery"),
	select: __("Select Gallery", "foogallery"),
	onRequestGalleryEdit: _.noop,
	onRequestModalOpen: _.noop
};